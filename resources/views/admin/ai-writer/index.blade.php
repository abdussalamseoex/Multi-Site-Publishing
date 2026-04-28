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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <div id="progress-area" class="mt-8 hidden">
                        <h3 class="font-bold text-lg mb-2">Generation Progress <span id="progress-text" class="text-indigo-600">0/0</span></h3>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                            <div class="bg-indigo-600 h-4 rounded-full transition-all duration-500" id="progress-bar" style="width: 0%"></div>
                        </div>
                        <ul id="log" class="text-sm text-gray-600 bg-gray-50 p-4 rounded border h-64 overflow-y-auto space-y-2 font-mono">
                        </ul>
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
            const featured_image_source = document.getElementById('featured_image_source').value;
            const in_content_images_count = document.getElementById('in_content_images_count').value;
            const in_content_image_source = document.getElementById('in_content_image_source').value;
            const status = document.getElementById('status').value;
            const schedule_interval = parseInt(document.getElementById('schedule_interval').value);
            
            let currentTime = new Date();

            for (let i = 0; i < keywords.length; i++) {
                const keyword = keywords[i];
                logArea.innerHTML += `<li>[${new Date().toLocaleTimeString()}] Starting: ${keyword}...</li>`;
                logArea.scrollTop = logArea.scrollHeight;

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
                            featured_image_source,
                            in_content_images_count,
                            in_content_image_source,
                            status,
                            schedule_time: scheduleTime
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        logArea.innerHTML += `<li class="text-green-600">[${new Date().toLocaleTimeString()}] Success: ${result.message}</li>`;
                    } else {
                        logArea.innerHTML += `<li class="text-red-600">[${new Date().toLocaleTimeString()}] Failed: ${result.message}</li>`;
                    }
                } catch (error) {
                    logArea.innerHTML += `<li class="text-red-600">[${new Date().toLocaleTimeString()}] Error: Server Timeout or Network issue.</li>`;
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
