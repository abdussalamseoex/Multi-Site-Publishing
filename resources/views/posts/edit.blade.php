<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    
                    @if($errors->any())
                        <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6 text-sm shadow-sm border border-red-100">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg mb-6 shadow-sm">
                        <h4 class="font-bold text-lg">Note: Pending Review</h4>
                        <p class="text-sm mt-1">If you update this post, it will be automatically sent to <strong>Pending</strong> status until an admin approves the changes, even if it is currently published.</p>
                    </div>

                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Article Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg px-4 py-3" placeholder="Enter an engaging title...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custom URL Slug <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <input type="text" name="slug" value="{{ old('slug', $post->original_slug) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. my-custom-article-url">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Featured Image (Optional)</label>
                                <input type="file" name="featured_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-4 py-2 bg-white">
                                @if($post->featured_image)
                                    <div class="mt-2">
                                        <img src="{{ $post->featured_image }}" alt="Current image" class="h-20 rounded shadow">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custom OG Image <span class="text-xs text-gray-400 font-normal">(Optional - Used for social sharing)</span></label>
                                <input type="file" name="og_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-4 py-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">If left blank, the system will automatically use the Featured Image.</p>
                                @if($post->og_image)
                                    <div class="mt-2 border p-2 inline-block rounded bg-gray-50">
                                        <p class="text-xs text-gray-500 mb-1">Current OG Image:</p>
                                        <img src="{{ $post->og_image }}" alt="Current OG image" class="h-16 rounded shadow">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Editor Content</label>

                                </div>
                                <textarea id="tinymce-editor" name="content">{!! old('content', $post->content) !!}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category (Optional)</label>
                                    <select name="category_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (old('category_id', $post->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- SEO Settings Box -->
                            <div class="border rounded-lg p-5 bg-gray-50 mt-6">
                                <h3 class="text-md font-semibold text-gray-700 mb-4">SEO Override (Optional)</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Leave blank to use Article Title">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                                        <textarea name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Leave blank to auto-generate from content">{{ old('meta_description', $post->meta_description) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. news, technology, software">
                                        <p class="mt-1 text-xs text-gray-500">Comma-separated SEO keywords.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    Save Changes & Submit for Review
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
      var hasDofollowPermission = @json(isset($userHasDofollowPermission) ? $userHasDofollowPermission : false);
      var relList = hasDofollowPermission 
          ? [
              { title: 'None', value: '' },
              { title: 'Nofollow', value: 'nofollow' },
              { title: 'Sponsored', value: 'sponsored' },
              { title: 'UGC', value: 'ugc' }
            ] 
          : [
              { title: 'Nofollow', value: 'nofollow' }
            ];

      tinymce.init({
        selector: '#tinymce-editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons template help',
        toolbar: 'undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code fullscreen preview',
        menubar: 'file edit view insert format tools table help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
        
        // Image upload configuration
        images_upload_url: '{{ route('tinymce.upload') }}',
        automatic_uploads: true,
        images_upload_credentials: true,
        file_picker_types: 'image',

        // Nofollow link option natively supported
        rel_list: relList,
        default_link_target: '_blank',

        // Force rel nofollow if user does not have permission
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });

            if (!hasDofollowPermission) {
                editor.on('NodeChange', function(e) {
                    if (e.element.nodeName === 'A') {
                        editor.dom.setAttrib(e.element, 'rel', 'nofollow');
                    }
                });
            }
        },

      });
    </script>
</x-app-layout>
