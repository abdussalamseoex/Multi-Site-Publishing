<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theme Options / Homepage Builder') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="homepageBuilder()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Sidebar / Add Block -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white shadow sm:rounded-lg p-6 sticky top-6">
                        <h3 class="text-lg font-bold mb-4">Add New Block</h3>
                        <p class="text-xs text-gray-500 mb-6">Build your dynamic homepage by adding blocks. The "GOOD" theme supports these block layouts.</p>
                        
                        <div class="space-y-4">
                            <template x-for="type in availableTypes" :key="type.id">
                                <button type="button" @click="addBlock(type.id)" class="w-full text-left px-4 py-3 border border-gray-200 rounded hover:border-indigo-500 hover:bg-indigo-50 transition group flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-sm text-gray-800 group-hover:text-indigo-700" x-text="type.name"></div>
                                        <div class="text-xs text-gray-500" x-text="type.desc"></div>
                                    </div>
                                    <span class="text-indigo-500 font-bold">+</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Main Builder Area -->
                <div class="w-full md:w-2/3">
                    <form action="{{ route('admin.theme.options.store') }}" method="POST" id="builder-form">
                        @csrf
                        <input type="hidden" name="layout_data" :value="JSON.stringify(blocks)">

                        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-bold text-gray-700">Homepage Layout Structure</h3>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-bold shadow-sm hover:bg-indigo-700 transition">Save Layout</button>
                            </div>
                            
                            <div class="p-6 space-y-4 bg-gray-100 min-h-[400px]" id="sortable-blocks">
                                
                                <template x-if="blocks.length === 0">
                                    <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded">
                                        <p class="text-gray-500 text-sm">No blocks added yet. Add a block from the left.</p>
                                    </div>
                                </template>

                                <template x-for="(block, index) in blocks" :key="block.id">
                                    <div class="bg-white border border-gray-200 rounded shadow-sm flex flex-col relative transition-all">
                                        
                                        <!-- Block Header -->
                                        <div class="p-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center cursor-move">
                                            <div class="flex items-center gap-3">
                                                <div class="flex flex-col gap-1 cursor-pointer">
                                                    <button type="button" @click="moveUp(index)" :disabled="index === 0" class="text-gray-400 hover:text-indigo-600 disabled:opacity-30">▲</button>
                                                    <button type="button" @click="moveDown(index)" :disabled="index === blocks.length - 1" class="text-gray-400 hover:text-indigo-600 disabled:opacity-30">▼</button>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-sm text-gray-800" x-text="getTypeName(block.type)"></span>
                                                    <span class="text-xs text-gray-400 ml-2">ID: <span x-text="block.id"></span></span>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeBlock(index)" class="text-red-500 hover:text-red-700 text-sm font-bold px-2 py-1 bg-red-50 rounded">Remove</button>
                                        </div>

                                        <!-- Block Settings -->
                                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            
                                            <template x-if="block.type !== 'ad_block'">
                                                <div class="col-span-1 md:col-span-2">
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Section Title</label>
                                                    <input type="text" x-model="block.title" class="w-full border-gray-300 rounded shadow-sm text-sm" placeholder="e.g. Latest News">
                                                </div>
                                            </template>

                                            <template x-if="block.type !== 'latest_news' && block.type !== 'ad_block'">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Select Category</label>
                                                    <select x-model="block.category_id" class="w-full border-gray-300 rounded shadow-sm text-sm">
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
                                                    <input type="number" min="1" max="20" x-model="block.limit" class="w-full border-gray-300 rounded shadow-sm text-sm">
                                                </div>
                                            </template>

                                            <template x-if="block.type === 'ad_block'">
                                                <div class="col-span-1 md:col-span-2">
                                                    <p class="text-sm text-gray-600 bg-yellow-50 p-3 rounded border border-yellow-200">This block will render the Ad placement associated with the "home_middle" or specific homepage ad slot. Ensure you have configured it in the Ads section.</p>
                                                </div>
                                            </template>

                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function homepageBuilder() {
            return {
                blocks: @json($homepageLayout),
                availableTypes: [
                    { id: 'hero_grid', name: 'Hero Grid (Top Layout)', desc: '1 large post, 2 small posts side by side. Perfect for top of the page.' },
                    { id: 'latest_news', name: 'Latest Articles List', desc: 'A vertical list of the latest global posts.' },
                    { id: 'category_spotlight', name: 'Category Spotlight', desc: '1 large featured post on left, list of small posts on right.' },
                    { id: 'category_grid', name: '3-Column Category Grid', desc: 'Standard 3-column grid of posts for a specific category.' },
                    { id: 'ad_block', name: 'Advertisement Block', desc: 'Display a banner ad between sections.' }
                ],
                
                addBlock(type) {
                    let newBlock = {
                        id: Math.random().toString(36).substr(2, 9),
                        type: type,
                        title: this.getDefaultTitle(type),
                        category_id: '',
                        limit: this.getDefaultLimit(type)
                    };
                    this.blocks.push(newBlock);
                },

                removeBlock(index) {
                    if (confirm('Remove this block?')) {
                        this.blocks.splice(index, 1);
                    }
                },

                moveUp(index) {
                    if (index > 0) {
                        let temp = this.blocks[index];
                        this.blocks[index] = this.blocks[index - 1];
                        this.blocks[index - 1] = temp;
                    }
                },

                moveDown(index) {
                    if (index < this.blocks.length - 1) {
                        let temp = this.blocks[index];
                        this.blocks[index] = this.blocks[index + 1];
                        this.blocks[index + 1] = temp;
                    }
                },

                getTypeName(typeId) {
                    const type = this.availableTypes.find(t => t.id === typeId);
                    return type ? type.name : typeId;
                },

                getDefaultTitle(type) {
                    switch(type) {
                        case 'hero_grid': return 'Top Stories';
                        case 'latest_news': return 'Latest Articles';
                        case 'category_spotlight': return 'Editorial Choice';
                        case 'category_grid': return 'Pro Lifestyle';
                        default: return '';
                    }
                },

                getDefaultLimit(type) {
                    switch(type) {
                        case 'hero_grid': return 4;
                        case 'latest_news': return 6;
                        case 'category_spotlight': return 5;
                        case 'category_grid': return 6;
                        default: return 4;
                    }
                }
            }
        }
    </script>
</x-app-layout>
