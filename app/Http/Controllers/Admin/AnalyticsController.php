<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $totalVisits = \App\Models\Visit::count();
        $visitsToday = \App\Models\Visit::whereDate('created_at', today())->count();
        
        $topCountries = \App\Models\Visit::selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->take(10)
            ->get();
            
        $topReferrers = \App\Models\Visit::selectRaw("referrer, count(*) as count")
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->take(10)
            ->get();
            
        $recentVisits = \App\Models\Visit::latest()->take(50)->get();

        return view('admin.analytics.index', compact('totalVisits', 'visitsToday', 'topCountries', 'topReferrers', 'recentVisits'));
    }
}
