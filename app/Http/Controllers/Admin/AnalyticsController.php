<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BlockedIp;
use App\Models\Visit;
use App\Models\ActiveSession;

class AnalyticsController extends Controller
{
    private function applyFilter($query, $filter)
    {
        if ($filter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($filter === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($filter === 'last_7_days') {
            $query->where('created_at', '>=', today()->subDays(7));
        } elseif ($filter === 'last_30_days') {
            $query->where('created_at', '>=', today()->subDays(30));
        } elseif ($filter === 'this_month') {
            $query->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year);
        }
        return $query;
    }

    public function index(Request $request)
    {
        // Default to 'today'
        $filter = $request->input('date_filter', 'today');

        // Base query — human visitors only
        $humanBase = Visit::where('is_bot', false);
        $query = $this->applyFilter(clone $humanBase, $filter);

        $filteredTotal = (clone $query)->count();
        $totalVisits   = Visit::where('is_bot', false)->count();
        $visitsToday   = Visit::where('is_bot', false)->whereDate('created_at', today())->count();

        // Real-time live counts from ActiveSession
        $liveUsers = ActiveSession::where('is_bot', false)
            ->where('last_activity_at', '>=', now()->subSeconds(45))
            ->where('status', '!=', 'left')
            ->count();

        $joined5m = ActiveSession::where('is_bot', false)
            ->where('started_at', '>=', now()->subMinutes(5))
            ->count();

        $left5m = ActiveSession::where('is_bot', false)
            ->where('status', 'left')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->count();

        // Fallback for live users if no active sessions yet recorded
        if ($liveUsers === 0) {
            $liveUsers = Visit::where('is_bot', false)
                ->where('created_at', '>=', now()->subMinutes(3))
                ->distinct('ip_address')
                ->count('ip_address');
        }

        // Unique real users
        $uniqueUsers = (clone $query)->distinct('ip_address')->count('ip_address');

        $topCountries = (clone $query)
            ->selectRaw('country, country_code, count(*) as count')
            ->whereNotNull('country_code')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $topReferrers = (clone $query)
            ->selectRaw("referrer, count(*) as count")
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $topPages = (clone $query)
            ->selectRaw("url, count(*) as count")
            ->whereNotNull('url')
            ->groupBy('url')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $recentVisits = (clone $query)->latest()->take(50)->get();

        // Initial Active Sessions with their step journey for Overview tab
        $activeSessions = ActiveSession::where('is_bot', false)
            ->where('last_activity_at', '>=', now()->subMinutes(15))
            ->orderByDesc('last_activity_at')
            ->take(15)
            ->get();

        // Bounce Rate Calculation
        $bounceRate = 0;
        if ($filteredTotal > 0) {
            $ipCounts = (clone $query)
                ->selectRaw('ip_address, count(*) as total_visits')
                ->groupBy('ip_address')
                ->pluck('total_visits', 'ip_address');

            $totalSessions = $ipCounts->count();
            $bounces = $ipCounts->filter(fn($v) => $v == 1)->count();

            if ($totalSessions > 0) {
                $bounceRate = round(($bounces / $totalSessions) * 100, 2);
            }
        }

        // Device / Browser detection
        $allUserAgents = (clone $query)->pluck('user_agent');

        $devices  = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0];
        $browsers = [];

        foreach ($allUserAgents as $ua) {
            if (empty($ua)) continue;

            if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
                $devices['Tablet']++;
            } elseif (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/i', $ua)) {
                $devices['Mobile']++;
            } else {
                $devices['Desktop']++;
            }

            if (preg_match('/Edg/i', $ua))                                      $browser = 'Edge';
            elseif (preg_match('/OPR|Opera/i', $ua))                            $browser = 'Opera';
            elseif (preg_match('/Chrome/i', $ua))                               $browser = 'Chrome';
            elseif (preg_match('/Safari/i', $ua))                               $browser = 'Safari';
            elseif (preg_match('/Firefox/i', $ua))                              $browser = 'Firefox';
            elseif (preg_match('/MSIE|Trident/i', $ua))                         $browser = 'Internet Explorer';
            else                                                                 $browser = 'Other';

            if ($browser !== 'Other') {
                $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
            }
        }

