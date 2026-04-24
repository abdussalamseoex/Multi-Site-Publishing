<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Top-up Points') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <ul class="text-sm text-red-700 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top-up Form -->
                <div class="md:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Request Points</h3>
                        
                        <div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                            <p class="text-sm text-indigo-800">
                                <strong>Price per Point:</strong> ${{ number_format($basePrice, 2) }}<br>
                                <strong>Your Balance:</strong> <span class="font-bold text-lg">{{ auth()->user()->points }}</span> Pts
                            </p>
                        </div>

                        <form action="{{ route('user.topup.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Points to Top-up</label>
                                    <input type="number" name="requested_points" id="requested_points" min="1" value="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" oninput="calculateTotal()">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Payable Amount ($)</label>
                                    <input type="text" id="total_amount" value="{{ number_format($basePrice, 2) }}" class="mt-1 block w-full border-gray-300 bg-gray-50 rounded-md shadow-sm sm:text-sm text-gray-500 font-bold" readonly>
                                </div>
                                <div class="pt-2 border-t border-gray-200">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID / Payment Proof</label>
                                    <input type="text" name="transaction_id" placeholder="e.g. PayPal TXN-12345" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    <p class="text-xs text-gray-500 mt-1">Please pay the total amount and enter the transaction ID here for verification.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                                </div>
                                
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- History -->
                <div class="md:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Your Top-up History</h3>
                        </div>
                        
                        @if($requests->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($requests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $request->requested_points }} Pts
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono text-xs">
                                                {{ $request->transaction_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($request->status === 'approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                                @elseif($request->status === 'rejected')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-6 text-center text-gray-500">
                                You have not made any top-up requests yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const basePrice = {{ $basePrice }};
        function calculateTotal() {
            let points = document.getElementById('requested_points').value;
            if (points < 1) points = 1;
            let total = points * basePrice;
            document.getElementById('total_amount').value = total.toFixed(2);
        }
    </script>
</x-app-layout>
