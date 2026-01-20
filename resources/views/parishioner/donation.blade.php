<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden">
                <div class="p-1 text-gray-900">
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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Donations List</h3>
                        <button class="btn bg-blue-700 text-white hover:bg-blue-800" onclick="addModal.showModal()">
                            Donate Now
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Donor Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($donations as $donation)
                                    <tr class="hover:bg-gray-50" onclick="viewModal{{ $donation->id }}.showModal()">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->donor_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
    {{ \Carbon\Carbon::parse($donation->created_at)->setTimezone('Asia/Manila')->format('F d, Y g:i A') }}

</td>


                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->status }}</td>
                                    </tr>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $donation->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg">
                                            <div class="flex items-center gap-2">
                                                <button class="btn text-black hover:bg-green-700 hover:text-white me-2"
                                                    type="button" onclick="viewModal{{ $donation->id }}.close()">
                                                    <i class='bx bx-left-arrow-alt'></i>
                                                </button>
                                                <h3 class="text-lg font-bold">View Donation</h3>
                                            </div>
                                            <hr class="my-4">
                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Full Name:
                                                    <input type="text" name="donor_name"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $donation->donor_name }}" readonly />
                                                </label>
                                            </div>
                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Email Address:
                                                    <input type="text" name="donor_email"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $donation->donor_email }}" readonly />
                                                </label>
                                            </div>
                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Phone Number:
                                                    <input type="text" name="donor_phone"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $donation->donor_phone }}" readonly />
                                                </label>
                                            </div>
                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Amount:
                                                    <input type="text" name="amount"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $donation->amount }}" readonly />
                                                </label>
                                            </div>
                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Date:
                                                    <input type="text" name="date"
    class="grow border-none focus:ring-0 focus:border-none"
    value="{{ \Carbon\Carbon::parse($donation->donation_date)->format('F d, Y') }}" readonly />

                                                </label>
                                            </div>
                                            <div class="mb-4">
    <label class="flex flex-col gap-2">
        Transaction ID:
        @if($donation->transaction_id)
            <img src="{{ str_starts_with($donation->transaction_id, 'http') ? $donation->transaction_id : asset('assets/transactions/' . $donation->transaction_id) }}" 
                 alt="Transaction Image" 
                 class="max-w-full h-auto border rounded" />
        @else
            <span class="text-gray-500">No transaction image uploaded</span>
        @endif
    </label>
