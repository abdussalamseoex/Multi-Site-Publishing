<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Search Users</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="author" {{ request('role') == 'author' ? 'selected' : '' }}>Author</option>
                            <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>Editor</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>
                    <div class="flex space-x-2 w-full md:w-auto">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white shadow-sm rounded-md text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-200 transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats / Limits</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Role / Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->email }}<br>
                                    <span class="text-xs text-gray-400">Joined: {{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="font-bold text-indigo-600 mb-1">Points: {{ $user->points }}</div>
                                    <div class="text-xs">
                                        Total Posts: {{ $user->total_posts }} @if(!$user->is_unlimited) / {{ $user->total_post_limit ?? \App\Models\Setting::get('default_total_post_limit', 10) }} @else / ∞ @endif
                                    </div>
                                    <div class="text-xs">
                                        DoFollow: 
                                        @if(is_null($user->dofollow_default))
                                            <span class="text-gray-400">System Default</span>
                                        @elseif($user->dofollow_default)
                                            <span class="text-green-600 font-bold">Always DoFollow</span>
                                        @else
                                            <span class="text-red-600 font-bold">Always NoFollow</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm flex justify-end gap-2">
                                    <form action="{{ route('admin.users.role', $user->id) }}" method="POST">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="text-xs font-bold border-gray-300 rounded shadow-sm py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                            @php $currentRole = $user->roles->first()->name ?? 'user'; @endphp
                                            <option value="user" {{ $currentRole == 'user' ? 'selected' : '' }}>User</option>
                                            <option value="author" {{ $currentRole == 'author' ? 'selected' : '' }}>Author</option>
                                            <option value="editor" {{ $currentRole == 'editor' ? 'selected' : '' }}>Editor</option>
                                            <option value="admin" {{ $currentRole == 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </form>

                                    <button type="button" onclick="openLimitModal({{ $user->id }}, {{ $user->points }}, {{ $user->daily_post_limit ?? 'null' }}, {{ $user->total_post_limit ?? 'null' }}, {{ $user->is_unlimited ? 'true' : 'false' }}, '{{ is_null($user->dofollow_default) ? '' : ($user->dofollow_default ? '1' : '0') }}', '{{ addslashes($user->name) }}')" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-bold rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">Limits</button>

                                    <form action="{{ route('admin.users.toggle-ban', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->status === 'banned')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-bold rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none" onclick="return confirm('Unban this user?')">Unban</button>
                                        @else
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-bold rounded shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none" onclick="return confirm('Ban this user? They will not be able to log in.')">Ban</button>
                                        @endif
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-bold rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none" onclick="return confirm('Delete this user permanently AND all of their submitted posts?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $users->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Limits & Points Modal -->
    <div id="limitModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeLimitModal()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="limitForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Edit Limits & Points: <span id="modalUserName" class="text-indigo-600 font-bold"></span>
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Account Points (Top-up/Deduct)</label>
                                        <input type="number" name="points" id="modalPoints" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Daily Limit Override</label>
                                            <input type="number" name="daily_post_limit" id="modalDailyLimit" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Default">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Total Limit Override</label>
                                            <input type="number" name="total_post_limit" id="modalTotalLimit" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Default">
                                        </div>
                                    </div>
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="is_unlimited" id="modalIsUnlimited" value="1" class="rounded border-gray-300 text-indigo-600">
                                        <label class="ml-2 block text-sm text-gray-900 font-bold">Grant Unlimited Posts</label>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">DoFollow Override</label>
                                        <select name="dofollow_default" id="modalDofollow" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                            <option value="">System Default</option>
                                            <option value="1">Always DoFollow</option>
                                            <option value="0">Always NoFollow</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Save Changes
                        </button>
                        <button type="button" onclick="closeLimitModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openLimitModal(userId, points, daily, total, unlimited, dofollow, name) {
            document.getElementById('limitForm').action = '/admin/users/' + userId + '/limits';
            document.getElementById('modalUserName').innerText = name;
            document.getElementById('modalPoints').value = points;
            document.getElementById('modalDailyLimit').value = daily !== null ? daily : '';
            document.getElementById('modalTotalLimit').value = total !== null ? total : '';
            document.getElementById('modalIsUnlimited').checked = unlimited;
            document.getElementById('modalDofollow').value = dofollow;
            document.getElementById('limitModal').classList.remove('hidden');
        }

        function closeLimitModal() {
            document.getElementById('limitModal').classList.add('hidden');
        }
    </script>
</x-app-layout>

