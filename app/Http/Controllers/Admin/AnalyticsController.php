<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $filter = $request->input('date_filter', 'all_time');
        
        $query = \App\Models\Visit::query();
        
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

        $filteredTotal = (clone $query)->count();
        $totalVisits = \App\Models\Visit::count();
        $visitsToday = \App\Models\Visit::whereDate('created_at', today())->count();
        $liveUsers = \App\Models\Visit::where('created_at', '>=', now()->subMinutes(5))->distinct('ip_address')->count('ip_address');
        
        $topCountries = (clone $query)->selectRaw('country, country_code, count(*) as count')
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
            
        $recentVisits = (clone $query)->latest()->take(50)->get();

        return view('admin.analytics.index', compact('totalVisits', 'visitsToday', 'filteredTotal', 'topCountries', 'topReferrers', 'recentVisits', 'filter', 'liveUsers'));
    }
}
