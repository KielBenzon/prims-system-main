<x-app-layout>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-6">Generate Report</h2>

        <!-- Filter Form -->
        <form action="{{ route('transactions.generate') }}" method="POST" 
              class="grid grid-cols-1 md:grid-cols-5 gap-4 bg-white shadow rounded-lg p-6 mb-6">
            @csrf

            <!-- Report Filters -->
                <h2 class="text-xl font-bold mb-4">Generate Report</h2>
                <form method="GET" action="{{ route('transactions.report') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-sm font-medium">Report Type</label>
                        <select name="report_type" class="border rounded p-2">
                            <option value="all" {{ request('report_type') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="payments" {{ request('report_type') == 'payments' ? 'selected' : '' }}>Payments</option>
                            <option value="donations" {{ request('report_type') == 'donations' ? 'selected' : '' }}>Donations</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                            class="border rounded p-2 bg-white text-black border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important; color-scheme: light;">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                            class="border rounded p-2 bg-white text-black border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important; color-scheme: light;">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Parishioner Name</label>
                        <input type="text" name="parishioner_name" value="{{ request('parishioner_name') }}" placeholder="Search name..." 
                            class="border rounded p-2 bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important;">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Generate</button>
                    </div>
                </form>

                <!-- Report Heading -->
        <h2 class="text-lg font-semibold mb-4">
            @if ($report_type === 'all')
                Showing All Transactions ({{ $total_count }} {{ Str::plural('record', $total_count) }})
                — Total ₱{{ number_format($total_amount, 2) }}
            @else
                Showing {{ ucfirst($report_type) }} Transactions ({{ $total_count }} {{ Str::plural('record', $total_count) }})
                — Total ₱{{ number_format($total_amount, 2) }}
            @endif

            @if (!empty($start_date) && !empty($end_date))
                from {{ $start_date }} to {{ $end_date }}
            @elseif (!empty($start_date))
                from {{ $start_date }} onwards
            @elseif (!empty($end_date))
                until {{ $end_date }}
            @endif
        </h2>
        <!-- Results Table -->
        @isset($transactions)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-sky-100 text-left">
                        <tr>
                            <th class="p-3 border">Date</th>
                            <th class="p-3 border">Full Name</th>
                            <th class="p-3 border">Amount</th>
                            <th class="p-3 border">Transaction Type</th>
                            <th class="p-3 border">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody>
    @forelse ($transactions as $t)
        <tr class="hover:bg-gray-50">
            <td class="border p-2">{{ $t->created_at ?? 'N/A' }}</td>
            <td class="border p-2">{{ $t->full_name ?? 'N/A' }}</td>
            <td class="border p-2">₱{{ number_format($t->amount, 2) }}</td>
            <td class="border p-2">{{ ucfirst($t->transaction_type ?? 'N/A') }}</td>
            <td>{{ $transaction->transaction_id ?? 'N/A' }}</td>

        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center p-3">No data available</td>
        </tr>
    @endforelse
</tbody>

                </table>
            </div>

            <!-- Actions -->
            <div class="mt-4 flex gap-3">
                <button onclick="window.print()" 
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg">Print Preview</button>
                <a href="{{ route('transactions.exportPdf') }}" 
                   class="bg-red-600 text-white px-4 py-2 rounded-lg">Export PDF</a>
                <a href="{{ route('transactions.exportExcel') }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-lg">Export Excel</a>
            </div>
        @endisset
    </div>
</x-app-layout>
