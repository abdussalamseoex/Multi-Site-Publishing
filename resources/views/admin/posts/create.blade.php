<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Write New Post (Admin)') }}
            </h2>
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium hover:bg-gray-700 transition">Back to Posts</a>
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

                    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="space-y-6">
                            <div class="mb-6 border-b pb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Article Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required value="{{ old('title') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg px-4 py-3" placeholder="Enter an engaging title...">
                            </div>

                            <div class="mb-6 border-b pb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Custom URL Slug <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2" placeholder="e.g. my-custom-article-url">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select name="category_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Publish Status</label>
                                    <select name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Featured Image</label>
                                <input type="file" name="featured_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Detailed Content</label>
                                <div id="quill-editor" style="height: 400px; background: white;">{!! old('content') !!}</div>
                                <input type="hidden" name="content" id="content-hidden">
                            </div>

                            <!-- SEO Settings Box -->
                            <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-widest mb-4">SEO & Metadata</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Leave blank to use post title">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="e.g. news, technology, software">
                                        <p class="mt-1 text-xs text-gray-500">Comma-separated SEO keywords.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                                        <textarea name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Leave blank to auto-generate from content...">{{ old('meta_description') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t flex justify-end">
                                <button type="submit" onclick="document.getElementById('content-hidden').value = quill.root.innerHTML" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700 focus:outline-none transition">
                                    Create Post
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

