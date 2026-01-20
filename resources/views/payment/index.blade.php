<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Payments</h2>

                <table class="w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-2 border">Parishioner</th>
                            <th class="p-2 border">Amount</th>
                            <th class="p-2 border">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="p-2 border">{{ $payment->user->name ?? 'Unknown' }}</td>
                                <td class="p-2 border">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="p-2 border">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
