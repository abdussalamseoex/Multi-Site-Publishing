<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function checkout($postId)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($postId);
        $basePrice = Setting::get('guest_post_base_price', '50.00');
        $dofollowPrice = Setting::get('addon_dofollow_price', '20.00');
        $fastApprovalPrice = Setting::get('addon_fast_approval_price', '10.00');

        return view('orders.checkout', compact('post', 'basePrice', 'dofollowPrice', 'fastApprovalPrice'));
    }

    public function process(Request $request, $postId)
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($postId);
        
        $basePrice = (float) Setting::get('guest_post_base_price', '50.00');
        $dofollowPrice = (float) Setting::get('addon_dofollow_price', '20.00');
        $fastApprovalPrice = (float) Setting::get('addon_fast_approval_price', '10.00');

        $totalPrice = $basePrice;

        $addonDofollow = $request->has('addon_dofollow');
        $addonFastApproval = $request->has('addon_fast_approval');

        if ($addonDofollow) $totalPrice += $dofollowPrice;
        if ($addonFastApproval) $totalPrice += $fastApprovalPrice;

        Order::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'base_price' => $basePrice,
            'addon_dofollow' => $addonDofollow,
            'addon_fast_approval' => $addonFastApproval,
            'total_price' => $totalPrice,
            'payment_status' => 'pending', 
            // Mock payment logic since "No payment gateway" is strict for MVP, just standard pending
        ]);

        return redirect()->route('posts.index')->with('success', 'Your Guest Post order has been submitted! It is pending review.');
    }
}
