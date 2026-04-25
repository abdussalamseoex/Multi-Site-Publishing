<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Settings & Add-ons') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    
                    <!-- General Settings -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">General Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="site_title" class="block text-sm font-medium text-gray-700">Site Title</label>
                                <input type="text" name="site_title" id="site_title" value="{{ $settings['site_title'] ?? env('APP_NAME') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>

                            <div>
                                <label for="site_tagline" class="block text-sm font-medium text-gray-700">Tagline</label>
                                <input type="text" name="site_tagline" id="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Logo</label>
                                    @if(isset($settings['site_logo']))
                                        <img src="{{ url($settings['site_logo']) }}" class="h-8 mt-2 mb-2 border rounded">
                                    @endif
                                    <input type="file" name="site_logo" accept="image/*" class="mt-1 block w-full text-xs">
                                </div>
                                <div class="flex flex-col justify-end pb-1 pb-1">
                                    <label class="block text-sm font-medium text-gray-700">Logo Height (px)</label>
                                    <input type="number" name="logo_height" value="{{ $settings['logo_height'] ?? '40' }}" placeholder="40" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm h-9">
                                    <span class="text-[10px] text-gray-500 mt-1">Adjust if logo is too small.</span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Favicon</label>
                                    @if(isset($settings['site_favicon']))
                                        <img src="{{ url($settings['site_favicon']) }}" class="h-8 mt-2 mb-2 border rounded">
                                    @endif
                                    <input type="file" name="site_favicon" accept="image/*" class="mt-1 block w-full text-xs">
                                </div>
                            </div>
                            
                            <div class="mt-2 border-t pt-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Default Post Publishing Rule</label>
                                <select name="default_post_status" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    <option value="pending" {{ ($settings['default_post_status'] ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending Review (Drafts)</option>
                                    <option value="published" {{ ($settings['default_post_status'] ?? '') == 'published' ? 'selected' : '' }}>Auto-Publish (Live immediately)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Controls what happens when regular users/authors submit a post.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer & Social Links -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Footer & Social Links</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Footer Copyright Text</label>
                                    <input type="text" name="footer_copyright_text" value="{{ $settings['footer_copyright_text'] ?? '&copy; ' . date('Y') . ' ' . env('APP_NAME') . '. All rights reserved.' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Facebook URL</label>
                                    <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Twitter (X) URL</label>
                                    <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://twitter.com/..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Instagram URL</label>
                                    <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">YouTube URL</label>
                                    <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance Options -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Appearance</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Active Theme</label>
                                <select name="active_theme" class="mt-1 block w-full border-gray-300 rounded-md sm:text-sm">
                                    <option value="minimal" {{ ($settings['active_theme'] ?? '') == 'minimal' ? 'selected' : '' }}>Minimal SEO</option>
                                    <option value="good" {{ ($settings['active_theme'] ?? '') == 'good' ? 'selected' : '' }}>GOOD (Dynamic Builder)</option>
                                    <option value="blog" {{ ($settings['active_theme'] ?? '') == 'blog' ? 'selected' : '' }}>Standard Blog</option>
                                    <option value="news" {{ ($settings['active_theme'] ?? '') == 'news' ? 'selected' : '' }}>News Layout</option>
                                    <option value="magazine" {{ ($settings['active_theme'] ?? '') == 'magazine' ? 'selected' : '' }}>Magazine Layout</option>
                                    <option value="vanguard" {{ ($settings['active_theme'] ?? '') == 'vanguard' ? 'selected' : '' }}>Vanguard Elite (Gaming/Crypto)</option>
                                    <option value="nexus" {{ ($settings['active_theme'] ?? '') == 'nexus' ? 'selected' : '' }}>Nexus (Tech/SaaS)</option>
                                    <option value="ledger" {{ ($settings['active_theme'] ?? '') == 'ledger' ? 'selected' : '' }}>Ledger (Finance/Business)</option>
                                    <option value="vitality" {{ ($settings['active_theme'] ?? '') == 'vitality' ? 'selected' : '' }}>Vitality (Health/CBD)</option>
                                    <option value="estate" {{ ($settings['active_theme'] ?? '') == 'estate' ? 'selected' : '' }}>Estate (Real Estate)</option>
                                    <option value="voyage" {{ ($settings['active_theme'] ?? '') == 'voyage' ? 'selected' : '' }}>Voyage (Travel/Lifestyle)</option>
                                    <option value="lens" {{ ($settings['active_theme'] ?? '') == 'lens' ? 'selected' : '' }}>Lens (Photography Portfolio)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                                    <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#4f46e5' }}" class="h-10 w-full cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Header Background</label>
                                    <input type="color" name="header_bg_color" value="{{ $settings['header_bg_color'] ?? '#ffffff' }}" class="h-10 w-full cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Header Text Color</label>
                                    <input type="color" name="header_text_color" value="{{ $settings['header_text_color'] ?? '#1f2937' }}" class="h-10 w-full cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Typography</label>
                                    <select name="typography" class="block w-full border-gray-300 rounded-md sm:text-sm h-10">
                                        <option value="Inter" {{ ($settings['typography'] ?? '') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Playfair Display" {{ ($settings['typography'] ?? '') == 'Playfair Display' ? 'selected' : '' }}>Playfair Display</option>
                                        <option value="Roboto" {{ ($settings['typography'] ?? '') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Homepage Builder (Sections)</label>
                                <div class="flex items-center mb-2">
                                    <input type="hidden" name="show_featured_section" value="0">
                                    <input type="checkbox" name="show_featured_section" value="1" {{ ($settings['show_featured_section'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Show Featured Posts Section (Hero)</span>
                                </div>
                                <div class="flex items-center">
                                    <input type="hidden" name="show_latest_section" value="0">
                                    <input type="checkbox" name="show_latest_section" value="1" {{ ($settings['show_latest_section'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Show Latest Posts Section</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System & Integrations -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Integrations & System</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                                <select name="timezone" class="mt-1 block w-full border-gray-300 rounded-md sm:text-sm">
                                    <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="Asia/Dhaka" {{ ($settings['timezone'] ?? '') == 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka</option>
                                    <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">SMTP Host (Email)</label>
                                <input type="text" name="smtp_host" placeholder="smtp.mailtrap.io" value="{{ $settings['smtp_host'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                <input type="text" name="smtp_user" value="{{ $settings['smtp_user'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <h4 class="text-sm font-bold text-gray-900 mb-3">Registration & Auth</h4>
                                
                                <div class="flex items-center mb-4">
                                    <input type="hidden" name="enable_registration" value="0">
                                    <input type="checkbox" name="enable_registration" value="1" {{ ($settings['enable_registration'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Enable User Registration (Allow public sign-ups)</span>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Role on Registration</label>
                                    <select name="default_user_role" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                        <option value="user" {{ ($settings['default_user_role'] ?? 'user') == 'user' ? 'selected' : '' }}>Normal User</option>
                                        <option value="author" {{ ($settings['default_user_role'] ?? '') == 'author' ? 'selected' : '' }}>Author (Can submit posts)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Footer Customization -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 col-span-1 border-l-4 border-yellow-400">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
                            <h3 class="text-lg font-medium text-gray-900">Footer Customization</h3>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Customize the copyright text and credits shown at the very bottom of the website.</p>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Copyright Text</label>
                                <input type="text" name="footer_copyright_text" value="{{ $settings['footer_copyright_text'] ?? '&copy; ' . date('Y') . ' ' . ($settings['site_title'] ?? env('APP_NAME')) . '. All rights reserved.' }}" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Name</label>
                                <input type="text" name="footer_credit_text" value="{{ $settings['footer_credit_text'] ?? 'Abdus Salam SEO Expert' }}" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Credit URL (Optional)</label>
                                <input type="url" name="footer_credit_url" value="{{ $settings['footer_credit_url'] ?? '#' }}" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Custom Scripts & Analytics -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 col-span-1 border-l-4 border-purple-400">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            <h3 class="text-lg font-medium text-gray-900">Custom Scripts & Analytics</h3>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Add Google Analytics, Facebook Pixel, or any custom Javascript/CSS. These will be injected globally across the entire frontend.</p>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Header Scripts (Inside <head>)</label>
                                <textarea name="custom_header_scripts" rows="6" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs text-purple-800" placeholder="<!-- Google Analytics -->&#10;<script async src='https://www.googletagmanager.com/gtag/js?id=G-XXXX'></script>">{{ $settings['custom_header_scripts'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Footer Scripts (Before </body>)</label>
                                <textarea name="custom_footer_scripts" rows="6" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs text-purple-800" placeholder="<script>...</script>">{{ $settings['custom_footer_scripts'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                </div> <!-- End grid -->

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-medium shadow hover:bg-indigo-700">
                        Save Configurations
                    </button>
                </div>
            </form>

            <!-- Demo Content Importer & System Updates -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-400">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <h3 class="text-lg font-medium text-gray-900">System Utilities</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-2">Database & System Updates</h4>
                        <p class="text-xs text-gray-500 mb-4">Run this immediately after downloading new code updates from GitHub to update the database schema automatically.</p>
                        
                        <form action="{{ route('admin.system.migrate') }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Run database migrations? This will apply all pending structure changes.')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none transition">
                                Run System Updates (Migrate)
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-2">Demo Content Importer</h4>
                        <p class="text-xs text-gray-500 mb-4">Generate 30 fully formatted demo articles with random images, categories, and typography styling instantly.</p>
                        
                        <a href="{{ route('admin.demo.import') }}" onclick="return confirm('Are you sure you want to generate 30 demo posts? This may take a few seconds.')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none transition">
                            Import Demo Content
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

