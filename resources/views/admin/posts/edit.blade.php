<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Post (Admin)') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('frontend.post', $post->slug) }}" target="_blank" class="px-3 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-md text-sm font-medium transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Preview
                </a>
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium hover:bg-gray-700 transition">Back to Posts</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">

                    @if($errors->any())
                        <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6 text-sm">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data"
                          x-data="{
                              status: '{{ old('status', $post->status) }}',
                              setScheduleOffset(hours) {
                                  const d = new Date();
                                  d.setHours(d.getHours() + hours);
                                  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                                  if (this.$refs.scheduleInput) {
                                      this.$refs.scheduleInput.value = d.toISOString().slice(0, 16);
                                  }
                              },
                              setScheduleTomorrow(hour) {
                                  const d = new Date();
                                  d.setDate(d.getDate() + 1);
                                  d.setHours(hour, 0, 0, 0);
                                  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                                  if (this.$refs.scheduleInput) {
                                      this.$refs.scheduleInput.value = d.toISOString().slice(0, 16);
                                  }
                              }
                          }">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Article Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required value="{{ old('title', $post->title) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg px-4 py-3" placeholder="Enter an engaging title...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custom URL Slug <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2" placeholder="e.g. {{ $post->slug }}">
                            </div>

                            <!-- Row 1: Category & Searchable Author Assignment -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                    <select name="category_id" required class="mt-1 block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Searchable Author Combobox -->
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ old('user_id', $post->user_id) }}',
                                    users: {{ Js::from($users->map(fn($u) => [
                                        'id' => $u->id,
                                        'name' => $u->name,
                                        'email' => $u->email,
                                        'role' => $u->role ?? ($u->roles->first()->name ?? 'Author')
                                    ])) }},
                                    get selectedUser() {
                                        return this.users.find(u => String(u.id) === String(this.selectedId)) || this.users[0] || null;
                                    },
                                    get filteredUsers() {
                                        if (!this.search.trim()) return this.users;
                                        const q = this.search.toLowerCase();
                                        return this.users.filter(u => (u.name && u.name.toLowerCase().includes(q)) || (u.email && u.email.toLowerCase().includes(q)));
                                    },
                                    selectUser(user) {
                                        this.selectedId = user.id;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }" class="relative">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Assign Author <span class="text-indigo-600 font-bold">&bull; Search</span>
                                        </label>
                                        <a href="{{ route('admin.users.create') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Add New Author
                                        </a>
                                    </div>

                                    <input type="hidden" name="user_id" :value="selectedId">

                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" @click.away="open = false" class="w-full bg-white border border-gray-300 rounded-md p-2 text-left flex items-center justify-between shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 hover:border-gray-400 transition">
                                        <div class="flex items-center gap-2.5 truncate">
                                            <template x-if="selectedUser">
                                                <div class="flex items-center gap-2.5 truncate">
                                                    <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs uppercase flex-shrink-0" x-text="selectedUser.name.charAt(0)"></div>
                                                    <div class="truncate">
                                                        <div class="text-sm font-bold text-gray-800 leading-tight truncate" x-text="selectedUser.name"></div>
                                                        <div class="text-[11px] text-gray-500 leading-tight truncate" x-text="selectedUser.email + ' (' + selectedUser.role + ')'"></div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedUser">
                                                <span class="text-gray-400 text-sm">-- Select Author --</span>
                                            </template>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- Dropdown Menu with Search -->
                                    <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
                                        <div class="p-2 border-b border-gray-100 bg-gray-50">
                                            <div class="relative">
                                                <input type="text" x-model="search" x-ref="searchInput" placeholder="Type author name or email..." class="w-full text-xs pl-8 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" @keydown.escape="open = false">
                                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            </div>
                                        </div>

                                        <div class="max-h-56 overflow-y-auto divide-y divide-gray-50">
                                            <template x-for="user in filteredUsers" :key="user.id">
                                                <div @click="selectUser(user)" class="p-2.5 hover:bg-indigo-50 cursor-pointer flex items-center justify-between transition-colors" :class="{ 'bg-indigo-50 font-semibold': String(user.id) === String(selectedId) }">
                                                    <div class="flex items-center gap-2.5 truncate">
                                                        <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0" x-text="user.name.charAt(0)"></div>
                                                        <div class="truncate">
                                                            <div class="text-xs font-bold text-gray-900 truncate" x-text="user.name"></div>
                                                            <div class="text-[11px] text-gray-500 truncate" x-text="user.email"></div>
                                                        </div>
                                                    </div>
                                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-gray-100 text-gray-600" x-text="user.role"></span>
                                                </div>
                                            </template>
                                            <template x-if="filteredUsers.length === 0">
                                                <div class="p-4 text-center text-xs text-gray-400 italic">No author found matching "<span x-text="search"></span>".</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Publish Status & Post Scheduling Widget -->
                            <div class="border border-gray-200 p-4 rounded-lg bg-gray-50/50 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Publish Status</label>
                                    <select name="status" x-model="status" class="mt-1 block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-white shadow-sm">
                                        <option value="draft">Draft</option>
                                        <option value="pending">Pending Review</option>
                                        <option value="published">Published (সরাসরি প্রকাশ)</option>
                                        <option value="scheduled">⏰ Scheduled (শিডিউল করে রাখুন)</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                <!-- Post Schedule Box (Expands when status === 'scheduled') -->
                                <div x-show="status === 'scheduled'" x-cloak x-transition.opacity.duration.200ms class="p-4 bg-purple-50/70 border border-purple-200 rounded-lg space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                                            <span>⏰ Schedule Publication Date & Time</span>
                                        </label>
                                        <span class="text-[11px] text-purple-700 font-mono bg-purple-100 px-2 py-0.5 rounded">Server: {{ config('app.timezone') }}</span>
                                    </div>
                                    
                                    <div>
                                        <input type="datetime-local" name="scheduled_at" x-ref="scheduleInput" value="{{ old('scheduled_at', $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '') }}" class="w-full text-sm border-purple-300 focus:ring-purple-500 focus:border-purple-500 rounded-md bg-white shadow-sm">
                                        @if($post->scheduled_at && $post->status === 'scheduled')
                                            <p class="text-xs text-purple-800 font-medium mt-1">Currently scheduled for: {{ $post->scheduled_at->format('F d, Y - h:i A') }} ({{ $post->scheduled_at->diffForHumans() }})</p>
                                        @else
                                            <p class="text-xs text-purple-700 mt-1">This post will automatically go live when the scheduled date & time is reached.</p>
                                        @endif
                                    </div>

                                    <!-- Quick Schedule Presets -->
                                    <div class="flex items-center gap-2 flex-wrap pt-1">
                                        <span class="text-xs font-bold text-purple-900">Quick Presets:</span>
                                        <button type="button" @click="setScheduleOffset(1)" class="text-xs bg-white text-purple-700 hover:bg-purple-100 px-2.5 py-1 rounded border border-purple-200 font-medium transition shadow-sm">+1 Hour</button>
                                        <button type="button" @click="setScheduleOffset(3)" class="text-xs bg-white text-purple-700 hover:bg-purple-100 px-2.5 py-1 rounded border border-purple-200 font-medium transition shadow-sm">+3 Hours</button>
                                        <button type="button" @click="setScheduleTomorrow(9)" class="text-xs bg-white text-purple-700 hover:bg-purple-100 px-2.5 py-1 rounded border border-purple-200 font-medium transition shadow-sm">Tomorrow 9:00 AM</button>
                                        <button type="button" @click="setScheduleTomorrow(20)" class="text-xs bg-white text-purple-700 hover:bg-purple-100 px-2.5 py-1 rounded border border-purple-200 font-medium transition shadow-sm">Tomorrow 8:00 PM</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Change Featured Image</label>
                                @if($post->featured_image)
                                    <div class="mb-2">
                                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" alt="Current Image" class="h-32 object-cover rounded shadow-sm border">
                                    </div>
                                @endif
                                <input type="file" name="featured_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing image.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custom OG Image <span class="text-xs text-gray-400 font-normal">(Optional - Used for social sharing)</span></label>
                                @if($post->og_image)
                                    <div class="mb-2 border p-2 inline-block rounded bg-gray-50">
                                        <p class="text-xs text-gray-500 mb-1">Current OG Image:</p>
                                        <img src="{{ Str::startsWith($post->og_image, 'http') ? $post->og_image : url($post->og_image) }}" alt="Current OG image" class="h-16 rounded shadow">
                                    </div>
                                @endif
                                <input type="file" name="og_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">If left blank, the system will automatically use the Featured Image.</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Detailed Content</label>
                                </div>
                                <textarea id="tinymce-editor" name="content">{!! old('content', $post->content) !!}</textarea>
                            </div>

                            <!-- SEO Settings Box -->
                            <div class="border rounded-lg p-5 bg-gray-50 mt-6">
                                <h3 class="text-md font-semibold text-gray-700 mb-4">SEO Override (Optional)</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Leave blank to use Article Title">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                                        <textarea name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Leave blank to auto-generate from content">{{ old('meta_description', $post->meta_description) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="e.g. news, technology, software">
                                        <p class="mt-1 text-xs text-gray-500">Comma-separated SEO keywords.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700 focus:outline-none transition">
                                    Update Post
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE CDN Init -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#tinymce-editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons template help',
        toolbar: 'undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code fullscreen preview',
        images_upload_url: '{{ route('tinymce.upload') }}',
        automatic_uploads: true,
        images_reuse_filename: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
      });
    </script>
</x-app-layout>
