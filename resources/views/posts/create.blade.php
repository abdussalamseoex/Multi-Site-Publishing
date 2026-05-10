<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Write New Post') }}
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

                    @if(isset($eligibleForPromo) && $eligibleForPromo)
                        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6 shadow-sm flex items-start gap-3">
                            <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                            <div>
                                <h4 class="font-bold text-lg">Special Promotion Active! 🎉</h4>
                                <p class="text-sm mt-1">Good news! You have a promotional free post available today. Your account points will <strong>not</strong> be deducted for this submission. Enjoy!</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Article Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg px-4 py-3" placeholder="Enter an engaging title...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custom URL Slug <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. my-custom-article-url">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Featured Image (Optional)</label>
                                <input type="file" name="featured_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-4 py-2 bg-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Editor</label>
                                <!-- Quill Editor -->
                                <div id="quill-editor" style="height: 400px; background: white;">{!! old('content') !!}</div>
                                <input type="hidden" name="content" id="content-hidden">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category (Optional)</label>
                                    <select name="category_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Leave blank to use Article Title">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                                        <textarea name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Leave blank to auto-generate from content"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. news, technology, software">
                                        <p class="mt-1 text-xs text-gray-500">Comma-separated SEO keywords.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t flex justify-end">
                                <button type="submit" onclick="document.getElementById('content-hidden').value = quill.root.innerHTML" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Quill.js CDN Init -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
      var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
          toolbar: [
            ['bold', 'italic', 'underline', 'strike'], 
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }], 
            [{ 'size': ['small', false, 'large', 'huge'] }], 
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['clean']                                         
          ]
        }
      });
    </script>
</x-app-layout>

