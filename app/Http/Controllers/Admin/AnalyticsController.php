<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

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

    public function index(\Illuminate\Http\Request $request)
    {
        $filter = $request->input('date_filter', 'all_time');
        
        $baseQuery = \App\Models\Visit::query();
        $query = $this->applyFilter(clone $baseQuery, $filter);

        $filteredTotal = (clone $query)->count();
        $totalVisits = \App\Models\Visit::count();
        $visitsToday = \App\Models\Visit::whereDate('created_at', today())->count();
        $liveUsers = \App\Models\Visit::where('created_at', '>=', now()->subMinutes(5))->distinct('ip_address')->count('ip_address');
        
        $topCountries = (clone $query)->selectRaw('country, country_code, count(*) as count')
            ->whereNotNull('country_code')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->take(10)
            ->get();
            
        $topReferrers = (clone $query)->selectRaw("referrer, count(*) as count")
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $topPages = (clone $query)->selectRaw("url, count(*) as count")
            ->whereNotNull('url')
            ->groupBy('url')
            ->orderByDesc('count')
            ->take(10)
            ->get();
            
        $recentVisits = (clone $query)->latest()->take(50)->get();

        // Bounce Rate Calculation
        // Sessions = unique IP addresses in the period
        // Bounces = IPs that appear only once in the period
        $bounceRate = 0;
        if ($filteredTotal > 0) {
            $ipCounts = (clone $query)->selectRaw('ip_address, count(*) as total_visits')
                ->groupBy('ip_address')
                ->pluck('total_visits', 'ip_address');
            
            $totalSessions = $ipCounts->count();
            $bounces = $ipCounts->filter(function ($visits) {
                return $visits == 1;
            })->count();

            if ($totalSessions > 0) {
                $bounceRate = round(($bounces / $totalSessions) * 100, 2);
            }
        }

        // Use Agent package for Devices, Browsers, and Bots
        $allUserAgents = (clone $query)->pluck('user_agent');
        $agent = new Agent();
        
        $devices = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0];
        $browsers = [];
        $bots = [];
        $botCount = 0;

        foreach ($allUserAgents as $ua) {
            if (empty($ua)) continue;
            
            $agent->setUserAgent($ua);
            
            if ($agent->isRobot()) {
                $botCount++;
                $robotName = $agent->robot();
                if ($robotName) {
                    $bots[$robotName] = ($bots[$robotName] ?? 0) + 1;
                } else {
                    $bots['Unknown Bot'] = ($bots['Unknown Bot'] ?? 0) + 1;
                }
            } else {
                // Devices
                if ($agent->isDesktop()) {
                    $devices['Desktop']++;
                } elseif ($agent->isTablet()) {
                    $devices['Tablet']++;
                } elseif ($agent->isMobile()) {
                    $devices['Mobile']++;
                } else {
                    $devices['Desktop']++; // Default fallback
                }

                // Browsers
                $browser = $agent->browser();
                if ($browser) {
                    $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
                }
            }
        }

        arsort($browsers);
        arsort($bots);
        $browsers = array_slice($browsers, 0, 5);
        $bots = array_slice($bots, 0, 5);

        return view('admin.analytics.index', compact(
            'totalVisits', 'visitsToday', 'filteredTotal', 
            'topCountries', 'topReferrers', 'topPages', 
            'recentVisits', 'filter', 'liveUsers', 
            'bounceRate', 'devices', 'browsers', 'bots', 'botCount'
        ));
    }

    public function export(Request $request)
    {
        $filter = $request->input('date_filter', 'all_time');
        
        $query = \App\Models\Visit::query();
        $query = $this->applyFilter($query, $filter);
        
        $visits = $query->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=traffic_report_{$filter}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($visits) {
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
