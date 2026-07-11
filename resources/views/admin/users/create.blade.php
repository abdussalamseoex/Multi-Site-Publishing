<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New User') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                &larr; Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <p class="text-sm font-bold text-red-700 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.users.store') }}" method="POST" class="divide-y divide-gray-100">
                    @csrf

                    {{-- Basic Info Section --}}
                    <div class="p-6">
                        <h3 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-black">1</span>
                            Basic Information
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    placeholder="e.g. John Smith"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                                    required>
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    placeholder="user@example.com"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                                    required>
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <select name="role" id="role"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('role') border-red-500 @enderror"
                                    required>
                                    <option value="">-- Select Role --</option>
                                    <option value="user"   {{ old('role') == 'user'   ? 'selected' : '' }}>User</option>
                                    <option value="author" {{ old('role') == 'author' ? 'selected' : '' }}>Author</option>
                                    <option value="editor" {{ old('role') == 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="admin"  {{ old('role') == 'admin'  ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('role')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="bio" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Bio / About <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <textarea name="bio" id="bio" rows="3"
                                    placeholder="A short description about this user..."
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
                                <p class="mt-1 text-xs text-gray-400">This bio will appear on the author's public profile page.</p>
                                @error('bio')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Password Section --}}
                    <div class="p-6">
                        <h3 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-black">2</span>
                            Password
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" id="password"
                                    placeholder="Min. 8 characters"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror"
                                    required>
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="Re-enter password"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- Limits & Points Section --}}
                    <div class="p-6">
                        <h3 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-black">3</span>
                            Points & Limits <span class="text-gray-400 font-normal text-sm">(optional)</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="points" class="block text-sm font-semibold text-gray-700 mb-1">Starting Points</label>
                                <input type="number" name="points" id="points" value="{{ old('points', 0) }}" min="0"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="daily_post_limit" class="block text-sm font-semibold text-gray-700 mb-1">Daily Post Limit</label>
                                <input type="number" name="daily_post_limit" id="daily_post_limit" value="{{ old('daily_post_limit') }}" min="1"
                                    placeholder="Use system default"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="total_post_limit" class="block text-sm font-semibold text-gray-700 mb-1">Total Post Limit</label>
                                <input type="number" name="total_post_limit" id="total_post_limit" value="{{ old('total_post_limit') }}" min="1"
                                    placeholder="Use system default"
                                    class="w-full border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <input type="checkbox" name="is_unlimited" id="is_unlimited" value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('is_unlimited') ? 'checked' : '' }}>
                            <label for="is_unlimited" class="text-sm font-semibold text-gray-700">Grant Unlimited Posts (overrides limits)</label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="p-6 bg-gray-50 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-5 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow">
                            ✓ Create User
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
