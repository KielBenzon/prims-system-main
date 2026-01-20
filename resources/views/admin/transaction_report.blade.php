<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden p-4 rounded-lg shadow-lg">

                

                <!-- Report Table -->
                <div id="reportArea" class="mt-6">
                    <!-- Parish Header -->
                    <div class="print-header text-center mb-6">
                        <!-- Logo -->
    <img src="{{ asset('assets/img/logo.png') }}" alt="Parish Logo" class="mx-auto mb-2 w-20 h-20 object-contain">

                        <h1 class="text-2xl font-bold text-yellow-800">St. Michael the Archangel Parish Church</h1>
                        <h2 class="text-md text-gray-700">Basey, Samar</h2>
                        <p style="margin-top: 5px; font-size: 12px; color: #444;">
                            Report Generated: {{ now()->format('F d, Y h:i A') }}
                        </p>
                        <hr class="my-2 border-yellow-700">
                    </div>

                    @if(isset($transactions) && count($transactions) > 0)
                        <!-- Totals -->
                        <div class="mb-3">
                            <strong>Total Transactions:</strong> {{ $total_transactions ?? 0 }} <br>
                            <strong>Total Amount:</strong> ₱{{ number_format($total_amount ?? 0, 2) }}
                        </div>

                        <!-- Transactions Table -->
                        <table class="w-full border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Fullname</th>
                                    <th class="border p-2">Amount</th>
                                    <th class="border p-2">Date and Time</th>
                                    <th class="border p-2">Transaction Type</th>
                                    <th class="border p-2">Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transactions as $transaction)
                                    @php $transaction = (object) $transaction; @endphp
                                    <tr class="cursor-pointer hover:bg-gray-100"
                                        onclick="showTransactionModal(
                                            '{{ $transaction->full_name }}',
                                            '{{ $transaction->amount }}',
                                            '{{ \Carbon\Carbon::parse($transaction->date ?? now())->format("F d, Y h:i A") }}',
                                            '{{ $transaction->transaction_type }}',
                                            '{{ $transaction->transaction_id ?? "" }}'
                                        )">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->full_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">₱{{ number_format($transaction->amount ?? 0, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($transaction->date ?? now())->format('F d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center mt-4">No transactions found for the selected filters.</p>
                    @endif
                </div>

                <!-- Export Buttons -->
                <div class="mt-4 flex gap-2 no-print">
                    <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Print / Save as PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div id="transactionModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
            <h2 class="text-xl font-bold mb-4">Transaction Details</h2>
            <p><strong>Fullname:</strong> <span id="modalFullname"></span></p>
            <p><strong>Amount:</strong> ₱<span id="modalAmount"></span></p>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>
            <p><strong>Type:</strong> <span id="modalType"></span></p>

            <div class="mt-4">
                <strong>Transaction Proof:</strong>
                <div id="modalProofContainer" class="mt-2">
                    <img id="modalProof"
                         src=""
                         alt="Transaction Image"
                         class="w-full h-48 object-cover border rounded shadow cursor-pointer hidden"
                         onclick="openImageInNewTab(this.src)">
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-red-700 hover:text-white"
                    type="button" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script>
        function showTransactionModal(fullname, amount, date, type, proofFilename) {
            document.getElementById("modalFullname").textContent = fullname;
            document.getElementById("modalAmount").textContent = parseFloat(amount).toFixed(2);
            document.getElementById("modalDate").textContent = date;
            document.getElementById("modalType").textContent = type;

            let proofImg = document.getElementById("modalProof");

            if (proofFilename && proofFilename !== "null") {
                let basePath = "";

                if (type === "Donation") {
                    basePath = "{{ asset('assets/transactions') }}";
                } else if (type === "Payment") {
                    basePath = "{{ asset('assets/payment') }}";
                } else {
                    basePath = "{{ asset('assets/others') }}";
                }

                proofImg.src = basePath + "/" + proofFilename;
                proofImg.classList.remove("hidden");
            } else {
                proofImg.classList.add("hidden");
            }

            document.getElementById("transactionModal").classList.remove("hidden");
        }

        function closeModal() {
            document.getElementById("transactionModal").classList.add("hidden");
        }

        function openImageInNewTab(src) {
            window.open(src, "_blank");
        }
    </script>

    <!-- Print Styles -->
    <style>
        @media print {
            body * { visibility: hidden; }
            #reportArea, #reportArea * { visibility: visible; }
            #reportArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</x-app-layout>
