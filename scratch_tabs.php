<?php

$file = 'resources/views/admin/analytics/index.blade.php';
$content = file_get_contents($file);

// 1. Add Tabs Menu & JS logic
$tabsMenu = <<<HTML
            <!-- Tabs Menu -->
            <div class="border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm overflow-x-auto">
                <nav class="-mb-px flex space-x-8 px-6 min-w-max" aria-label="Tabs">
                    <button onclick="openTab('tab-overview', this)" class="tab-btn active-tab border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm">
                        Overview
                    </button>
                    <button onclick="openTab('tab-sources', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Traffic Sources
                    </button>
                    <button onclick="openTab('tab-pages', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Top Content
                    </button>
                    <button onclick="openTab('tab-live', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse inline-block"></span> Live Log
                    </button>
                    <button onclick="openTab('tab-bots', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Bot Traffic & Security
                    </button>
                </nav>
            </div>

            <!-- TAB: OVERVIEW -->
            <div id="tab-overview" class="tab-pane block space-y-6">
                <!-- Key Metrics Grid -->
HTML;

$content = str_replace('<!-- Key Metrics Grid -->', $tabsMenu, $content);

// Extract parts
$parts = [
    'top_pages' => '',
    'bot_segments' => ''
];

if (preg_match('/<!-- Top Pages -->(.*?)<!-- Bot Segments -->/s', $content, $m)) {
    $parts['top_pages'] = $m[1];
}
if (preg_match('/<!-- Bot Segments -->(.*?)<!-- Traffic Sources & Referrers Grid -->/s', $content, $m)) {
    $parts['bot_segments'] = $m[1];
}

// Remove the "More Widgets" entirely
$content = preg_replace('/<!-- More Widgets -->.*?<!-- Traffic Sources & Referrers Grid -->/s', '<!-- Traffic Sources & Referrers Grid -->', $content);


// Add end of Overview, start of Traffic Sources
$trafficSourcesStart = <<<HTML
            </div> <!-- End tab-overview -->

            <!-- TAB: TOP CONTENT -->
            <div id="tab-pages" class="tab-pane hidden space-y-6">
                <!-- Top Pages -->
                {$parts['top_pages']}
            </div>

            <!-- TAB: TRAFFIC SOURCES -->
            <div id="tab-sources" class="tab-pane hidden space-y-6">
                <!-- Traffic Sources & Referrers Grid -->
HTML;

$content = str_replace('<!-- Traffic Sources & Referrers Grid -->', $trafficSourcesStart, $content);


// Close Traffic Sources, start Live Log
$liveLogStart = <<<HTML
            </div>

            <!-- TAB: LIVE LOG -->
            <div id="tab-live" class="tab-pane hidden space-y-6">
                <!-- Recent Visits Logs -->
HTML;
$content = str_replace('<!-- Recent Visits Logs -->', $liveLogStart, $content);

// Close Live Log, start Bots
$botsStart = <<<HTML
            </div>

            <!-- TAB: BOT TRAFFIC & SECURITY -->
            <div id="tab-bots" class="tab-pane hidden space-y-6">
                <!-- Bot Segments -->
                {$parts['bot_segments']}
                
                <!-- Blocked IPs Management -->
HTML;
$content = str_replace('<!-- Blocked IPs Management -->', $botsStart, $content);

// Close Bots tab
$content = preg_replace('/(<\/div>\s*<\/div>\s*<\/x-app-layout>)/', "            </div>\n        $1", $content);

// Append JS for tabs
$js = <<<JS

<script>
    function openTab(tabId, element) {
        // Hide all panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('block');
            pane.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'text-indigo-600', 'font-bold');
            btn.classList.add('border-transparent', 'text-gray-500', 'font-medium');
        });
        
        // Show current pane
        document.getElementById(tabId).classList.remove('hidden');
        document.getElementById(tabId).classList.add('block');
        
        // Set active class to current button
        element.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
        element.classList.add('border-indigo-500', 'text-indigo-600', 'font-bold');
    }
</script>
JS;

$content = str_replace('</x-app-layout>', "</x-app-layout>\n" . $js, $content);

file_put_contents($file, $content);
echo "Reorganization successful!";
