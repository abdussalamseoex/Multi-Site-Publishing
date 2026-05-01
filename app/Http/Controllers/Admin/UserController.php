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

    public function updateLimits(Request $request, User $user)
    {
        $request->validate([
            'points' => 'required|integer',
            'daily_post_limit' => 'nullable|integer|min:1',
            'total_post_limit' => 'nullable|integer|min:1',
            'dofollow_default' => 'nullable|boolean',
        ]);

        $user->points = $request->points;
        $user->daily_post_limit = $request->daily_post_limit;
        $user->total_post_limit = $request->total_post_limit;
        $user->is_unlimited = $request->has('is_unlimited');
        $user->dofollow_default = $request->dofollow_default;
        
        $user->save();

        return back()->with('status', "Limits and Points updated for {$user->name}.");
    }

    /**
     * Bulk operations for users
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');
        
        if (empty($ids)) {
            return back()->withErrors(['error' => 'No users selected.']);
        }

        // Prevent self-action for sensitive operations
        $userIds = array_diff($ids, [auth()->id()]);
        if (empty($userIds) && count($ids) > 0) {
            return back()->withErrors(['error' => 'You cannot perform bulk actions on yourself.']);
        }

        $query = User::whereIn('id', $userIds);
        $message = 'Bulk action completed.';

        switch ($action) {
            case 'delete':
                \App\Models\Post::whereIn('user_id', $userIds)->delete();
                User::whereIn('id', $userIds)->delete();
                $message = count($userIds) . ' users and their posts have been permanently deleted.';
                break;

            case 'ban':
                $query->update(['status' => 'banned']);
                $message = count($userIds) . ' users have been banned.';
                break;

            case 'unban':
                $query->update(['status' => 'active']);
                $message = count($userIds) . ' users have been unbanned.';
                break;

            case 'change_role':
                $role = $request->input('role');
                if (in_array($role, ['admin', 'author', 'editor', 'user'])) {
                    $users = $query->get();
                    foreach ($users as $u) {
                        $u->syncRoles([$role]);
                    }
                    $message = count($userIds) . ' users role changed to ' . ucfirst($role) . '.';
                }
                break;

            case 'set_points':
                $points = (int)$request->input('value');
                $query->update(['points' => $points]);
                $message = 'Points set to ' . $points . ' for ' . count($userIds) . ' users.';
                break;

            case 'add_points':
                $points = (int)$request->input('value');
                $query->increment('points', $points);
                $message = $points . ' points added to ' . count($userIds) . ' users.';
                break;

            case 'update_limits':
                $updateData = [];
                
                // Only update fields that are provided/filled to avoid overwriting with nulls unless intended
                if ($request->filled('points')) {
                    $updateData['points'] = (int)$request->input('points');
                }
                
                if ($request->filled('daily_post_limit')) {
                    $updateData['daily_post_limit'] = (int)$request->input('daily_post_limit');
                }
                
                if ($request->filled('total_post_limit')) {
                    $updateData['total_post_limit'] = (int)$request->input('total_post_limit');
                }
                
                // For checkboxes in bulk, we might need a specific 'set_unlimited' flag
                if ($request->has('apply_unlimited')) {
                    $updateData['is_unlimited'] = $request->has('is_unlimited');
                }
                
                if ($request->filled('dofollow_default')) {
                    $val = $request->input('dofollow_default');
                    $updateData['dofollow_default'] = ($val === 'null') ? null : (bool)$val;
                }
                
                if (!empty($updateData)) {
                    $query->update($updateData);
                    $message = 'Limits and points updated for ' . count($userIds) . ' users.';
                } else {
                    return back()->withErrors(['error' => 'No limit values were provided to update.']);
                }
                break;

            default:
                return back()->withErrors(['error' => 'Invalid bulk action selected.']);
        }

        return back()->with('status', $message);
    }
}