        arsort($browsers);
        $browsers = array_slice($browsers, 0, 5);

        // ---- Bot Traffic ----
        $botQuery = $this->applyFilter(Visit::where('is_bot', true), $filter);

        $goodBotRaw = (clone $botQuery)
            ->where('bot_type', 'good')
            ->selectRaw('user_agent, count(*) as count')
            ->groupBy('user_agent')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $badBotRaw = (clone $botQuery)
            ->where('bot_type', 'bad')
            ->selectRaw('user_agent, count(*) as count')
            ->groupBy('user_agent')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $goodBots = [];
        foreach ($goodBotRaw as $row) {
            $name = $this->extractBotName($row->user_agent);
            $goodBots[$name] = ($goodBots[$name] ?? 0) + $row->count;
        }

        $badBots = [];
        foreach ($badBotRaw as $row) {
            $name = $this->extractBotName($row->user_agent);
            $badBots[$name] = ($badBots[$name] ?? 0) + $row->count;
        }

        arsort($goodBots);
        arsort($badBots);

        $recentBotVisits = (clone $botQuery)->latest()->take(30)->get();
        $totalGoodBots   = Visit::where('is_bot', true)->where('bot_type', 'good')->count();
        $totalBadBots    = Visit::where('is_bot', true)->where('bot_type', 'bad')->count();

        $blockedIps     = BlockedIp::latest()->get();
        $blockedIpList  = $blockedIps->pluck('ip_address')->toArray();

        // AI Intelligence Summary
        $aiSummary = $this->generateAiInsights($liveUsers, $topCountries, $topPages, $bounceRate);

