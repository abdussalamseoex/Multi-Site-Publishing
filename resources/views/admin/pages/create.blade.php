<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Static Page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <!-- Quill CSS -->
                <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                
                <form action="{{ route('admin.pages.store') }}" method="POST" id="page-form">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-lg font-bold text-gray-700 mb-2">Page Title</label>
                        <input type="text" name="title" required class="block w-full border-gray-300 rounded-md shadow-sm sm:text-lg px-4 py-3" placeholder="e.g. About Us">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Page Content</label>
                        <div id="editor-container" class="h-64 bg-white"></div>
                        <input type="hidden" name="content" id="content">
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 border rounded">
                        <h4 class="font-bold mb-2">SEO Settings (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700">Meta Title</label>
                                <input type="text" name="meta_title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700">Meta Description</label>
                                <textarea name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded font-bold shadow hover:bg-indigo-700">Publish Page</button>
                    </div>
                </form>

                <!-- Quill JS -->
                <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                <script>
                    var quill = new Quill('#editor-container', {
                        theme: 'snow',
                        placeholder: 'Write the page content here...',
                        modules: {
                            toolbar: [
                                [{ 'header': [2, 3, false] }],
                                ['bold', 'italic', 'underline'],
                                ['link', 'image', 'video'],
                                [{'list': 'ordered'}, {'list': 'bullet'}],
                                ['clean']
                            ]
                        }
                    });

                    var form = document.getElementById('page-form');
                    form.onsubmit = function() {
                        var content = document.querySelector('input[name=content]');
                        content.value = quill.root.innerHTML;
                        if(quill.getText().trim().length === 0) {
                            alert('Content cannot be empty');
                            return false;
                        }
                        return true;
                    };
                </script>
            </div>
        </div>
    </div>
</x-app-layout>

