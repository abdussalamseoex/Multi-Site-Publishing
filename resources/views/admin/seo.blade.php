<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('SEO Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.seo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Robots.txt Box -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Robots.txt Editor</h3>
                            <p class="text-xs text-gray-500 mt-1">Manage search engine crawler access (Live URL: <a href="{{ url('/robots.txt') }}" target="_blank" class="text-indigo-600 hover:underline">{{ url('/robots.txt') }}</a>)</p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-robot text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="p-6 text-gray-900 space-y-6">
                        @php
                            $defaultRobots = "User-agent: *\nDisallow: /admin/\nDisallow: /checkout/\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
                        @endphp
                        <div>
                            <textarea name="custom_robots_txt" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm bg-gray-50" placeholder="{{ $defaultRobots }}">{{ $settings['custom_robots_txt'] ?? $defaultRobots }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">Warning: Misconfiguring this file can completely de-index your website from Google. Only edit if you know what you are doing.</p>
                        </div>
                    </div>
                </div>

                <!-- Sitemap XML Injection Box -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Advanced Sitemap Injection</h3>
                            <p class="text-xs text-gray-500 mt-1">Inject custom raw XML nodes into your Sitemap (Live URL: <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-indigo-600 hover:underline">{{ url('/sitemap.xml') }}</a>)</p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-sitemap text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="p-6 text-gray-900 space-y-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-3">
                                Your platform already automatically generates a dynamic sitemap containing all your published <strong>Posts</strong> and the <strong>Homepage</strong>. 
                                However, if you explicitly want to hardcode external pages or additional assets, you can inject RAW XML <code>&lt;url&gt;</code> tags here.
                            </p>
                            <textarea name="custom_sitemap_xml" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm bg-gray-50" placeholder="<url>&#10;  <loc>https://yourdomain.com/custom-page</loc>&#10;  <changefreq>monthly</changefreq>&#10;</url>">{{ $settings['custom_sitemap_xml'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Automated URL & Redirect Rules -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Global Redirects (404 Fallback)</h3>
                            <p class="text-xs text-gray-500 mt-1">Manage what happens when Google or users hit old/deleted URLs (404 Not Found)</p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-random text-2xl"></i>
                        </div>
                    </div>
                    <div class="p-6 text-gray-900 space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-bold text-gray-700">Redirect 404 Pages to Homepage (301)</label>
                                <p class="text-xs text-gray-500 mt-1">Instead of showing a "Page Not Found" error, silently redirect old WordPress/dropped URLs to your homepage. Great for preserving SEO crawl budget.</p>
                            </div>
                            <div>
                                <select name="redirect_404_to_home" class="mt-1 block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                                    <option value="1" {{ (isset($settings['redirect_404_to_home']) && $settings['redirect_404_to_home'] == '1') || !isset($settings['redirect_404_to_home']) ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ (isset($settings['redirect_404_to_home']) && $settings['redirect_404_to_home'] == '0') ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OG Image Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Default Open Graph (OG) Image</h3>
                            <p class="text-xs text-gray-500 mt-1">Configure the default image shown when your website is shared on Facebook, Twitter, LinkedIn, etc.</p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="p-6 text-gray-900 space-y-6">
                        <div class="flex flex-col md:flex-row items-center gap-6 pb-6 border-b border-gray-100">
                            <div class="w-full md:w-1/2">
                                <label class="text-sm font-bold text-gray-700">Upload Image File</label>
                                <p class="text-xs text-gray-500 mt-1">Upload an image directly from your computer.</p>
                                <input type="file" name="default_og_image_file" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="text-sm font-bold text-gray-700">OR Image URL</label>
                                <p class="text-xs text-gray-500 mt-1">Provide a direct URL to the image (e.g. https://.../image.jpg).</p>
                                <input type="text" name="default_og_image_url" value="{{ $settings['default_og_image'] ?? '' }}" class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-md">
                            <p class="text-sm text-indigo-700"><strong>Note on Priority:</strong> The system will first try to use the specific Article's Featured Image. If the page is not an article or has no featured image, it will use this Default OG Image. If none is set, it falls back to the Main Site Logo.</p>
                        </div>
                        @if(isset($settings['default_og_image']) && $settings['default_og_image'])
                        <div class="mt-4">
                            <label class="text-sm font-bold text-gray-700">Current Default OG Image:</label>
                            <div class="mt-2">
                                <img src="{{ $settings['default_og_image'] }}" alt="OG Image" class="h-32 object-contain border rounded shadow-sm">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Automated URL & Redirect Rules -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">URL Architecture & Routing</h3>
                            <p class="text-xs text-gray-500 mt-1">Configure global slug structuring and automated redirections for legacy imports</p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <i class="fas fa-link text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="p-6 text-gray-900 space-y-6">
                        <div class="flex items-center justify-between pb-6 border-b border-gray-100">
                            <div>
                                <label class="text-sm font-bold text-gray-700">Auto WebP Image Conversion</label>
                                <p class="text-xs text-gray-500 mt-1">If enabled, all uploaded images (posts, logos, etc.) will be automatically converted to <strong>WebP</strong> format to improve page speed and SEO.</p>
                            </div>
                            <div>
                                <select name="enable_auto_webp" class="mt-1 block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                                    <option value="off" {{ (isset($settings['enable_auto_webp']) && $settings['enable_auto_webp'] === 'off') || !isset($settings['enable_auto_webp']) ? 'selected' : '' }}>Disabled</option>
                                    <option value="on" {{ (isset($settings['enable_auto_webp']) && $settings['enable_auto_webp'] === 'on') ? 'selected' : '' }}>Enabled</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-bold text-gray-700">Append Unique Code to Post Slugs</label>
                                <p class="text-xs text-gray-500 mt-1">If enabled, a unique identifier (e.g. <code>-64ab21fc</code>) will be appended to newly generated post URLs to guarantee uniqueness over time.</p>
                            </div>
                            <div>
                                <select name="seo_post_slug_code" class="mt-1 block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                                    <option value="off" {{ (isset($settings['seo_post_slug_code']) && $settings['seo_post_slug_code'] === 'off') || !isset($settings['seo_post_slug_code']) ? 'selected' : '' }}>Disabled</option>
                                    <option value="on" {{ (isset($settings['seo_post_slug_code']) && $settings['seo_post_slug_code'] === 'on') ? 'selected' : '' }}>Enabled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mb-12">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-md shadow uppercase font-bold tracking-wider hover:bg-indigo-700 transition transform hover:-translate-y-0.5">Save SEO Configurations</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
