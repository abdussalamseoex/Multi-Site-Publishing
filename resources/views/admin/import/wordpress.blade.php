<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WordPress Data Importer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Bulk Import from WordPress XML</h3>
                    <p class="text-sm text-gray-500 mb-6">Upload your WordPress Export file (.xml). We parse the file in batches to bypass server memory and timeout limits. This is safe for massive exports (up to 60,000+ posts).</p>

                    <!-- Alert Box -->
                    <div id="alertBox" class="hidden mb-6 rounded-md bg-blue-50 p-4 border border-blue-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                <p id="alertText" class="text-sm text-blue-700 font-medium">Message goes here</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form id="uploadForm" enctype="multipart/form-data">
                        
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Import Format</label>
                            <div class="flex space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="file_format" value="xml" class="form-radio text-indigo-600" checked onchange="updateFileAccept('.xml,.txt')">
                                    <span class="ml-2 text-sm text-gray-700">Default WP XML (WXR)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="file_format" value="csv" class="form-radio text-indigo-600" onchange="updateFileAccept('.csv')">
                                    <span class="ml-2 text-sm text-gray-700">CSV (WP Data Exporter)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="file_format" value="json" class="form-radio text-indigo-600" onchange="updateFileAccept('.json')">
                                    <span class="ml-2 text-sm text-gray-700">JSON (WP Data Exporter)</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                            <input type="file" name="upload_file" id="upload_file" accept=".xml,.txt" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md p-2">
                        </div>
                        
                        <button type="submit" id="startImportBtn" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Start Secure Import
                        </button>
                    </form>

                    <!-- Progress Section -->
                    <div id="progressSection" class="hidden mt-8 border-t border-gray-200 pt-6">
                        <h4 class="text-md font-bold text-gray-800 mb-2">Import Progress</h4>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                            <div id="progressBar" class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-xs font-semibold text-gray-500">
                            <span id="progressText">Analyzing...</span>
                            <span id="progressCount">0 / 0 Posts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        function updateFileAccept(acceptValues) {
            document.getElementById('upload_file').accept = acceptValues;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('uploadForm');
            const fileInput = document.getElementById('upload_file');
            const startBtn = document.getElementById('startImportBtn');
            const progressSection = document.getElementById('progressSection');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const progressCount = document.getElementById('progressCount');
            const alertBox = document.getElementById('alertBox');
            const alertText = document.getElementById('alertText');

            let totalItems = 0;
            let currentOffset = 0;
            let filePath = '';
            let fileType = '';
            let totalProcessed = 0;

            function showAlert(message, type = 'blue') {
                alertBox.classList.remove('hidden', 'bg-blue-50', 'bg-red-50', 'bg-green-50', 'border-blue-200', 'border-red-200', 'border-green-200');
                alertBox.classList.add(`bg-${type}-50`, `border-${type}-200`);
                alertText.className = `text-sm text-${type}-700 font-medium`;
                alertText.innerText = message;
            }

            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alertBox.classList.add('hidden');

                if (!fileInput.files.length) {
                    showAlert('Please select a file to import.', 'red');
                    return;
                }

                startBtn.disabled = true;
                startBtn.innerText = 'Uploading...';
                
                let formData = new FormData();
                formData.append('upload_file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                // Step 1: Upload and Analyze
                fetch('{{ route("admin.import.wordpress.upload") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    filePath = data.file_path;
                    fileType = data.file_type;
                    totalItems = data.total_items;
                    
                    if (totalItems === 0) {
                        throw new Error('No valid items found in the uploaded file.');
                    }
                    
                    progressSection.classList.remove('hidden');
                    progressText.innerText = 'File uploaded successfully. Preparing to insert data...';
                    progressCount.innerText = `0 / ${totalItems} Posts`;
                    
                    startBtn.innerText = 'Importing...';
                    
                    // Step 2: Begin Chunk Processing
                    processNextChunk();
                })
                .catch(error => {
                    showAlert(error.message || 'An unexpected error occurred during upload.', 'red');
                    startBtn.disabled = false;
                    startBtn.innerText = 'Start Secure Import';
                });
            });

            function processNextChunk() {
                let chunkData = new FormData();
                chunkData.append('file_path', filePath);
                chunkData.append('file_type', fileType);
                chunkData.append('offset', currentOffset);
                chunkData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route("admin.import.wordpress.process") }}', {
                    method: 'POST',
                    body: chunkData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    currentOffset = data.next_offset;
                    totalProcessed += data.processed; // Posts actually saved
                    
                    // Cap percentage at 100
                    let pct = Math.min(Math.round((currentOffset / totalItems) * 100), 100);
                    
                    progressBar.style.width = pct + '%';
                    progressText.innerText = `Inserting into database in batches...`;
                    progressCount.innerText = `${Math.min(currentOffset, totalItems)} / ${totalItems} Parsed`;

                    if (!data.is_finished) {
                        // Automatically trigger next chunk
                        setTimeout(processNextChunk, 800);
                    } else {
                        // Finished
                        startBtn.innerText = 'Import Complete!';
                        progressBar.classList.remove('bg-indigo-600');
                        progressBar.classList.add('bg-green-500');
                        progressText.innerText = `Successfully saved ${totalProcessed} posts.`;
                        progressCount.innerText = `${totalItems} / ${totalItems} Posts`;
                        showAlert(`Import successfully completed! Created ${totalProcessed} valid posts. Check your "All Posts" library.`, 'green');
                        fileInput.value = ''; // Reset file input
                    }
                })
                .catch(error => {
                    showAlert('Error while processing batch: ' + error.message, 'red');
                    startBtn.disabled = false;
                    startBtn.innerText = 'Resume Import';
                    
                    // In a real robust system, clicking resume would recall processNextChunk() 
                    // But changing the handler gets complex. This is good enough.
                    startBtn.onclick = function(e){
                         e.preventDefault();
                         startBtn.disabled = true;
                         startBtn.innerText = 'Resuming...';
                         processNextChunk();
                    };
                });
            }
        });
    </script>
</x-app-layout>
