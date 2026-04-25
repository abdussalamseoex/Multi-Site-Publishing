<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Universal Theme Builder') }}
            </h2>
            <div class="bg-indigo-100 text-indigo-800 px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider">
                Active Theme: {{ $activeTheme }}
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="homepageBuilder()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.theme.options.store') }}" method="POST" id="builder-form">
                @csrf
                <input type="hidden" name="layout_data" :value="JSON.stringify(blocks)">
                <input type="hidden" name="sidebar_data" :value="JSON.stringify(sidebarBlocks)">

                <div class="flex justify-between items-center mb-6">
                    <p class="text-gray-600">You are currently editing the layout for the <strong class="uppercase text-indigo-600">{{ $activeTheme }}</strong> theme. Any changes saved here will only apply to this theme.</p>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded font-bold shadow-sm hover:bg-indigo-700 transition">Save All Layouts</button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left: Available Blocks -->
                    <div class="lg:col-span-3">
                        <div class="bg-white shadow sm:rounded-lg p-5 sticky top-6">
                            <h3 class="text-base font-bold mb-4 border-b pb-2">Main Body Blocks</h3>
                            <div class="space-y-2 mb-8">
                                <template x-for="type in availableTypes" :key="type.id">
                                    <button type="button" @click="addBlock(type.id)" class="w-full text-left px-3 py-2 border border-gray-200 rounded hover:border-indigo-500 hover:bg-indigo-50 transition flex items-center justify-between text-sm">
                                        <span class="font-bold text-gray-700" x-text="type.name"></span>
                                        <span class="text-indigo-500 font-bold">+</span>
                                    </button>
                                </template>
                            </div>

                            <h3 class="text-base font-bold mb-4 border-b pb-2">Sidebar Widgets</h3>
                            <div class="space-y-2">
                                <template x-for="type in sidebarTypes" :key="type.id">
                                    <button type="button" @click="addSidebarBlock(type.id)" class="w-full text-left px-3 py-2 border border-gray-200 rounded hover:border-purple-500 hover:bg-purple-50 transition flex items-center justify-between text-sm">
                                        <span class="font-bold text-gray-700" x-text="type.name"></span>
                                        <span class="text-purple-500 font-bold">+</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: Main Content Builder -->
                    <div class="lg:col-span-6">
                        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                            <div class="p-4 bg-indigo-50 border-b border-indigo-100">
                                <h3 class="font-bold text-indigo-900">Main Content Layout (Left Side)</h3>
                            </div>
                            
                            <div class="p-4 space-y-4 bg-gray-50 min-h-[500px]" id="sortable-blocks">
                                
                                <template x-if="blocks.length === 0">
                                    <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded">
                                        <p class="text-gray-500 text-sm">No main blocks added yet.</p>
                                    </div>
                                </template>

                                <template x-for="(block, index) in blocks" :key="block.id">
                                    <div class="bg-white border border-gray-200 rounded shadow-sm flex flex-col relative transition-all">
                                        
                                        <!-- Block Header -->
                                        <div class="p-2 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="flex flex-col gap-1 cursor-pointer">
                                                    <button type="button" @click="moveUp(index, 'main')" :disabled="index === 0" class="text-gray-400 hover:text-indigo-600 disabled:opacity-30 leading-none">▲</button>
                                                    <button type="button" @click="moveDown(index, 'main')" :disabled="index === blocks.length - 1" class="text-gray-400 hover:text-indigo-600 disabled:opacity-30 leading-none">▼</button>
                                                </div>
                                                <span class="font-bold text-sm text-gray-800" x-text="getTypeName(block.type)"></span>
                                            </div>
                                            <button type="button" @click="removeBlock(index, 'main')" class="text-red-500 hover:text-red-700 text-xs font-bold px-2 py-1 bg-red-50 rounded">Remove</button>
                                        </div>

                                        <!-- Block Settings -->
                                        <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-if="block.type !== 'ad_block'">
                                                <div class="col-span-1 md:col-span-2">
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Section Title</label>
                                                    <input type="text" x-model="block.title" class="w-full border-gray-300 rounded shadow-sm text-xs py-1.5" placeholder="e.g. Latest News">
                                                </div>
                                            </template>

                                            <template x-if="block.type !== 'latest_news' && block.type !== 'ad_block'">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Select Category</label>
                                                    <select x-model="block.category_id" class="w-full border-gray-300 rounded shadow-sm text-xs py-1.5">
                                                        <option value="">Select a category</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </template>

                                            <template x-if="block.type !== 'ad_block'">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Number of Posts</label>
                                                    <input type="number" min="1" max="20" x-model="block.limit" class="w-full border-gray-300 rounded shadow-sm text-xs py-1.5">
                                                </div>
                                            </template>

                                            <template x-if="block.type === 'ad_block'">
                                                <div class="col-span-1 md:col-span-2">
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Ad Code (HTML/JS)</label>
                                                    <textarea x-model="block.ad_code" rows="3" class="w-full border-gray-300 rounded shadow-sm text-xs py-1.5 font-mono text-purple-700" placeholder="Paste your ad code here..."></textarea>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sidebar Builder -->
                    <div class="lg:col-span-3">
                        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                            <div class="p-4 bg-purple-50 border-b border-purple-100">
                                <h3 class="font-bold text-purple-900">Sidebar Layout (Right Side)</h3>
                            </div>
                            
                            <div class="p-4 space-y-3 bg-gray-50 min-h-[500px]" id="sortable-sidebar">
                                
                                <template x-if="sidebarBlocks.length === 0">
                                    <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded">
                                        <p class="text-gray-500 text-sm">No sidebar widgets added.</p>
                                    </div>
                                </template>

                                <template x-for="(block, index) in sidebarBlocks" :key="block.id">
                                    <div class="bg-white border border-gray-200 rounded shadow-sm flex flex-col relative transition-all">
                                        
                                        <!-- Sidebar Block Header -->
                                        <div class="p-2 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <div class="flex flex-col gap-1 cursor-pointer">
                                                    <button type="button" @click="moveUp(index, 'sidebar')" :disabled="index === 0" class="text-gray-400 hover:text-purple-600 disabled:opacity-30 leading-none text-xs">▲</button>
                                                    <button type="button" @click="moveDown(index, 'sidebar')" :disabled="index === sidebarBlocks.length - 1" class="text-gray-400 hover:text-purple-600 disabled:opacity-30 leading-none text-xs">▼</button>
                                                </div>
                                                <span class="font-bold text-xs text-gray-800" x-text="getSidebarTypeName(block.type)"></span>
                                            </div>
                                            <button type="button" @click="removeBlock(index, 'sidebar')" class="text-red-500 hover:text-red-700 text-[10px] font-bold px-2 py-0.5 bg-red-50 rounded">Del</button>
                                        </div>

                                        <!-- Sidebar Block Settings -->
                                        <div class="p-2">
                                            <template x-if="block.type !== 'ad_block' && block.type !== 'social_counter' && block.type !== 'newsletter'">
                                                <div class="mb-2">
                                                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Widget Title</label>
                                                    <input type="text" x-model="block.title" class="w-full border-gray-300 rounded shadow-sm text-xs py-1" placeholder="Title">
                                                </div>
                                            </template>
                                            
                                            <template x-if="block.type === 'popular_posts' || block.type === 'categories_list'">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Limit</label>
                                                    <input type="number" min="1" max="10" x-model="block.limit" class="w-full border-gray-300 rounded shadow-sm text-xs py-1">
                                                </div>
                                            </template>

                                            <template x-if="block.type === 'ad_block'">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Ad Code (HTML/JS)</label>
                                                    <textarea x-model="block.ad_code" rows="3" class="w-full border-gray-300 rounded shadow-sm text-xs py-1 font-mono text-purple-700" placeholder="Paste 300x250 ad code..."></textarea>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        function homepageBuilder() {
            return {
                blocks: @json($homepageLayout),
                sidebarBlocks: @json($sidebarLayout),
                
                availableTypes: [
                    { id: 'hero_grid', name: 'Hero Grid (Top News)' },
                    { id: 'latest_news', name: 'Latest Articles List' },
                    { id: 'category_spotlight', name: 'Category Spotlight' },
                    { id: 'category_grid', name: '3-Column Grid' },
                    { id: 'ad_block', name: 'Advertisement Banner' }
                ],
                
                sidebarTypes: [
                    { id: 'social_counter', name: 'Stay Connected (Social)' },
                    { id: 'popular_posts', name: 'Most Popular Posts' },
                    { id: 'categories_list', name: 'Categories List' },
                    { id: 'ad_block', name: 'Sidebar Ad (300x250)' },
                    { id: 'newsletter', name: 'Newsletter Subscribe' }
                ],
                
                addBlock(type) {
                    let newBlock = {
                        id: Math.random().toString(36).substr(2, 9),
                        type: type,
                        title: this.getDefaultTitle(type),
                        category_id: '',
                        limit: this.getDefaultLimit(type),
                        ad_code: ''
                    };
                    this.blocks.push(newBlock);
                },

                addSidebarBlock(type) {
                    let newBlock = {
                        id: Math.random().toString(36).substr(2, 9),
                        type: type,
                        title: this.getSidebarDefaultTitle(type),
                        limit: this.getSidebarDefaultLimit(type),
                        ad_code: ''
                    };
                    this.sidebarBlocks.push(newBlock);
                },

                removeBlock(index, context) {
                    if (confirm('Remove this block?')) {
                        if (context === 'main') this.blocks.splice(index, 1);
                        else this.sidebarBlocks.splice(index, 1);
                    }
                },

                moveUp(index, context) {
                    let arr = context === 'main' ? this.blocks : this.sidebarBlocks;
                    if (index > 0) {
                        let temp = arr[index];
                        arr[index] = arr[index - 1];
                        arr[index - 1] = temp;
                    }
                },

                moveDown(index, context) {
                    let arr = context === 'main' ? this.blocks : this.sidebarBlocks;
                    if (index < arr.length - 1) {
                        let temp = arr[index];
                        arr[index] = arr[index + 1];
                        arr[index + 1] = temp;
                    }
                },

                getTypeName(typeId) {
                    const type = this.availableTypes.find(t => t.id === typeId);
                    return type ? type.name : typeId;
                },

                getSidebarTypeName(typeId) {
                    const type = this.sidebarTypes.find(t => t.id === typeId);
                    return type ? type.name : typeId;
                },

                getDefaultTitle(type) {
                    switch(type) {
                        case 'hero_grid': return 'Top Stories';
                        case 'latest_news': return 'Latest Articles';
                        case 'category_spotlight': return 'Editorial Choice';
                        case 'category_grid': return 'More News';
                        case 'legacy_theme_content': return 'Original Theme Design';
                        default: return '';
                    }
                },

                getDefaultLimit(type) {
                    switch(type) {
                        case 'hero_grid': return 4;
                        case 'latest_news': return 6;
                        case 'category_spotlight': return 5;
                        case 'category_grid': return 6;
                        case 'legacy_theme_content': return null;
                        default: return 4;
                    }
                },

                getSidebarDefaultTitle(type) {
                    switch(type) {
                        case 'popular_posts': return 'Most Popular';
                        case 'categories_list': return 'Categories';
                        default: return '';
                    }
                },

                getSidebarDefaultLimit(type) {
                    switch(type) {
                        case 'popular_posts': return 5;
                        case 'categories_list': return 6;
                        default: return null;
                    }
                }
            }
        }
    </script>
</x-app-layout>
