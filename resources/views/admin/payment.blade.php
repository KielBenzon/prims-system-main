<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden">
                <div class="p-1 text-gray-900">
                    {{-- Success & Error Messages --}}
                    @if (session('success'))
                        <div class="toast" id="success">
                            <div class="alert alert-info bg-green-500 text-white">
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="toast" id="error">
                            <div class="alert alert-danger bg-red-500 text-white">
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Search --}}
                    <div class="mb-4">
                        <form action="{{ route('payment') }}" method="GET" class="flex items-center" id="searchForm">
                            <input type="text" name="search" placeholder="Search Payments..."
                                class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                                style="background-color: white !important; color: black !important;" />
                            <button type="submit"
                                class="ml-2 bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Full Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transaction Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transaction ID
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transactions as $transaction)
    @php $transaction = (object) $transaction; @endphp
    <tr class="cursor-pointer" onclick="viewModal{{ $loop->index }}.showModal()">
        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->full_name ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap">₱{{ number_format($transaction->amount ?? 0, 2) }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->date_time ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_type ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_id ?? 'N/A' }}</td>
    </tr>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $loop->index }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl p-6 bg-white">
                                            <!-- Modal Header -->
                                            <div class="flex items-center justify-between mb-6">
                                                <button class="btn text-black hover:bg-green-700 hover:text-white"
                                                    type="button" onclick="viewModal{{ $loop->index }}.close()">
                                                    <i class="bx bx-left-arrow-alt"></i>
                                                </button>
                                                <h3 class="text-lg font-semibold text-gray-800">View Transaction</h3>
                                            </div>

                                            <hr class="my-4">

                                            <!-- Transaction Details -->
                                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                                <div class="sm:col-span-1">
                                                    <label class="input input-bordered w-full flex items-center gap-2">
                                                        <span class="font-medium text-gray-700">Full Name</span>
                                                        <input type="text" name="full_name"
                                                            class="grow border-none focus:ring-0 focus:border-gray-300"
                                                            value="{{ $transaction->full_name }}" readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <label class="input input-bordered w-full flex items-center gap-2">
                                                        <span class="font-medium text-gray-700">Amount</span>
                                                        <input type="text" name="amount"
                                                            class="grow border-none focus:ring-0 focus:border-gray-300"
                                                            value="{{ $transaction->amount }}" readonly />
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2 mt-6">
                                                <div class="sm:col-span-1">
                                                    <label class="input input-bordered w-full flex items-center gap-2">
                                                        <span class="font-medium text-gray-700">Date & Time</span>
                                                        <input type="text" name="date_time"
                                                            class="grow border-none focus:ring-0 focus:border-gray-300"
                                                            value="{{ $transaction->date_time }}" readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <label class="input input-bordered w-full flex items-center gap-2">
                                                        <span class="font-medium text-gray-700">Transaction Type</span>
                                                        <input type="text" name="transaction_type"
                                                            class="grow border-none focus:ring-0 focus:border-gray-300"
                                                            value="{{ $transaction->transaction_type }}" readonly />
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Transaction ID and Image -->
                                            <div class="mt-6">
                                                <label class="input input-bordered w-full flex items-center gap-2">
                                                    <span class="font-medium text-gray-700">Transaction ID</span>
                                                    <input type="text" name="transaction_id"
                                                        class="grow border-none focus:ring-0 focus:border-gray-300"
                                                        value="{{ $transaction->transaction_id }}" readonly />
                                                </label>

                                                <!-- Image Display -->
                                                <div class="mt-4 text-center">
                                                    <img src="{{ str_starts_with($transaction->transaction_id, 'http') ? $transaction->transaction_id : asset('assets/payment/' . $transaction->transaction_id) }}" 
                                                        alt="Transaction Image" 
                                                        class="max-w-full max-h-64 object-contain rounded-xl shadow-lg mx-auto">
                                                </div>
                                            </div>

                                            <hr class="my-4">

                                            <!-- Close Button -->
                                            <div class="flex justify-end">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                    type="button" onclick="event.stopPropagation(); viewModal{{ $loop->index }}.close()">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </dialog>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