</div>

                                            <div class="mb-4">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Note:
                                                    <input type="text" name="note"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $donation->note }}" readonly />
                                                </label>
                                            </div>
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                    type="button"
                                                    onclick="viewModal{{ $donation->id }}.close()">Close</button>
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
    </div>

    <!-- Add Modal -->
    <dialog id="addModal" class="modal">
        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl bg-white" style="background-color: white !important;">
            <div class="flex items-center gap-2">
                <button class="btn bg-white text-black border border-gray-300 hover:bg-green-700 hover:text-white me-2" 
                    style="background-color: white !important; color: black !important;" type="button"
                    onclick="addModal.close()">
                    <i class='bx bx-left-arrow-alt'></i>
                </button>
                <h3 class="text-lg font-bold text-black" style="color: black !important;">Add Donation</h3>
            </div>
            <hr class="my-4">
            <p class="text-black" style="color: black !important;">Make an online donation to help our organization provide treatment, care and support services to
                vulnerable children. Please submit your donation through our secure and easy online donation form
                below.
            </p>
            <hr class="my-4">
            <form action="{{ route('donation.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="flex items-center justify-center mb-4">
        <img src="{{ asset('assets/img/logo.png') }}" alt="St. Michael the Archangel Parish Logo" class="w-36">
        <h3 class="text-xl font-bold mb-2 uppercase ms-4 text-center text-black" style="color: black !important;">
            Diocese of Calbayog<br>Parokya ni San Miguel Archangel<br>Basey, Samar
        </h3>
    </div>

    <h1 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Payment Details</h1>
    <p class="text-black" style="color: black !important;">Mobile Number: 09563782778</p>
    <hr class="my-4">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
       <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Full Name:</label>
            <input type="text" name="donor_name" required 
                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                style="background-color: white !important; color: black !important;" 
                placeholder="Enter full name">
        </div>

         <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Email Address:</label>
            <input type="email" name="donor_email" required 
                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                style="background-color: white !important; color: black !important;" 
                placeholder="Enter email address">
        </div>

         <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Phone Number:</label>
            <input type="text" name="donor_phone" required 
                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                style="background-color: white !important; color: black !important;" 
                placeholder="Enter phone number">
        </div>

        <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Donation Comment:</label>
                        <input type="text" name="note" 
                            class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                            style="background-color: white !important; color: black !important;" 
                            placeholder="Enter donation comment" />
                </div>
                <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Donation Amount (₱):</label>
                        <input type="number" name="amount" required 
                            class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                            style="background-color: white !important; color: black !important;" 
                            placeholder="Enter donation amount">
                    </div>
            <div class="mb-4">
                    <label class="block text-black font-medium mb-2" style="color: black !important;">Transaction ID:</label>
            <input type="file" name="transaction_id" accept="image/*" required 
                class="file-input file-input-bordered w-full bg-white text-black" 
                style="background-color: white !important; color: black !important;">
        </div>
    </div>

    <input type="hidden" name="donation_date" value="{{ now()->toDateString() }}" />

    <div class="mb-4 text-center mt-4">
        <img src="{{ asset('assets/img/qr_code.jpg') }}" alt="QR Code" class="w-52 mx-auto">
    </div>

    <hr class="my-4">
<div class="flex justify-end gap-2">
    <button
        id="donateBtn"
        type="submit"
        class="btn bg-blue-700 text-white hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        Donate
    </button>

        <button type="button" class="btn bg-white text-black border border-gray-300 hover:bg-red-700 hover:text-white" 
            style="background-color: white !important; color: black !important;" 
            onclick="addModal.close()">Close</button>
    </div>
</form>




        </div>
    </dialog>

    <script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form[action="{{ route('donation.store') }}"]');
    const donateBtn = document.getElementById('donateBtn');

    function showError(input, message) {
        input.classList.add('border-red-500');

        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-msg')) {
            const error = document.createElement('p');
            error.className = 'text-red-600 text-sm mt-1 error-msg';
            error.textContent = message;
            input.after(error);
        }
    }

    function clearError(input) {
        input.classList.remove('border-red-500');
        if (input.nextElementSibling && input.nextElementSibling.classList.contains('error-msg')) {
            input.nextElementSibling.remove();
        }
    }

    function getLabel(input) {
        const label = input.closest('div')?.querySelector('label');
        return label ? label.textContent.replace(':', '') : 'This field';
    }

    form.addEventListener('submit', function (e) {
        let valid = true;

        // clear previous errors
        form.querySelectorAll('.error-msg').forEach(el => el.remove());
        form.querySelectorAll('input').forEach(el => el.classList.remove('border-red-500'));

        const requiredFields = form.querySelectorAll('input[required]');

        requiredFields.forEach(field => {

            if (field.type === 'file') {
                if (!field.files || field.files.length === 0) {
                    valid = false;
                    showError(field, `${getLabel(field)} is required`);
                }
            } else {
                if (!field.value.trim()) {
                    valid = false;
                    showError(field, `${getLabel(field)} is required`);
                }
            }
        });

        if (!valid) {
            e.preventDefault(); // ❌ STOP SUBMIT
        }
    });

    // live clear error habang nagta-type
    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value.trim()) {
                clearError(input);
            }
        });

        input.addEventListener('change', () => {
            if (input.type === 'file' && input.files.length > 0) {
                clearError(input);
            }
        });
    });

});
</script>

</x-app-layout>
