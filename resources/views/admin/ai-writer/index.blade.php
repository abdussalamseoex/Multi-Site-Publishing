<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('AI Bulk Writer') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="mb-6 text-gray-600">Enter your keywords (one per line) and let the AI generate SEO-optimized articles based on EEAT principles.</p>

                    <form id="ai-writer-form" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Keywords (One per line)</label>
                            <textarea id="keywords" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required placeholder="Best laptops 2024&#10;How to lose weight fast&#10;Top 10 travel destinations"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Category</label>
                                <select id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Content Language</label>
                                <select id="language" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="English">English</option>
                                    <option value="Bengali">Bengali</option>
                                    <option value="Hindi">Hindi</option>
                                    <option value="Spanish">Spanish</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Generate Title from Keyword?</label>
                                <select id="generate_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="yes">Yes (AI Generates Title)</option>
                                    <option value="no">No (Use Keyword as Title)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Article Length</label>
                                <select id="article_length" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="500">Short (~500 Words)</option>
                                    <option value="800" selected>Medium (~800 Words)</option>
                                    <option value="1200">Long (~1200 Words)</option>
                                    <option value="1500">Very Long (~1500 Words)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 text-indigo-600 font-bold">Assign Author</label>
                                <select id="user_id" class="mt-1 block w-full rounded-md border-indigo-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->role }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- SEO & Links -->
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 mb-6">
                            <h3 class="text-indigo-900 font-bold text-sm mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                SEO & Outbound Links
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" id="enable_outbound_links" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="enable_outbound_links" class="text-sm font-medium text-gray-700">Add Outbound Links (Wikipedia, News, etc.)</label>
                                </div>
                                <div id="outbound_count_wrapper" class="opacity-50 pointer-events-none transition-opacity">
                                    <label class="block font-medium text-xs text-gray-500 uppercase mb-1">Links per Article</label>
                                    <select id="outbound_links_count" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="1">1 Link</option>
                                        <option value="2">2 Links</option>
                                        <option value="3">3 Links</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <div>
                                <label class="block font-bold text-sm text-slate-800 mb-3 uppercase tracking-wider">Featured Image Sources</label>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="featured_image_sources[]" value="pexels" checked class="w-4 h-4 text-indigo-600 rounded">
                                        <label class="text-sm text-gray-700">Pexels (Free Stock)</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="featured_image_sources[]" value="unsplash" class="w-4 h-4 text-indigo-600 rounded">
                                        <label class="text-sm text-gray-700">Unsplash (Free Stock)</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="featured_image_sources[]" value="google" class="w-4 h-4 text-indigo-600 rounded">
                                        <label class="text-sm text-gray-700">Google Images (Creative Commons)</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="featured_image_sources[]" value="dalle" class="w-4 h-4 text-indigo-600 rounded">
                                        <label class="text-sm text-gray-700">DALL-E 3 (AI Generated)</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-sm text-slate-800 mb-3 uppercase tracking-wider">In-Content Image Sources</label>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block font-medium text-xs text-gray-500 uppercase mb-2">Select Platforms</label>
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="in_content_image_sources[]" value="pexels" checked class="w-4 h-4 text-indigo-600 rounded">
                                                <label class="text-sm text-gray-700">Pexels</label>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="in_content_image_sources[]" value="unsplash" class="w-4 h-4 text-indigo-600 rounded">
                                                <label class="text-sm text-gray-700">Unsplash</label>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="in_content_image_sources[]" value="google" class="w-4 h-4 text-indigo-600 rounded">
                                                <label class="text-sm text-gray-700">Google Images</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block font-medium text-xs text-gray-500 uppercase mb-1">Max Images</label>
                                        <select id="in_content_images_count" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="0">No Images</option>
                                            <option value="1">1 Image</option>
                                            <option value="2">2 Images</option>
                                            <option value="3" selected>3 Images</option>
                                            <option value="5">5 Images</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-indigo-50 p-4 rounded border border-indigo-100">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Post Status</label>
                                <select id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="published">Publish Immediately</option>
                                    <option value="draft">Save as Draft</option>
                                    <option value="scheduled">Schedule (Drip Feed)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Schedule Interval (Minutes)</label>
                                <input type="number" id="schedule_interval" value="60" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <p class="text-xs text-gray-500 mt-1">If 'Schedule' is selected, wait this long between each post's publish time.</p>
                            </div>
                        </div>

                        <div>
                            <button type="submit" id="start-btn" class="bg-indigo-600 text-white px-6 py-2 rounded shadow hover:bg-indigo-700">
                                Start Generating
                            </button>
                        </div>
                    </form>

                    <!-- Campaign Area -->
                    <div id="campaign-area" class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-gray-900">Active Campaigns & History</h3>
                            <button onclick="loadCampaigns()" class="text-indigo-600 text-sm hover:underline flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Refresh
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto bg-gray-50 rounded-xl border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Campaign</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Progress</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Next Run</th>
                                        <th class="px-4 py-3 text-right font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="campaign-list" class="bg-white divide-y divide-gray-100">
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading campaigns...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- History Area (Recently Generated Posts) -->
                    <div class="mt-10 border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">Recently Generated Posts</h3>
                            <a href="{{ route('admin.posts.index') }}" class="text-indigo-600 text-sm hover:underline">Manage All Posts &rarr;</a>
                        </div>
                        
                        @if($recentPosts && count($recentPosts) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Title</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Category</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentPosts as $p)
                                        <tr>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('admin.posts.edit', $p->id) }}" class="text-indigo-600 hover:underline">{{ $p->title }}</a>
                                            </td>
                                            <td class="px-4 py-2 text-gray-500">{{ $p->category->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $p->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($p->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-gray-500">{{ $p->created_at->format('M d, Y h:i A') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No recent posts found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let countdownIntervals = {};

        document.getElementById('enable_outbound_links').addEventListener('change', function() {
            const wrapper = document.getElementById('outbound_count_wrapper');
            if (this.checked) {
                wrapper.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                wrapper.classList.add('opacity-50', 'pointer-events-none');
            }
        });

        document.getElementById('ai-writer-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('start-btn');
            const keywordsRaw = document.getElementById('keywords').value;
            const keywords = keywordsRaw.split('\n').map(k => k.trim()).filter(k => k !== '');
            
            if (keywords.length === 0) return alert('Please enter at least one keyword.');

            const category_id = document.getElementById('category_id').value;
            const language = document.getElementById('language').value;
            const generate_title = document.getElementById('generate_title').value;
            const article_length = parseInt(document.getElementById('article_length').value);
            const user_id = document.getElementById('user_id').value;
            const enable_outbound_links = document.getElementById('enable_outbound_links').checked;
            const outbound_links_count = document.getElementById('outbound_links_count').value;
            
            // Collect multi-source images
            const featured_image_sources = Array.from(document.querySelectorAll('input[name="featured_image_sources[]"]:checked')).map(cb => cb.value);
            const in_content_image_sources = Array.from(document.querySelectorAll('input[name="in_content_image_sources[]"]:checked')).map(cb => cb.value);

            const in_content_images_count = document.getElementById('in_content_images_count').value;
            const status = document.getElementById('status').value;
            const schedule_interval = parseInt(document.getElementById('schedule_interval').value);

            btn.disabled = true;
            btn.innerText = 'Starting Campaign...';

            try {
                const response = await fetch("{{ route('admin.ai-writer.bulk-start') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        keywords: keywordsRaw,
                        category_id,
                        language,
                        generate_title,
                        article_length,
                        user_id,
                        enable_outbound_links,
                        outbound_links_count,
                        featured_image_sources,
                        in_content_image_sources,
                        in_content_images_count,
                        status,
                        schedule_interval
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    document.getElementById('keywords').value = '';
                    loadCampaigns();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error("AI Writer Error:", error);
                alert('An error occurred. Please check console.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Start Generating';
            }
        });

        async function loadCampaigns() {
            try {
                const response = await fetch("{{ route('admin.ai-writer.campaigns') }}");
                const data = await response.json();
                const list = document.getElementById('campaign-list');
                
                if (data.data.length === 0) {
                    list.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No campaigns found.</td></tr>';
                    return;
                }

                // Clear previous countdowns
                Object.values(countdownIntervals).forEach(clearInterval);
                countdownIntervals = {};

                list.innerHTML = '';
                data.data.forEach(c => {
                    const progress = Math.round((c.processed_count / c.total_count) * 100);
                    const statusClass = {
                        'pending': 'bg-gray-100 text-gray-800',
                        'processing': 'bg-blue-100 text-blue-800 animate-pulse',
                        'completed': 'bg-green-100 text-green-800',
                        'failed': 'bg-red-100 text-red-800',
                        'paused': 'bg-yellow-100 text-yellow-800'
                    }[c.status];

                    const nextRunDate = c.next_run_at ? new Date(c.next_run_at) : null;
                    const nextRunText = nextRunDate ? nextRunDate.toLocaleString() : 'N/A';
                    const canPause = c.status === 'processing' || c.status === 'pending';
                    const canResume = c.status === 'paused';

                    list.innerHTML += `
                        <tr class="hover:bg-gray-50 transition" id="campaign-row-${c.id}">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900">${c.name}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold">${c.category ? c.category.name : 'N/A'}</span>
                                    <span class="text-[10px] text-gray-300">|</span>
                                    <span class="text-[10px] text-indigo-400 font-bold">Author: ${c.user ? c.user.name : 'N/A'}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: ${progress}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-indigo-600">${c.processed_count}/${c.total_count}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full uppercase ${statusClass}">
                                    ${c.status}
                                </span>
                                ${c.error_log ? `<div class="text-[10px] text-red-500 mt-1 truncate max-w-[150px]" title="${c.error_log}">Error: ${c.error_log.split('\n').pop()}</div>` : ''}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                                <div id="countdown-${c.id}">${nextRunText}</div>
                                ${nextRunDate && c.status !== 'completed' && c.status !== 'failed' && c.status !== 'paused' ? 
                                    `<div class="text-[10px] text-indigo-500 font-bold mt-1" id="timer-text-${c.id}">Calculating...</div>` : ''}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                ${canPause ? `<button onclick="toggleCampaign(${c.id})" class="text-yellow-600 hover:text-yellow-800 font-bold text-[10px] uppercase">Pause</button>` : ''}
                                ${canResume ? `<button onclick="toggleCampaign(${c.id})" class="text-green-600 hover:text-green-800 font-bold text-[10px] uppercase">Resume</button>` : ''}
                                <button onclick="deleteCampaign(${c.id})" class="text-red-600 hover:text-red-800 font-bold text-[10px] uppercase">Delete</button>
                            </td>
                        </tr>
                    `;

                    if (nextRunDate && c.status !== 'completed' && c.status !== 'failed' && c.status !== 'paused') {
                        startCountdown(c.id, nextRunDate);
                    }
                });
            } catch (error) {
                console.error("Load Campaigns Error:", error);
            }
        }

        function startCountdown(id, date) {
            const timerEl = document.getElementById(`timer-text-${id}`);
            if (!timerEl) return;

            function update() {
                const now = new Date().getTime();
                const distance = date.getTime() - now;

                if (distance < 0) {
                    timerEl.innerText = "Next post due now...";
                    clearInterval(countdownIntervals[id]);
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerEl.innerText = `Next in: ${hours}h ${minutes}m ${seconds}s`;
            }

            update();
            countdownIntervals[id] = setInterval(update, 1000);
        }

        async function toggleCampaign(id) {
            try {
                const response = await fetch(`/admin/ai-writer/campaigns/${id}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                });
                if (response.ok) loadCampaigns();
            } catch (error) { console.error(error); }
        }

        async function deleteCampaign(id) {
            if (!confirm('Are you sure you want to delete this campaign?')) return;
            try {
                const response = await fetch(`/admin/ai-writer/campaigns/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                });
                if (response.ok) loadCampaigns();
            } catch (error) { console.error(error); }
        }

        // Auto refresh every 30 seconds
        setInterval(loadCampaigns, 30000);
        loadCampaigns();
    </script>
    @endpush
</x-app-layout>