        return view('admin.analytics.index', compact(
            'totalVisits', 'visitsToday', 'filteredTotal', 'uniqueUsers',
            'topCountries', 'topReferrers', 'topPages',
            'recentVisits', 'filter', 'liveUsers', 'joined5m', 'left5m',
            'activeSessions', 'aiSummary',
            'blockedIps', 'blockedIpList',
            'bounceRate', 'devices', 'browsers',
            'goodBots', 'badBots', 'recentBotVisits', 'totalGoodBots', 'totalBadBots'
        ));
    }

    /**
     * Real-time Live Feed Endpoint (called every 3 seconds for zero-reload Overview updates).
     */
    public function liveFeed(Request $request)
    {
        $now = now();

        $liveUsers = ActiveSession::where('is_bot', false)
            ->where('last_activity_at', '>=', $now->copy()->subSeconds(45))
            ->where('status', '!=', 'left')
            ->count();

        $idleUsers = ActiveSession::where('is_bot', false)
            ->whereBetween('last_activity_at', [$now->copy()->subSeconds(180), $now->copy()->subSeconds(45)])
            ->where('status', '!=', 'left')
            ->count();

        $joined5m = ActiveSession::where('is_bot', false)
            ->where('started_at', '>=', $now->copy()->subMinutes(5))
            ->count();

        $left5m = ActiveSession::where('is_bot', false)
            ->where('status', 'left')
            ->where('last_activity_at', '>=', $now->copy()->subMinutes(5))
            ->count();

        // Fallback for live count if active_sessions empty
        if ($liveUsers === 0) {
            $liveUsers = Visit::where('is_bot', false)
                ->where('created_at', '>=', $now->copy()->subMinutes(3))
                ->distinct('ip_address')
                ->count('ip_address');
        }

        // Active Sessions with step-by-step journeys
        $rawSessions = ActiveSession::where('is_bot', false)
            ->where('last_activity_at', '>=', $now->copy()->subMinutes(15))
            ->orderByDesc('last_activity_at')
            ->take(20)
            ->get();

        $sessionsData = [];
        foreach ($rawSessions as $s) {
            // Retrieve page journey steps for this session
            $visits = Visit::where('session_id', $s->session_id)
                ->orderBy('created_at', 'asc')
                ->take(8)
                ->get();

            $journey = [];
            foreach ($visits as $v) {
                $path = parse_url($v->url, PHP_URL_PATH) ?: '/';
                $journey[] = [
                    'url'        => $v->url,
                    'path'       => $path,
                    'title'      => $v->page_title ?: ($path === '/' ? 'Home Page' : Str::limit($path, 30)),
                    'dwell'      => $v->dwell_seconds > 0 ? ($v->dwell_seconds >= 60 ? floor($v->dwell_seconds/60).'m '.($v->dwell_seconds%60).'s' : $v->dwell_seconds.'s') : '',
                    'time_ago'   => $v->created_at->diffForHumans(null, true),
                    'is_current' => ($v->url === $s->current_page),
                ];
            }

            // Fallback journey step if none in visits table
            if (empty($journey) && $s->current_page) {
                $path = parse_url($s->current_page, PHP_URL_PATH) ?: '/';
                $journey[] = [
                    'url'        => $s->current_page,
                    'path'       => $path,
                    'title'      => $s->current_page_title ?: ($path === '/' ? 'Home Page' : Str::limit($path, 30)),
                    'dwell'      => '',
                    'time_ago'   => 'Just now',
                    'is_current' => true,
                ];
            }

            $durationSec = $s->started_at ? max(1, $now->diffInSeconds($s->started_at)) : 1;
            $durationText = $durationSec >= 60 ? floor($durationSec / 60) . 'm ' . ($durationSec % 60) . 's' : $durationSec . 's';

            $currentPath = parse_url($s->current_page, PHP_URL_PATH) ?: '/';

            $sessionsData[] = [
                'session_id'     => $s->session_id,
                'ip_address'     => $s->ip_address,
                'country'        => $s->country ?: 'Unknown',
                'country_code'   => strtolower($s->country_code ?? ''),
                'city'           => $s->city ?: '',
                'device_type'    => $s->device_type ?: 'Desktop',
                'browser'        => $s->browser ?: 'Other',
                'os'             => $s->os ?: 'Unknown',
                'referrer'       => $s->referrer ?: '',
                'referrer_domain'=> $s->referrer_domain ?: ($s->referrer ? parse_url($s->referrer, PHP_URL_HOST) : 'Direct'),
                'page_views'     => $s->page_views,
                'duration'       => $durationText,
                'status'         => $s->computed_status,
                'current_page'   => $s->current_page,
                'current_path'   => $currentPath,
                'current_title'  => $s->current_page_title ?: ($currentPath === '/' ? 'Home Page' : Str::limit($currentPath, 35)),
                'last_active'    => $s->last_activity_at ? $s->last_activity_at->diffForHumans(null, true) : 'Now',
                'journey'        => $journey,
            ];
        }

        // Live stream event ticker (last 10 human hits)
        $recentVisits = Visit::where('is_bot', false)
            ->latest()
            ->take(10)
            ->get();

        $events = [];
        foreach ($recentVisits as $v) {
            $path = parse_url($v->url, PHP_URL_PATH) ?: '/';
            $events[] = [
                'time'         => $v->created_at->format('H:i:s'),
                'country'      => $v->country,
                'country_code' => strtolower($v->country_code ?? ''),
                'city'         => $v->city,
                'device'       => $v->device_type ?: 'Desktop',
                'ip'           => $v->ip_address,
                'title'        => $v->page_title ?: ($path === '/' ? 'Home Page' : Str::limit($path, 30)),
                'url'          => $v->url,
            ];
        }

        // Suspicious Scraper / Bot Anomaly Detection
        $threatAlert = null;
        $suspiciousIp = Visit::where('created_at', '>=', $now->copy()->subMinutes(2))
            ->selectRaw('ip_address, count(*) as count')
            ->groupBy('ip_address')
            ->having('count', '>=', 20)
            ->first();

        if ($suspiciousIp) {
            $threatAlert = [
                'ip'      => $suspiciousIp->ip_address,
                'count'   => $suspiciousIp->count,
                'message' => "High-frequency scraping detected from {$suspiciousIp->ip_address} ({$suspiciousIp->count} hits in 2m).",
            ];
        }

        // Dynamic AI summary text
        $topCountries = Visit::where('is_bot', false)
            ->whereDate('created_at', today())
            ->selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->take(3)
            ->get();

        $topPages = Visit::where('is_bot', false)
            ->whereDate('created_at', today())
            ->selectRaw('url, count(*) as count')
            ->groupBy('url')
            ->orderByDesc('count')
            ->take(3)
            ->get();

        $aiSummary = $this->generateAiInsights($liveUsers, $topCountries, $topPages, 0);

        return response()->json([
            'live_users'      => $liveUsers,
            'idle_users'      => $idleUsers,
            'joined_5m'       => $joined5m,
            'left_5m'         => $left5m,
            'active_sessions' => $sessionsData,
            'live_events'     => $events,
            'threat_alert'    => $threatAlert,
            'ai_summary'      => $aiSummary,
            'timestamp'       => $now->format('H:i:s'),
        ]);
    }

    /**
     * AJAX endpoint for heartbeat from frontend visitor tracker.
     */
    public function ping(Request $request)
    {
        $sessionId = $request->input('session_id') ?? $request->cookie('_va_sid');
        if (!$sessionId) {
            return response()->json(['ok' => false]);
        }

        $url = $request->input('url');
        $title = $request->input('title');
        $dwell = (int) $request->input('dwell_seconds', 0);
        $action = $request->input('action', 'heartbeat');

        try {
            $session = ActiveSession::where('session_id', $sessionId)->first();
            if ($session) {
                $status = ($action === 'idle') ? 'idle' : (($action === 'exit') ? 'left' : 'active');

                $session->update([
                    'current_page_title' => $title ?: $session->current_page_title,
                    'dwell_seconds'      => max($session->dwell_seconds, $dwell),
                    'status'             => $status,
                    'last_activity_at'   => now(),
                ]);
            }

            // Also update the latest Visit record for this session & URL
            if ($url) {
                $latestVisit = Visit::where('session_id', $sessionId)
                    ->where('url', $url)
                    ->latest()
                    ->first();

                if ($latestVisit) {
                    $latestVisit->update([
                        'page_title'    => $title ?: $latestVisit->page_title,
                        'dwell_seconds' => max($latestVisit->dwell_seconds, $dwell),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Beacon endpoint when visitor leaves the page/tab.
     */
    public function beaconExit(Request $request)
    {
        $sessionId = $request->input('session_id') ?? $request->cookie('_va_sid');
        if ($sessionId) {
            try {
                ActiveSession::where('session_id', $sessionId)->update([
                    'status'           => 'left',
                    'last_activity_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return response()->noContent();
    }

    /**
     * Generate dynamic AI Intelligence insights based on real-time data.
     */
    private function generateAiInsights($liveCount, $topCountries, $topPages, $bounceRate): array
    {
        $countryList = [];
        if ($topCountries instanceof \Illuminate\Support\Collection) {
            foreach ($topCountries as $c) {
                if (!empty($c->country) && $c->country !== 'Unknown') {
                    $countryList[] = $c->country;
                }
            }
        }
        $countryStr = !empty($countryList) ? implode(', ', array_slice($countryList, 0, 3)) : 'গ্লোবাল';

        $topPageTitle = 'Home';
        if ($topPages instanceof \Illuminate\Support\Collection && $topPages->isNotEmpty()) {
            $first = $topPages->first();
            $path = parse_url($first->url ?? '', PHP_URL_PATH) ?: '/';
            $topPageTitle = ($path === '/' ? 'হোমপেজ' : Str::limit($path, 25));
        }

        if ($liveCount > 0) {
            $bnText = "বর্তমানে সাইটে {$liveCount} জন রিয়েল ইউজার সক্রিয় রয়েছে। প্রধান ট্র্যাফিক আসছে {$countryStr} থেকে এবং ব্যবহারকারীরা সর্বাধিক আগ্রহী '{$topPageTitle}' কন্টেন্টে। রিয়েল-টাইম রিটেনশন সন্তোষজনক।";
            $enText = "Currently {$liveCount} real visitor(s) are active on the site. Top traffic is flowing from {$countryStr}, focusing on '{$topPageTitle}'.";
        } else {
            $bnText = "এই মুহূর্তে কোনো লাইভ অ্যাক্টিভ ইউজার নেই। তবে আজকের দিনে {$countryStr} থেকে ট্র্যাফিকের সর্বোচ্চ ধারাবাহিকতা লক্ষ্য করা গেছে। নতুন কন্টেন্ট প্রচারের উপযুক্ত সময়।";
            $enText = "No active users online right now. Consistent visitor engagement was recorded from {$countryStr} today.";
        }

        return [
            'bn'          => $bnText,
            'en'          => $enText,
            'status'      => $liveCount > 0 ? 'active' : 'idle',
            'top_country' => $countryStr,
            'top_content' => $topPageTitle,
        ];
    }

    /**
     * AJAX endpoint: legacy support for apiStats.
     */
    public function apiStats()
    {
        $liveUsers = ActiveSession::where('is_bot', false)
            ->where('last_activity_at', '>=', now()->subSeconds(45))
            ->where('status', '!=', 'left')
            ->count();

        if ($liveUsers === 0) {
            $liveUsers = Visit::where('is_bot', false)
                ->where('created_at', '>=', now()->subMinutes(3))
                ->distinct('ip_address')
                ->count('ip_address');
        }

        $recentVisits = Visit::where('is_bot', false)
            ->latest()
            ->take(10)
            ->get(['ip_address', 'url', 'country', 'country_code', 'created_at']);

        return response()->json([
            'live_users'    => $liveUsers,
            'recent_visits' => $recentVisits,
        ]);
    }

    /**
     * Extract a readable bot name from a user-agent string.
     */
    private function extractBotName(string $ua): string
    {
        if (preg_match('/(googlebot|bingbot|yandexbot|baiduspider|duckduckbot|semrushbot|ahrefsbot)/i', $ua, $m)) {
            return ucfirst(strtolower($m[1]));
        }
        if (preg_match('/\(compatible;\s*([^;)]+)/i', $ua, $m)) {
            return trim($m[1]);
        }
        return strlen($ua) > 60 ? substr($ua, 0, 60) . '…' : $ua;
    }

    public function blockIp(Request $request)
    {
        $request->validate(['ip_address' => 'required|ip']);

        BlockedIp::firstOrCreate(['ip_address' => $request->ip_address], [
            'reason' => $request->reason ?? 'Blocked manually from Analytics Dashboard'
        ]);

        return back()->with('status', 'IP Address has been successfully blocked.');
    }

    public function unblockIp(Request $request)
    {
        $request->validate(['ip_address' => 'required|ip']);
        BlockedIp::where('ip_address', $request->ip_address)->delete();
        return back()->with('status', 'IP Address has been unblocked.');
    }

    public function export(Request $request)
    {
        $filter  = $request->input('date_filter', 'today');
        $query   = Visit::where('is_bot', false);
        $query   = $this->applyFilter($query, $filter);
        $visits  = $query->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=traffic_report_{$filter}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($visits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Session ID', 'IP Address', 'URL', 'Page Title', 'Country', 'City', 'Device', 'Browser', 'OS', 'Dwell Time (s)', 'Referrer', 'Date']);

            foreach ($visits as $visit) {
                fputcsv($file, [
                    $visit->id,
                    $visit->session_id,
                    $visit->ip_address,
                    $visit->url,
                    $visit->page_title,
                    $visit->country,
                    $visit->city,
                    $visit->device_type,
                    $visit->browser,
                    $visit->os,
                    $visit->dwell_seconds,
                    $visit->referrer,
                    $visit->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
