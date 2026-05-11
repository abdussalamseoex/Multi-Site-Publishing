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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Editor</label>
                                <!-- Quill Editor -->
                                <div id="quill-editor" style="height: 400px; background: white;">{!! old('content', $post->content) !!}</div>
                                <input type="hidden" name="content" id="content-hidden">
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
                                <button type="submit" onclick="document.getElementById('content-hidden').value = quill.root.innerHTML" class="px-6 py-3 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    Save Changes & Submit for Review
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
      
      @if(isset($userHasDofollowPermission) && !$userHasDofollowPermission)
          cbContainer.innerHTML = '<label style="font-size:12px; color:#ef4444;"><input type="checkbox" id="ql-nofollow-cb" checked disabled> Nofollow (No Permission)</label>';
      @else
          cbContainer.innerHTML = '<label style="font-size:12px; color:#16a34a;"><input type="checkbox" id="ql-nofollow-cb"> Make this link Nofollow</label>';
      @endif
      
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
          @if(isset($userHasDofollowPermission) && !$userHasDofollowPermission)
              cb.checked = true;
          @else
              cb.checked = isChecked;
          @endif
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
                          @if(isset($userHasDofollowPermission) && !$userHasDofollowPermission)
                              cb.checked = true;
                          @else
                              cb.checked = window.quillNofollowLinks[href] === true;
                          @endif
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
    </script>
</x-app-layout>
