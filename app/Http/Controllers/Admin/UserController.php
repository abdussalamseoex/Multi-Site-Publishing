<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $sort = $request->input('sort', 'latest');
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        }

        $users = $query->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,editor,author,user'
        ]);

        $user->syncRoles([$request->role]);

        return back()->with('status', 'User role updated successfully.');
    }

    public function toggleBan(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'You cannot ban yourself.');
        }

        $user->status = $user->status === 'banned' ? 'active' : 'banned';
        $user->save();

        $action = $user->status === 'banned' ? 'banned' : 'unbanned';
        return back()->with('status', 'User has been ' . $action . ' successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'You cannot delete yourself.');
        }

        // Delete associated posts
        \App\Models\Post::where('user_id', $user->id)->delete();
        
        $user->delete();

        return back()->with('status', 'User and all their posts have been permanently deleted.');
    }
}
