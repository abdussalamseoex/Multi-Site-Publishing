<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Menu Builder') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex gap-2">
                @foreach($menus as $m)
                    <a href="{{ route('admin.menus.index', ['id' => $m->id]) }}" class="px-4 py-2 rounded font-bold text-sm {{ $activeMenu->id == $m->id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 shadow-sm' }}">
                        {{ $m->name }}
                    </a>
                @endforeach
            </div>

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="space-y-6">
                    <!-- Add New Item -->
                    <div class="bg-white shadow sm:rounded-lg p-6 h-fit">
                        <h3 class="text-lg font-bold mb-4">Add Menu Item</h3>
                        <form action="{{ route('admin.menus.items.store', $activeMenu->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Link Title</label>
                                <input type="text" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">URL</label>
                                <input type="text" name="url" required placeholder="/page-url OR https://..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Parent Link</label>
                                <select name="parent_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    <option value="">None (Top Level)</option>
                                    @foreach($activeMenu->items->whereNull('parent_id') as $parentItem)
                                        <option value="{{ $parentItem->id }}">{{ $parentItem->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white rounded py-2 text-sm font-bold shadow hover:bg-indigo-700 transition">Add to Menu</button>
                        </form>
                    </div>

                    <!-- Import Categories -->
                    <div class="bg-white shadow sm:rounded-lg p-6 h-fit border border-blue-200">
                        <h3 class="text-lg font-bold mb-3 flex items-center gap-2 text-blue-800">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Advanced Import
                        </h3>
                        <form action="{{ route('admin.menus.import_categories', $activeMenu->id) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Categories to Import:</label>
                                <div class="max-h-40 overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50 space-y-1">
                                    @foreach($mainCategories as $cat)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100 p-1 rounded">
                                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="rounded text-blue-600 focus:ring-blue-500" checked>
                                            {{ $cat->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Import Mode:</label>
                                <select name="import_mode" id="import_mode" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" onchange="document.getElementById('dropdown_name_div').style.display = this.value === 'dropdown' ? 'block' : 'none'">
                                    <option value="top_level">Top Level (Directly in Menu)</option>
                                    <option value="dropdown">Inside a Dropdown</option>
                                </select>
                            </div>

                            <div class="mb-4" id="dropdown_name_div" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dropdown Menu Name:</label>
                                <input type="text" name="dropdown_name" value="Categories" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>

                            <button type="submit" onclick="return confirm('Import selected categories into this menu?')" class="w-full bg-blue-600 text-white rounded py-2 text-sm font-bold shadow hover:bg-blue-700 transition flex justify-center items-center gap-2">
                                Import Selected
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Menu Structure -->
                <div class="md:col-span-2 bg-white shadow sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Menu Structure: {{ $activeMenu->name }}</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Drag to Reorder</span>
                    </div>

                    <div id="menu-list" class="space-y-3">
                        @forelse($activeMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                            <div class="border border-gray-200 bg-gray-50 p-3 rounded shadow-sm cursor-move" data-id="{{ $item->id }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-gray-800">{{ $item->title }}</span>
                                        <span class="text-xs text-gray-500 ml-2">{{ $item->url }}</span>
                                    </div>
                                    <form action="{{ route('admin.menus.items.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 text-sm hover:text-red-700" onclick="return confirm('Are you sure?')">Remove</button>
                                    </form>
                                </div>
                                
                                <!-- Sub Items -->
                                @if($item->children->count() > 0)
                                <div class="mt-3 pl-6 space-y-2 border-l-2 border-indigo-200">
                                    @foreach($item->children->sortBy('order') as $child)
                                    <div class="border border-gray-200 bg-white p-2 rounded shadow-sm flex justify-between items-center">
                                        <div>
                                            <span class="font-semibold text-gray-700 text-sm">↳ {{ $child->title }}</span>
                                            <span class="text-xs text-gray-400 ml-2">{{ $child->url }}</span>
                                        </div>
                                        <form action="{{ route('admin.menus.items.destroy', $child->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 text-xs hover:text-red-700" onclick="return confirm('Are you sure?')">Remove</button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500 italic">No menu items added yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('menu-list');
            if (el) {
                new Sortable(el, {
                    animation: 150,
                    ghostClass: 'bg-blue-50',
                    onEnd: function (evt) {
                        let items = [];
                        el.querySelectorAll('div[data-id]').forEach(function(node) {
                            items.push(node.getAttribute('data-id'));
                        });

                        fetch("{{ route('admin.menus.reorder') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ items: items })
                        });
                    }
                });
            }
        });
    </script>
</x-app-layout>

