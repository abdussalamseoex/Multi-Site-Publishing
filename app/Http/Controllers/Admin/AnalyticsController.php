<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlockedIp;
use App\Models\Visit;

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
        // Default to 'today' instead of all_time
        $filter = $request->input('date_filter', 'today');

        // Base query — human visitors only (exclude bots)
        $humanBase = Visit::where('is_bot', false);
        $query = $this->applyFilter(clone $humanBase, $filter);

        $filteredTotal  = (clone $query)->count();
        $totalVisits    = Visit::where('is_bot', false)->count();
        $visitsToday    = Visit::where('is_bot', false)->whereDate('created_at', today())->count();
        $liveUsers      = Visit::where('is_bot', false)
                               ->where('created_at', '>=', now()->subMinutes(5))
                               ->distinct('ip_address')
                               ->count('ip_address');

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

        // Bounce Rate Calculation (human visitors only)
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

        // Device / Browser detection (human visitors only)
        $allUserAgents = (clone $query)->pluck('user_agent');

        $devices  = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0];
        $browsers = [];

        foreach ($allUserAgents as $ua) {
            if (empty($ua)) continue;

            // Device Detection
            if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
                $devices['Tablet']++;
            } elseif (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/i', $ua)) {
                $devices['Mobile']++;
            } else {
                $devices['Desktop']++;
            }

            // Browser Detection
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

        // ---- Bot Traffic (separate from human metrics) ----
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

        // For display: aggregate into readable bot names
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

        // Recent bot visits for the Bot Traffic tab
        $recentBotVisits = (clone $botQuery)->latest()->take(30)->get();
        $totalGoodBots   = Visit::where('is_bot', true)->where('bot_type', 'good')->count();
        $totalBadBots    = Visit::where('is_bot', true)->where('bot_type', 'bad')->count();

        $blockedIps     = BlockedIp::latest()->get();
        $blockedIpList  = $blockedIps->pluck('ip_address')->toArray();

        return view('admin.analytics.index', compact(
            'totalVisits', 'visitsToday', 'filteredTotal', 'uniqueUsers',
            'topCountries', 'topReferrers', 'topPages',
            'recentVisits', 'filter', 'liveUsers', 'blockedIps', 'blockedIpList',
            'bounceRate', 'devices', 'browsers',
            'goodBots', 'badBots', 'recentBotVisits', 'totalGoodBots', 'totalBadBots'
        ));
    }

    /**
     * AJAX endpoint: returns live metrics for real-time polling.
     */
    public function apiStats()
    {
        $liveUsers = Visit::where('is_bot', false)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('ip_address')
            ->count('ip_address');

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
            fputcsv($file, ['ID', 'IP Address', 'URL', 'Referrer', 'User Agent', 'Country', 'Country Code', 'Date']);

            foreach ($visits as $visit) {
                fputcsv($file, [
                    $visit->id,
                    $visit->ip_address,
                    $visit->url,
                    $visit->referrer,
                    $visit->user_agent,
                    $visit->country,
                    $visit->country_code,
                    $visit->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
