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
                        <div class="mb-3 text-black">
                            <strong>Total Transactions:</strong> {{ $total_transactions ?? 0 }} <br>
                            <strong>Total Amount:</strong> ₱{{ number_format($total_amount ?? 0, 2) }}
                        </div>

                        <!-- Transactions Table -->
                        <table class="w-full border-2 border-black text-black">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-black p-2 text-black">Fullname</th>
                                    <th class="border border-black p-2 text-black">Amount</th>
                                    <th class="border border-black p-2 text-black">Date and Time</th>
                                    <th class="border border-black p-2 text-black">Transaction Type</th>
                                    <th class="border border-black p-2 text-black" style="max-width: 150px;">Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
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
                                        <td class="border border-black px-2 py-2 text-black">{{ $transaction->full_name }}</td>
                                        <td class="border border-black px-2 py-2 text-black">₱{{ number_format($transaction->amount ?? 0, 2) }}</td>
                                        <td class="border border-black px-2 py-2 text-black">
                                            {{ \Carbon\Carbon::parse($transaction->date ?? now())->format('F d, Y h:i A') }}
                                        </td>
                                        <td class="border border-black px-2 py-2 text-black">{{ $transaction->transaction_type }}</td>
                                        <td class="border border-black px-2 py-2 text-black" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px;">
                                            {{ Str::limit($transaction->transaction_id, 40, '...') }}
                                        </td>
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
            <h2 class="text-xl font-bold mb-4 text-black">Transaction Details</h2>
            <p class="text-black"><strong>Fullname:</strong> <span id="modalFullname"></span></p>
            <p class="text-black"><strong>Amount:</strong> ₱<span id="modalAmount"></span></p>
            <p class="text-black"><strong>Date:</strong> <span id="modalDate"></span></p>
            <p class="text-black"><strong>Type:</strong> <span id="modalType"></span></p>

            <div class="mt-4">
                <strong class="text-black">Transaction Proof:</strong>
                <div id="modalProofContainer" class="mt-2">
                    <img id="modalProof"
                         src=""
                         alt="Transaction Image"
                         class="w-full h-48 object-cover border rounded shadow cursor-pointer hidden"
                         onclick="openImageInNewTab(this.src)">
                    <p id="noProofMessage" class="text-gray-500 text-sm hidden">No transaction proof available</p>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    type="button" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script>
        function showTransactionModal(fullname, amount, date, type, proofUrl) {
            document.getElementById("modalFullname").textContent = fullname;
            document.getElementById("modalAmount").textContent = parseFloat(amount).toFixed(2);
            document.getElementById("modalDate").textContent = date;
            document.getElementById("modalType").textContent = type;

            let proofImg = document.getElementById("modalProof");
            let noProofMsg = document.getElementById("noProofMessage");

            // Check if proofUrl exists and is a valid URL (starts with http)
            if (proofUrl && proofUrl !== "null" && proofUrl !== "" && proofUrl.startsWith("http")) {
                proofImg.src = proofUrl;
                proofImg.classList.remove("hidden");
                noProofMsg.classList.add("hidden");
            } else {
                proofImg.classList.add("hidden");
                noProofMsg.classList.remove("hidden");
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
