<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTopupRequest;
use Illuminate\Http\Request;

class TopupRequestController extends Controller
{
    public function index()
    {
        $requests = PointTopupRequest::with('user')->latest()->paginate(20);
        return view('admin.topup.index', compact('requests'));
    }

    public function update(Request $request, PointTopupRequest $topupRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if ($topupRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $topupRequest->status = $request->status;
        $topupRequest->save();

        if ($request->status === 'approved') {
            $topupRequest->user->increment('points', $topupRequest->requested_points);
            return back()->with('status', "Request approved. {$topupRequest->requested_points} points added to {$topupRequest->user->name}'s account.");
        }

        return back()->with('status', 'Request has been rejected.');
    }
}
