<!-- Quill Custom HTML / Source Code Modal -->
<div id="quill-html-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full overflow-hidden border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900 text-white">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                <h3 class="font-bold text-base">Custom HTML Code / Source Editor</h3>
            </div>
            <button type="button" onclick="closeQuillHtmlModal()" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-gray-500 mb-3">
                You can write or paste any raw HTML code here (such as embed codes, custom tables, banners, iframes, or formatted lists). Clicking "Apply HTML Code" will update the editor instantly.
            </p>
            <textarea id="quill-html-source-textarea" rows="16" class="w-full font-mono text-xs bg-gray-900 text-green-400 p-4 rounded-lg border border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none leading-relaxed"></textarea>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
            <button type="button" onclick="closeQuillHtmlModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold text-sm rounded-lg transition">
                Cancel
            </button>
            <button type="button" onclick="applyQuillHtmlSource()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Apply HTML Code
            </button>
        </div>
    </div>
</div>

<script>
function openQuillHtmlModal() {
    if (typeof quill !== 'undefined' && quill) {
        document.getElementById('quill-html-source-textarea').value = quill.root.innerHTML;
    }
    document.getElementById('quill-html-modal').classList.remove('hidden');
}

function closeQuillHtmlModal() {
    document.getElementById('quill-html-modal').classList.add('hidden');
}

function applyQuillHtmlSource() {
    var rawHtml = document.getElementById('quill-html-source-textarea').value;
    if (typeof quill !== 'undefined' && quill) {
        quill.root.innerHTML = rawHtml;
        var hiddenContent = document.getElementById('content-hidden');
        if (hiddenContent) {
            hiddenContent.value = rawHtml;
        }
    }
    closeQuillHtmlModal();
}
</script>
