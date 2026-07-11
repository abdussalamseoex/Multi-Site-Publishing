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
                                <label class="block text-sm font-medium text-gray-700">Custom OG Image <span class="text-xs text-gray-400 font-normal">(Optional - Used for social sharing)</span></label>
                                <input type="file" name="og_image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">If left blank, the system will automatically use the Featured Image.</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Detailed Content</label>
                                    <button type="button" onclick="openQuillHtmlModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-black text-white text-xs font-bold rounded-md shadow transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        Edit Custom HTML (&lt;/&gt;)
                                    </button>
                                </div>
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
      // Pre-process existing links to build the nofollow map
      window.quillNofollowLinks = {};
      var editorDiv = document.getElementById('quill-editor');
      if (editorDiv) {
          var existingLinks = editorDiv.getElementsByTagName('a');
          for (var i = 0; i < existingLinks.length; i++) {
              var href = existingLinks[i].getAttribute('href');
              var rel = existingLinks[i].getAttribute('rel');
              if (href && rel && rel.toLowerCase().indexOf('nofollow') !== -1) {
                  window.quillNofollowLinks[href] = true;
              } else if (href) {
                  window.quillNofollowLinks[href] = false;
              }
          }
      }

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

      // Add Nofollow Checkbox to Quill Link Tooltip
      var tooltip = quill.theme.tooltip;
      var qlTooltip = document.querySelector('.ql-tooltip');
      var cbContainer = document.createElement('div');
      cbContainer.style.marginTop = '10px';
      cbContainer.style.display = 'block';
      
      cbContainer.innerHTML = '<label style="font-size:12px; color:#16a34a;"><input type="checkbox" id="ql-nofollow-cb"> Make this link Nofollow</label>';
      
      qlTooltip.appendChild(cbContainer);

      var originalSave = tooltip.save;
      tooltip.save = function() {
          var value = this.textbox.value;
          if (value) {
              var Link = Quill.import('formats/link');
              var sanitizedValue = Link.sanitize(value);
              var isNofollow = document.getElementById('ql-nofollow-cb').checked;
              window.quillNofollowLinks[sanitizedValue] = isNofollow;
              
              var range = this.quill.getSelection();
              if (range && range.length === 0) {
                  var leaf = this.quill.getLeaf(range.index);
                  var node = leaf ? leaf[0] : null;
                  while (node && node.statics && node.statics.blotName !== 'scroll') {
                      if (node.statics.blotName === 'link') {
                          this.quill.setSelection(this.quill.getIndex(node), node.length(), 'silent');
                          break;
                      }
                      node = node.parent;
                  }
              }
          }
          originalSave.call(this);
      };
      
      var originalEdit = tooltip.edit;
      tooltip.edit = function(mode, preview) {
          originalEdit.call(this, mode, preview);
          var isChecked = false;
          if (preview && window.quillNofollowLinks[preview] === true) {
              isChecked = true;
          }
          var cb = document.getElementById('ql-nofollow-cb');
          cb.checked = isChecked;
          cb.dataset.activeHref = preview || '';
      };

      quill.on('selection-change', function(range) {
          if (range) {
              var leaf = quill.getLeaf(range.index);
              var node = leaf ? leaf[0] : null;
              while (node && node.statics && node.statics.blotName !== 'scroll') {
                  if (node.statics.blotName === 'link') {
                      var href = node.domNode.getAttribute('href');
                      var cb = document.getElementById('ql-nofollow-cb');
                      if (cb && href) {
                          cb.dataset.activeHref = href;
                          cb.checked = window.quillNofollowLinks[href] === true;
                      }
                      break;
                  }
                  node = node.parent;
              }
          }
      });

      document.getElementById('ql-nofollow-cb').addEventListener('change', function() {
          var activeHref = this.dataset.activeHref;
          if (activeHref) {
              window.quillNofollowLinks[activeHref] = this.checked;
          }
      });

      // Intercept the form submission to apply the mapped rel attributes
      var form = document.getElementById('content-hidden').closest('form');
      if (form) {
          form.addEventListener('submit', function(e) {
              var html = quill.root.innerHTML;
              var tempDiv = document.createElement('div');
              tempDiv.innerHTML = html;
              var links = tempDiv.getElementsByTagName('a');
              for (var i = 0; i < links.length; i++) {
                  var href = links[i].getAttribute('href');
                  if (href && window.quillNofollowLinks.hasOwnProperty(href)) {
                      if (window.quillNofollowLinks[href]) {
                          links[i].setAttribute('rel', 'nofollow');
                      } else {
                          links[i].removeAttribute('rel');
                      }
                  }
              }
              document.getElementById('content-hidden').value = tempDiv.innerHTML;
          });
      }

      // Add Custom HTML button directly into Quill toolbar
      var toolbarEl = document.querySelector('.ql-toolbar');
      if (toolbarEl) {
          var customGroup = document.createElement('span');
          customGroup.className = 'ql-formats';
          customGroup.innerHTML = '<button type="button" onclick="openQuillHtmlModal()" title="Edit HTML Code (< />)" style="width:auto; padding: 0 8px; font-weight:bold; font-size:12px; color:#4f46e5;">&lt;/&gt; HTML</button>';
          toolbarEl.appendChild(customGroup);
      }
    </script>
    @include('components.quill-html-modal')
</x-app-layout>

