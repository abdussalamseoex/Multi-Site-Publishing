<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Guest Post Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 flex flex-col md:flex-row gap-8">
                    
                    <!-- Order Details -->
                    <div class="w-full md:w-2/3">
                        <h3 class="text-lg font-bold mb-4">Select Add-ons</h3>
                        <p class="text-sm text-gray-600 mb-6">Your article "<strong>{{ $post->title }}</strong>" has been saved. Please select any optional add-ons before submitting it for review.</p>
                        
                        <form action="{{ route('orders.process', $post->id) }}" method="POST" id="checkout-form">
                            @csrf
                            
                            <div class="space-y-4 mb-8">
                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="checkbox" name="addon_dofollow" id="cb_dofollow" value="1" class="sr-only" onchange="calculateTotal()">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span id="project-type-0-label" class="block text-sm font-medium text-gray-900">Do-Follow Index</span>
                                            <span id="project-type-0-description-0" class="mt-1 flex items-center text-sm text-gray-500">Ensure all your outgoing links are indexed as do-follow.</span>
                                        </span>
                                    </span>
                                    <span class="ml-4 flex items-center text-indigo-600 font-bold">${{ number_format($dofollowPrice, 2) }}</span>
                                    <!-- Checked Icon -->
                                    <svg class="h-5 w-5 text-indigo-600 absolute right-4 top-4 hidden check-icon" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="checkbox" name="addon_fast_approval" id="cb_fast" value="1" class="sr-only" onchange="calculateTotal()">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span id="project-type-1-label" class="block text-sm font-medium text-gray-900">Fast Approval</span>
                                            <span id="project-type-1-description-0" class="mt-1 flex items-center text-sm text-gray-500">Skip the queue. Guaranteed review within 24 hours.</span>
                                        </span>
                                    </span>
                                    <span class="ml-4 flex items-center text-indigo-600 font-bold">${{ number_format($fastApprovalPrice, 2) }}</span>
                                </label>
                            </div>

                            <div class="border-t pt-6">
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-md shadow-sm transition">
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Summary Sidebar -->
                    <div class="w-full md:w-1/3 bg-gray-50 p-6 rounded-lg border">
                        <h3 class="text-lg font-bold mb-4">Order Summary</h3>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Base Post</span>
                            <span class="font-medium">${{ number_format($basePrice, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between mb-2 hidden" id="row_dofollow">
                            <span class="text-gray-600">Do-Follow Add-on</span>
                            <span class="font-medium">+${{ number_format($dofollowPrice, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between mb-4 hidden" id="row_fast">
                            <span class="text-gray-600">Fast Approval</span>
                            <span class="font-medium">+${{ number_format($fastApprovalPrice, 2) }}</span>
                        </div>
                        
                        <div class="border-t pt-4 mt-2 flex justify-between">
                            <span class="font-bold text-lg">Total</span>
                            <span class="font-bold text-indigo-600 text-lg" id="total_price">${{ number_format($basePrice, 2) }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const basePrice = {{ $basePrice }};
        const doFollowPrice = {{ $dofollowPrice }};
        const fastApprovalPrice = {{ $fastApprovalPrice }};

        function calculateTotal() {
            let total = basePrice;
            
            const isDoFollow = document.getElementById('cb_dofollow').checked;
            const isFast = document.getElementById('cb_fast').checked;

            if(isDoFollow) {
                total += doFollowPrice;
                document.getElementById('row_dofollow').classList.remove('hidden');
                document.getElementById('cb_dofollow').parentElement.classList.add('border-indigo-500', 'ring-1', 'ring-indigo-500');
            } else {
                document.getElementById('row_dofollow').classList.add('hidden');
                document.getElementById('cb_dofollow').parentElement.classList.remove('border-indigo-500', 'ring-1', 'ring-indigo-500');
            }

            if(isFast) {
                total += fastApprovalPrice;
                document.getElementById('row_fast').classList.remove('hidden');
                document.getElementById('cb_fast').parentElement.classList.add('border-indigo-500', 'ring-1', 'ring-indigo-500');
            } else {
                document.getElementById('row_fast').classList.add('hidden');
                document.getElementById('cb_fast').parentElement.classList.remove('border-indigo-500', 'ring-1', 'ring-indigo-500');
            }

            document.getElementById('total_price').innerText = '$' + total.toFixed(2);
        }
    </script>
</x-app-layout>
