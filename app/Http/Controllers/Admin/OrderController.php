<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'post')->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded'
        ]);

        $order->payment_status = $request->payment_status;
        
        if ($request->payment_status === 'paid' && $order->post) {
            $order->post->status = 'published';
            $order->post->save();
        }

        $order->save();

        return back()->with('status', 'Order status updated successfully');
    }
}
