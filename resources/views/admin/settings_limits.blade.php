<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Limits & Pricing Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    
                    <!-- Guest Post & User Settings -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">User & Post Controls</h3>
                        <div class="space-y-4">
                            <div class="flex items-center mb-2 bg-gray-50 p-2 rounded border">
                                <input type="hidden" name="enable_checkout_flow" value="0">
                                <input type="checkbox" name="enable_checkout_flow" value="1" {{ ($settings['enable_checkout_flow'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 font-bold">Enable Checkout/Payment Flow (Legacy)</span>
                            </div>

                            <div class="flex items-center mb-2 bg-indigo-50 p-2 rounded border border-indigo-100">
                                <input type="hidden" name="enable_user_post_editing" value="0">
                                <input type="checkbox" name="enable_user_post_editing" value="1" {{ ($settings['enable_user_post_editing'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-indigo-400 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-indigo-900 font-bold">Allow Users to Edit Their Posts (Sends to Pending)</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Daily Limit</label>
                                    <input type="number" name="default_daily_post_limit" value="{{ $settings['default_daily_post_limit'] ?? '1' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Total Limit</label>
                                    <input type="number" name="default_total_post_limit" value="{{ $settings['default_total_post_limit'] ?? '10' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Post Link Type</label>
                                <select name="default_dofollow_status" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    <option value="0" {{ ($settings['default_dofollow_status'] ?? '0') == '0' ? 'selected' : '' }}>NoFollow</option>
                                    <option value="1" {{ ($settings['default_dofollow_status'] ?? '0') == '1' ? 'selected' : '' }}>DoFollow</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Add-ons -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Promotions</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Base Price (Per Post/Point $)</label>
                                <input type="number" step="0.01" name="guest_post_base_price" value="{{ $settings['guest_post_base_price'] ?? '50.00' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 text-xs">Do-Follow Add-on ($)</label>
                                    <input type="number" step="0.01" name="addon_dofollow_price" value="{{ $settings['addon_dofollow_price'] ?? '20.00' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 text-xs">Fast Approval Add-on ($)</label>
                                    <input type="number" step="0.01" name="addon_fast_approval_price" value="{{ $settings['addon_fast_approval_price'] ?? '10.00' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex items-center mb-2 bg-green-50 p-2 rounded border border-green-100">
                                <input type="hidden" name="enable_promotional_free_posts" value="0">
                                <input type="checkbox" name="enable_promotional_free_posts" value="1" {{ ($settings['enable_promotional_free_posts'] ?? '0') == '1' ? 'checked' : '' }} class="rounded border-green-400 text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-sm text-green-900 font-bold">Enable Free Promo (Bypass Point Deduction)</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 text-xs">Free Promo Posts Per User Today</label>
                                <input type="number" name="promotional_free_post_limit" value="{{ $settings['promotional_free_post_limit'] ?? '1' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment & Bank Instructions for Top-ups</h3>
                        <p class="text-sm text-gray-500 mb-4">Enter the payment details (Bank Transfer, PayPal, Stripe Link) that users will see when requesting a point top-up. HTML is allowed.</p>
                        <textarea name="payment_instructions" rows="5" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="<p>Please send money to Bkash: 017XXXXXX</p>">{{ $settings['payment_instructions'] ?? '<p>Please pay the total amount via Bank Transfer to Account #123456789 (XYZ Bank). Once paid, enter your transaction ID above.</p>' }}</textarea>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-medium shadow hover:bg-indigo-700">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
