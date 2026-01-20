<x-app-layout>
    <div class="p-6 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Transactions</h1>

        <!-- Date Range Filter -->
        <form method="GET" action="{{ route('transactions.index') }}" class="mb-4 flex gap-2">
            <input type="date" name="start_date" value="{{ $start_date ?? '' }}" class="border rounded p-2">
            <input type="date" name="end_date" value="{{ $end_date ?? '' }}" class="border rounded p-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
        </form>

        <!-- Generate Report -->
        <a href="{{ route('transactions.report', ['start_date' => $start_date, 'end_date' => $end_date]) }}"
           class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block"
           target="_blank">
            Generate Report
        </a>

        <div class="mb-4">
    <h2 class="text-xl font-bold">
        Showing {{ ucfirst($report_type) }} Transactions 
        ({{ $total_count }} records)
    </h2>
    <p class="text-gray-600">
        Total Amount: ₱{{ number_format($total_amount, 2) }}
    </p>
</div>

        
        

        <!-- Transactions Table -->
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Full Name</th>
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Amount</th>
                    <th class="p-2 border">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td class="border p-2">{{ $tx->transaction_id }}</td>
<td class="border p-2">{{ $tx->full_name ?? 'N/A' }}</td>
<td class="border p-2">₱{{ number_format($tx->amount, 2) }}</td>
<td class="border p-2">{{ $tx->created_at ?? 'N/A' }}</td>
<td class="border p-2">{{ ucfirst($tx->transaction_type ?? 'N/A') }}</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
