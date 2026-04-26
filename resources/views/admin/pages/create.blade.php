<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Static Page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <!-- Summernote Lite CSS/JS -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

                <form action="{{ route('admin.pages.store') }}" method="POST" id="page-form">
                    @csrf
                    
                    <div class="mb-6 border-b pb-4">
                        <label class="block text-lg font-bold text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="title" required class="block w-full border-gray-300 rounded-md shadow-sm sm:text-lg px-4 py-3" placeholder="e.g. About Us">
                    </div>

                    <div class="mb-6 border-b pb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Custom URL Slug (Optional)</label>
                        <input type="text" name="slug" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm px-4 py-2" placeholder="e.g. about-us-custom">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Page Content</label>
                        <textarea id="summernote" name="content"></textarea>
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 border rounded">
                        <h4 class="font-bold mb-2">SEO Settings (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Meta Title</label>
                                <input type="text" name="meta_title" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="e.g. seo, contact, about">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-700 mb-1">Meta Description</label>
                                <textarea name="meta_description" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded font-bold shadow hover:bg-indigo-700">Publish Page</button>
                    </div>
                </form>

                <script>
                    $(document).ready(function() {
                        $('#summernote').summernote({
                            placeholder: 'Write your page content here... Use the </> button for custom HTML code.',
                            tabsize: 2,
                            height: 400,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'picture', 'video']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ]
                        });
                    });

                    $('#page-form').on('submit', function() {
                        if ($('#summernote').summernote('isEmpty')) {
                            alert('Content cannot be empty');
                            return false;
                        }
                        return true;
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>

