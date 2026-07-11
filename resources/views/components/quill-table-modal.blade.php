<!-- Quill Insert Table Builder Modal -->
<div id="quill-table-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-900 text-white">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <h3 class="font-bold text-base">Insert Table</h3>
            </div>
            <button type="button" onclick="closeQuillTableModal()" class="text-gray-300 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-xs text-gray-500">
                Specify the number of rows and columns for your table. You can edit the text inside any cell directly in the editor or HTML source after inserting.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Number of Rows</label>
                    <input type="number" id="table-rows-input" min="1" max="50" value="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Number of Columns</label>
                    <input type="number" id="table-cols-input" min="1" max="20" value="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                    <input type="checkbox" id="table-header-checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Include Header Row (Bold & Highlighted Top Row)
                </label>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
            <button type="button" onclick="closeQuillTableModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold text-sm rounded-lg transition">
                Cancel
            </button>
            <button type="button" onclick="insertTableIntoQuill()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Insert Table
            </button>
        </div>
    </div>
</div>

<script>
function openQuillTableModal() {
    document.getElementById('quill-table-modal').classList.remove('hidden');
}

function closeQuillTableModal() {
    document.getElementById('quill-table-modal').classList.add('hidden');
}

function insertTableIntoQuill() {
    var rows = parseInt(document.getElementById('table-rows-input').value, 10) || 3;
    var cols = parseInt(document.getElementById('table-cols-input').value, 10) || 3;
    var hasHeader = document.getElementById('table-header-checkbox').checked;

    var html = '<div class="overflow-x-auto my-6"><table class="w-full border-collapse border border-gray-300 text-sm">';
    
    if (hasHeader) {
        html += '<thead><tr class="bg-gray-100">';
        for (var c = 1; c <= cols; c++) {
            html += '<th class="border border-gray-300 px-4 py-2.5 font-bold text-left text-gray-800">Header ' + c + '</th>';
        }
        html += '</tr></thead>';
    }

    html += '<tbody>';
    var startRow = hasHeader ? 1 : 1;
    for (var r = 1; r <= rows; r++) {
        var trClass = (r % 2 === 0) ? 'bg-gray-50/50' : 'bg-white';
        html += '<tr class="' + trClass + '">';
        for (var c = 1; c <= cols; c++) {
            html += '<td class="border border-gray-300 px-4 py-2.5 text-gray-700">Cell ' + r + '-' + c + '</td>';
        }
        html += '</tr>';
    }
    html += '</tbody></table></div><p><br></p>';

    if (typeof quill !== 'undefined' && quill) {
        var range = quill.getSelection(true);
        if (range) {
            quill.clipboard.dangerouslyPasteHTML(range.index, html);
        } else {
            quill.root.innerHTML += html;
        }
        var hiddenContent = document.getElementById('content-hidden');
        if (hiddenContent) {
            hiddenContent.value = quill.root.innerHTML;
        }
    }
    closeQuillTableModal();
}
</script>
