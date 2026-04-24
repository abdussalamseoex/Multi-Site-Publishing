<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PointTopupRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    public function index()
    {
        $basePrice = Setting::get('guest_post_base_price', 50.00);
        $requests = PointTopupRequest::where('user_id', Auth::id())->latest()->get();
        
        return view('user.topup.index', compact('basePrice', 'requests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_points' => 'required|integer|min:1',
            'transaction_id' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        PointTopupRequest::create([
            'user_id' => Auth::id(),
            'requested_points' => $request->requested_points,
            'transaction_id' => $request->transaction_id,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Your top-up request has been submitted successfully and is pending admin approval.');
    }
}
