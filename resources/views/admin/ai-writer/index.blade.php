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
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded border">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Featured Image</label>
                                <select id="featured_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="pexels">Free (Pexels)</option>
                                    <option value="unsplash">Free (Unsplash)</option>
                                    <option value="dalle">AI Generated (DALL-E 3)</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">In-Content Images Count</label>
                                <input type="number" id="in_content_images_count" value="1" min="0" max="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">In-Content Image Source</label>
                                <select id="in_content_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="pexels">Free (Pexels)</option>
                                    <option value="unsplash">Free (Unsplash)</option>
                                    <option value="dalle">AI Generated (DALL-E 3)</option>
                                    <option value="none">None</option>
                                </select>
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

                    <!-- Progress Area -->
                    <div id="progress-area" class="mt-8 hidden border-t pt-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-lg text-gray-900">Generation Progress <span id="progress-text" class="text-indigo-600 font-bold ml-2">0/0</span></h3>
                            <div class="text-right">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider block">Est. Time Per Post</span>
                                <span id="estimated-time" class="text-sm font-bold text-indigo-600 block">~45-60 seconds</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4 overflow-hidden">
                            <div id="progress-bar" class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        
                        <div class="flex justify-between items-center bg-indigo-50 border border-indigo-100 rounded-lg p-3 mb-4">
                            <div class="flex items-center space-x-2">
                                <svg class="animate-spin w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span class="text-sm font-semibold text-indigo-900">Current Post Processing Time:</span>
                            </div>
                            <span id="live-timer" class="text-lg font-mono font-bold text-indigo-700">00:00</span>
                        </div>

                        <ul id="log" class="bg-gray-900 text-green-400 font-mono text-sm p-4 rounded-lg h-64 overflow-y-auto space-y-1 shadow-inner leading-relaxed">
                            <li>Waiting for initialization...</li>
                        </ul>
                    </div>

                    <!-- History Area -->
                    <div class="mt-10 border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">Recently Generated Posts</h3>
                            <a href="{{ route('posts.index') }}" class="text-indigo-600 text-sm hover:underline">Manage All Posts &rarr;</a>
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
                                                <a href="{{ route('posts.edit', $p->id) }}" class="text-indigo-600 hover:underline">{{ $p->title }}</a>
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
        document.getElementById('ai-writer-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('start-btn');
            const logArea = document.getElementById('log');
            const progressArea = document.getElementById('progress-area');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            const keywordsRaw = document.getElementById('keywords').value;
            const keywords = keywordsRaw.split('\n').map(k => k.trim()).filter(k => k !== '');
            
            if (keywords.length === 0) return alert('Please enter at least one keyword.');

            btn.disabled = true;
            btn.innerText = 'Generating...';
            progressArea.classList.remove('hidden');
            logArea.innerHTML = '';
            
            const category_id = document.getElementById('category_id').value;
            const language = document.getElementById('language').value;
            const generate_title = document.getElementById('generate_title').value;
            const article_length = parseInt(document.getElementById('article_length').value);
            const featured_image_source = document.getElementById('featured_image_source').value;
            const in_content_images_count = document.getElementById('in_content_images_count').value;
            const in_content_image_source = document.getElementById('in_content_image_source').value;
            const status = document.getElementById('status').value;
            const schedule_interval = parseInt(document.getElementById('schedule_interval').value);
            
            let currentTime = new Date();
            let timerInterval;

            for (let i = 0; i < keywords.length; i++) {
                const keyword = keywords[i];
                logArea.innerHTML += `<li>[${new Date().toLocaleTimeString()}] Starting: <strong>${keyword}</strong>...</li>`;
                logArea.scrollTop = logArea.scrollHeight;

                // Start Live Timer
                let secondsElapsed = 0;
                document.getElementById('live-timer').innerText = '00:00';
                timerInterval = setInterval(() => {
                    secondsElapsed++;
                    let m = Math.floor(secondsElapsed / 60).toString().padStart(2, '0');
                    let s = (secondsElapsed % 60).toString().padStart(2, '0');
                    document.getElementById('live-timer').innerText = `${m}:${s}`;
                }, 1000);

                let scheduleTime = null;
                if (status === 'scheduled') {
                    // add interval
                    currentTime.setMinutes(currentTime.getMinutes() + schedule_interval);
                    scheduleTime = currentTime.toISOString();
                }

                try {
                    const response = await fetch("{{ route('admin.ai-writer.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            keyword,
                            category_id,
                            language,
                            generate_title,
                            article_length,
                            featured_image_source,
                            in_content_images_count,
                            in_content_image_source,
                            status,
                            schedule_time: scheduleTime
                        })
                    });

                    clearInterval(timerInterval);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    if (result.success) {
                        logArea.innerHTML += `<li class="text-green-300">[${new Date().toLocaleTimeString()}] Success: ${result.message} (Took ${secondsElapsed}s)</li>`;
                    } else {
                        logArea.innerHTML += `<li class="text-red-400">[${new Date().toLocaleTimeString()}] Failed: ${result.message}</li>`;
                    }
                } catch (error) {
                    clearInterval(timerInterval);
                    console.error("AI Writer Error:", error);
                    logArea.innerHTML += `<li class="text-red-400">[${new Date().toLocaleTimeString()}] Error: ${error.message}. Please check Developer Console (F12) for details.</li>`;
                }

                // Update Progress
                let percent = ((i + 1) / keywords.length) * 100;
                progressBar.style.width = percent + '%';
                progressText.innerText = `${i + 1}/${keywords.length}`;
                logArea.scrollTop = logArea.scrollHeight;
            }

            btn.disabled = false;
            btn.innerText = 'Start Generating';
            logArea.innerHTML += `<li><strong>[${new Date().toLocaleTimeString()}] All tasks completed!</strong></li>`;
            logArea.scrollTop = logArea.scrollHeight;
        });
    </script>
    @endpush
</x-app-layout>
