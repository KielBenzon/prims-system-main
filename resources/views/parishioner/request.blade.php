<x-app-layout>
    <div class="py-2 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white overflow-hidden">
                <div class="p-4 text-gray-900">
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
                    <div class="mb-4">
                        <form action="{{ route('request') }}" method="GET" class="flex items-center" id="searchForm">
                            <input type="text" name="search" placeholder="Search Requests..."
                                class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                                style="background-color: white !important; color: black !important;" />
                            <button type="submit"
                                class="ml-2 bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Requests List</h3>
                        <button class="btn bg-blue-600 text-white hover:bg-blue-700 border-0"
                            onclick="document.getElementById('addModal').showModal()">
                            Request For Certificate
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Document Type</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Requested By</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time Requested</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Approved by</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Paid</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($requests as $request)
                                <tr class="cursor-pointer hover:bg-gray-100"
    onclick="document.getElementById('viewModal{{ $request->id }}').showModal()">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->document_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
    {{ \Carbon\Carbon::parse($request->created_at)->setTimezone('Asia/Manila')->format('F d, Y g:i A') }}

</td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->request_approved->name ?? 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->status }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->is_paid }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ strlen($request->notes) > 2 ? substr($request->notes, 0, 2) . '...' : $request->notes }}
                                    <td class="px-6 py-1 whitespace-nowrap">
    {{-- ================= PAY NOW ================= --}}
    @if ($request->status == 'Approved' && $request->is_paid == 'Unpaid')
        <button title="Pay Now"
            class="hover:bg-green-100 p-4 rounded-md mx-auto"
            onclick="event.stopPropagation(); document.getElementById('paymentModal{{ $request->id }}').showModal()">
            <img src="{{ asset('assets/img/pay.png') }}" class="w-[20px] h-[20px]">
        </button>
    @endif

    {{-- ================= EDIT ================= --}}
    <button title="Edit"
        class="hover:bg-blue-100 p- rounded-md {{ $request->status == 'Approved' ? 'opacity-50 cursor-not-allowed' : '' }}"
        {{ $request->status == 'Approved' ? 'disabled' : '' }}
        onclick="event.stopPropagation(); document.getElementById('editModal{{ $request->id }}').showModal()">
        <img src="{{ asset('assets/img/edit.svg') }}">
    </button>

    {{-- ================= DELETE ================= --}}
    <button title="Delete"
        class="hover:bg-red-100 p-3 rounded-md {{ $request->status == 'Approved' ? 'opacity-50 cursor-not-allowed' : '' }}"
        {{ $request->status == 'Approved' ? 'disabled' : '' }}
        onclick="event.stopPropagation(); document.getElementById('deleteModal{{ $request->id }}').showModal()">
        <img src="{{ asset('assets/img/delete.png') }}" class="w-[20px] h-[20px]">
    </button>
</td>


<!-- View Modal -->
<dialog id="viewModal{{ $request->id }}" class="modal">
    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-bold">Request Details</h2>
                <p class="text-sm text-gray-600">View full information of this request</p>
            </div>
            <button class="btn text-black hover:bg-red-700 hover:text-white"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Document Type:</strong> {{ $request->document_type }}</div>
            <div><strong>Requested By:</strong> {{ $request->user->name }}</div>
            <div><strong>Date & Time Requested:</strong> {{ $request->created_at }}</div>
            <div><strong>Approved By:</strong> {{ $request->request_approved->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ $request->status }}</div>
            <div><strong>Paid:</strong> {{ $request->is_paid }}</div>
            <div class="col-span-2">
                <strong>Notes:</strong>
                <p class="mt-1 p-2 bg-gray-100 rounded">{{ $request->notes ?: 'No notes provided.' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button class="btn bg-gray-600 text-white hover:bg-gray-700"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">Close</button>
        </div>
    </div>
</dialog>


                                <!-- Payment Modal -->
                                <dialog id="paymentModal{{ $request->id }}" class="modal">
                                    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl relative bg-white">
                                        <!-- Modal Header -->
                                        <button
    class="btn bg-gray-200 hover:bg-gray-300 text-black me-2 border-0"
    type="button"
    onclick="event.preventDefault(); this.closest('dialog').close();">
    <i class="bx bx-left-arrow-alt"></i>
</button>

                                        <div class="flex flex-col gap-2">
                                            <h2 class="text-lg font-bold text-black">Requested Document Payment Summary</h2>
                                            <p class="text-md">Scan the QRCode below to pay the bill.</p>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Payment Form -->
                                        <form action="{{ route('payment.update', $request->id) }}" method="POST" enctype="multipart/form-data">

                                            @csrf
                                            @method('PUT')

                                            <div class="flex gap-6 items-start">
                                                <!-- Dynamic QR Codes -->
                                                <div class="w-1/2">
                                                    @if ($request->document_type == 'Baptismal Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/baptismal_certificate.jpg') }}"
                                                        alt="Baptismal Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Marriage Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/marriage_certificate.jpg') }}"
                                                        alt="Marriage Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Death Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/death_certificate.jpg') }}"
                                                        alt="Death Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Confirmation Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/confirmation_certificate.jpg') }}"
                                                        alt="Confirmation Certificate"
                                                        class="w-full h-auto">
                                                    @else
                                                    <img
                                                        src="{{ asset('assets/img/gcash-popup.png') }}"
                                                        alt="Default GCash QR Code"
                                                        class="w-full h-auto">
                                                    @endif
                                                </div>

                                                <!-- Payment Summary and Transaction ID -->
                                                <div class="w-2/3 space-y-4">
                                                    <!-- Payment Summary -->
                                                    <div>
                                                        <dl class="flex items-center justify-between gap-4">
                                                            <dt class="text-gray-500 dark:text-gray-400">
                                                                {{ $request->document_type }}
                                                            </dt>
                                                            <dd class="text-base font-medium text-gray-900 dark:text-white">
                                                                ₱ {{ $request->certificate_type->amount ?? '' }}
                                                            </dd>

                                                        </dl>
                                                    </div>

                                                    <!-- Transaction ID and Number of Copies -->
                                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                                                        <input
                                                            type="text"
                                                            id="number-copies"
                                                            name="number_copies"
                                                            placeholder="Number of Copies"
                                                            required
                                                            class="input input-bordered w-full"
                                                            oninput="updateTotal()">

                                                            <label class="input input-bordered flex items-center gap-2 text-gray-400">
                                                                Transaction ID:
                                                                    <input
                                                                    type="file" 
                                                                    name="transaction_id" 
                                                                    placeholder="Transaction ID"
                                                                    id="transaction_id"
                                                                    accept="image/*"
                                                                    class="grow border-none focus:ring-0 focus:border-none"
                                                                    value="{{ $request->payment->transaction_id ?? '' }}"
                                                                    required
                                                                    />
                                                            </label>
                                                        <input
                                                            type="text"
                                                            name="to_pay"
                                                            placeholder="Paid Amount"
                                                            id="to_pay"
                                                            oninput="validatePayment()"
                                                            class="input input-bordered w-full"
                                                            required>
                                                    </div>
                                                    <div class="text-base font-medium text-black" style="color: black !important;">
                                                        Total: ₱ <span id="total-amount">0</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4">

                                            <!-- Modal Actions -->
                                            <div class="flex justify-end gap-2">
                                                <button
    class="btn bg-gray-200 hover:bg-gray-300 text-black border-0"
    type="button"
    onclick="event.preventDefault(); this.closest('dialog').close();">
    Close
</button>

                                                <button
                                                    name="submit"
                                                    id="complete-payment-button"
                                                    class="btn bg-blue-600 text-white hover:bg-blue-700 border-0"
                                                    type="submit"
                                                    disabled>
                                                    Complete Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
<dialog id="editModal{{ $request->id }}" class="modal">
    <div class="modal-box bg-white" style="background-color: white !important;">
        <h3 class="font-bold text-lg mb-4 text-black" style="color: black !important;">Edit Request</h3>

        <form method="POST" action="{{ route('request.update', $request->id) }}">
            @csrf
            @method('PUT')
            

            <div class="mb-4">
                    <label class="block text-black font-medium" style="color: black !important;">Document Type</label>
                    <select name="document_type"
    id="document_type_{{ $request->id }}"
    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black"
    style="background-color: white !important; color: black !important;"
    required>
    <option value="" style="color: black;">Select Document Type</option>
    <option value="Baptismal Certificate" style="color: black;">Baptismal Certificate</option>
    <option value="Marriage Certificate" style="color: black;">Marriage Certificate</option>
    <option value="Death Certificate" style="color: black;">Death Certificate</option>
    <option value="Confirmation Certificate" style="color: black;">Confirmation Certificate</option>
</select>

                </div>
                <hr class="my-4">
                <div id="baptism_{{ $request->id }}" class="hidden">
                    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Baptismal Certificate Details</h2>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Child: </label>
                            <input type="text" name="name_of_child" placeholder="Name of Child"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Date of Birth: </label>
                            <input type="date" name="date_of_birth" placeholder="Date of Birth"
                                class="input input-bordered w-full bg-white text-black border-gray-300"
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Place of Birth: </label>
                            <input type="text" name="place_of_birth" placeholder="Place of Birth"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Date of Baptism: </label>
                            <input type="date" name="baptism_schedule" placeholder="Date of Baptism"
                                class="input input-bordered w-full bg-white text-black border-gray-300"
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Father: </label>
                            <input type="text" name="name_of_father" placeholder="Name of Father"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Mother: </label>
                            <input type="text" name="name_of_mother" placeholder="Name of Mother"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
    <label for="gmail" class="block text-black font-medium" style="color: black !important;">Gmail:</label>
    <input type="email" 
           name="gmail" 
           id="gmail"
           placeholder="Enter your Gmail"
           class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
           style="background-color: white !important; color: black !important;"
           value="{{ old('gmail', $request->user->email ?? '') }}" />
</div>

                    </div>
                </div>

                <div id="marriage_{{ $request->id }}" class="hidden">
                    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Marriage Certificate Details</h2>
                    <h3 class="text-md font-bold mb-4 text-black" style="color: black !important;">Bride Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Bride Name: </label>
                            <input type="text" name="bride_name" placeholder="Bride Name"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500"
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Birthdate of Bride: </label>
                            <input type="date" id="birthdate_bride" name="birthdate_bride"
                                placeholder="Birthdate of Bride" class="input input-bordered w-full bg-white text-black border-gray-300"
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Age of Bride: </label>
                            <input type="text" id="age_bride" name="age_bride" placeholder="Age of Bride"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthplace of Bride: </label>
                            <input type="text" name="birthplace_bride" placeholder="Birthplace of Bride"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Citizenship of Bride: </label>
                            <input type="text" name="citizenship_bride" placeholder="Citizenship of Bride"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Religion of Bride: </label>
                            <input type="text" name="religion_bride" placeholder="Religion of Bride"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Residence of Bride: </label>
                            <input type="text" name="residence_bride" placeholder="Residence of Bride"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Civil Status of Bride: </label>
                            <input type="text" name="civil_status_bride" placeholder="Civil Status of Bride"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Father of Bride: </label>
                            <input type="text" name="name_of_father_bride" placeholder="Name of Father of Bride"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Mother of Bride: </label>
                            <input type="text" name="name_of_mother_bride" placeholder="Name of Mother of Bride"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <h3 class="text-md font-bold mb-4">Groom Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Groom: </label>
                            <input type="text" name="name_of_groom" placeholder="Name of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthdate of Groom: </label>
                            <input type="date" id="birthdate_groom" name="birthdate_groom"
                                placeholder="Birthdate of Groom" class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Age of Groom: </label>
                            <input type="text" id="age_groom" name="age_groom" placeholder="Age of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthplace of Groom: </label>
                            <input type="text" name="birthplace_groom" placeholder="Birthplace of Groom"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Citizenship of Groom: </label>
                            <input type="text" name="citizenship_groom" placeholder="Citizenship of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Religion of Groom: </label>
                            <input type="text" name="religion_groom" placeholder="Religion of Groom"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Residence of Groom: </label>
                            <input type="text" name="residence_groom" placeholder="Residence of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Civil Status of Groom: </label>
                            <input type="text" name="civil_status_groom" placeholder="Civil Status of Groom"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Father of Groom: </label>
                            <input type="text" name="name_of_father_groom" placeholder="Name of Father of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Mother of Groom: </label>
                            <input type="text" name="name_of_mother_groom" placeholder="Name of Mother of Groom"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
    <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
    <input type="email" 
           name="gmail" 
           id="gmail"
           placeholder="Enter your Gmail"
           class="input input-bordered w-full" 
           value="{{ old('gmail', $request->user->email ?? '') }}" />
</div>
                    </div>
                </div>

                <div id="death_{{ $request->id }}" class="hidden">
    <h2 class="text-lg font-bold mb-4">Death Certificate Details</h2>
    <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">

        <!-- First Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">First Name of Deceased:</label>
            <input type="text" name="first_name_death" placeholder="First Name"
                   class="input input-bordered w-full">
        </div>

        <!-- Middle Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Middle Name of Deceased:</label>
            <input type="text" name="middle_name_death" placeholder="Middle Name"
                   class="input input-bordered w-full">
        </div>

        <!-- Last Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Last Name of Deceased:</label>
            <input type="text" name="last_name_death" placeholder="Last Name"
                   class="input input-bordered w-full">
        </div>

        <!-- Date of Birth -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Date of Birth:</label>
            <input type="date" name="date_of_birth_death" class="input input-bordered w-full">
        </div>

        <!-- Date of Death -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Date of Death:</label>
            <input type="date" name="date_of_death" class="input input-bordered w-full">
        </div>

        <!-- File Upload -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Death Certificate (Hospital Record):</label>
            <input type="file" name="file_death" class="input input-bordered w-full">
            <p class="text-gray-500 text-sm italic mt-1">
                Note: Please attach the death certificate of the deceased.
            </p>
        </div>

        <!-- Gmail -->
        <div class="sm:col-span-3">
            <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
            <input type="email" 
                   name="gmail" 
                   id="gmail"
                   placeholder="Enter your Gmail"
                   class="input input-bordered w-full" 
                   value="{{ old('gmail', $request->user->email ?? '') }}" />
        </div>

    </div>
</div>


                <div id="confirmation_{{ $request->id }}" class="hidden">
                    <h2 class="text-lg font-bold mb-4">Confirmation Certificate Details</h2>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name: </label>
                            <input type="text" name="confirmation_first_name" placeholder="First Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name: </label>
                            <input type="text" name="confirmation_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name: </label>
                            <input type="text" name="confirmation_last_name" placeholder="Last Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Birth: </label>
                            <input type="date" name="confirmation_date_of_birth" placeholder="Date of Birth"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Confirmation: </label>
                            <input type="date" name="confirmation_date_of_confirmation"
                                placeholder="Date of Confirmation" class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Upload Confirmation Document: </label>
                            <input type="file" name="file_confirmation"
                                class="file-input file-input-bordered w-full" accept="image/*,.pdf">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <!-- Gmail -->
        <div class="sm:col-span-3">
            <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
            <input type="email" 
                   name="gmail" 
                   id="gmail"
                   placeholder="Enter your Gmail"
                   class="input input-bordered w-full" 
                   value="{{ old('gmail', $request->user->email ?? '') }}" />
        </div>
                    </div>
                </div>

                
               <hr class="my-4">

<div class="flex justify-end gap-2">

    <!-- CLOSE BUTTON (Dialog close only) -->
    <button type="button" class="btn bg-white text-black border border-gray-300 hover:bg-red-700 hover:text-white"
        style="background-color: white !important; color: black !important;"
        onclick="document.getElementById('editModal{{ $request->id }}').close()">
    Close
</button>


    <!-- SUBMIT REQUEST BUTTON -->
    <form method="POST" action="{{ route('request.store') }}">
        @csrf
        <button id="submitRequestBtn"
            type="submit"
            class="btn bg-blue-700 hover:bg-blue-800 text-white">
            Submit Request
        </button>
    </form>

</div>

    </dialog>
<dialog id="deleteModal{{ $request->id }}" class="modal">
    <div class="modal-box bg-white" style="background-color: white !important;">
        <h3 class="font-bold text-lg text-red-600" style="color: #dc2626 !important;">Delete Request</h3>

        <p class="py-4 text-black" style="color: black !important;">
            Are you sure you want to delete this request?
            <br>
            <span class="text-sm text-gray-500">This action cannot be undone.</span>
        </p>

        <div class="modal-action flex gap-2">
            <button class="btn bg-white text-black border border-gray-300 hover:bg-gray-100" 
                style="background-color: white !important; color: black !important;"
                onclick="document.getElementById('deleteModal{{ $request->id }}').close()">
                Cancel
            </button>
            
            <form method="POST" action="{{ route('request.destroy', $request->id) }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn bg-red-600 text-white hover:bg-red-700 border-0">
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</dialog>

 
<!-- View Modal -->
<dialog id="viewModal{{ $request->id }}" class="modal">
    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-bold">Request Details</h2>
                <p class="text-sm text-gray-600">View full information of this request</p>
            </div>
            <button class="btn text-black hover:bg-red-700 hover:text-white"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Document Type:</strong> {{ $request->document_type }}</div>
            <div><strong>Requested By:</strong> {{ $request->user->name }}</div>
            <div><strong>Date & Time Requested:</strong> {{ $request->created_at }}</div>
            <div><strong>Approved By:</strong> {{ $request->request_approved->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ $request->status }}</div>
            <div><strong>Paid:</strong> {{ $request->is_paid }}</div>
            <div class="col-span-2">
                <strong>Notes:</strong>
                <p class="mt-1 p-2 bg-gray-100 rounded">{{ $request->notes ?: 'No notes provided.' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button class="btn bg-gray-600 text-white hover:bg-gray-700"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">Close</button>
        </div>
    </div>
</dialog>


                                <!-- Payment Modal -->
                                <dialog id="paymentModal{{ $request->id }}" class="modal">
                                    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl relative bg-white">
                                        <!-- Modal Header -->
                                        <button
    class="btn bg-gray-200 hover:bg-gray-300 text-black me-2 border-0"
    type="button"
    onclick="event.preventDefault(); this.closest('dialog').close();">
    <i class="bx bx-left-arrow-alt"></i>
</button>

                                        <div class="flex flex-col gap-2">
                                            <h2 class="text-lg font-bold text-black">Requested Document Payment Summary</h2>
                                            <p class="text-md">Scan the QRCode below to pay the bill.</p>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Payment Form -->
                                        <form action="{{ route('payment.update', $request->id) }}" method="POST" enctype="multipart/form-data">

                                            @csrf
                                            @method('PUT')

                                            <div class="flex gap-6 items-start">
                                                <!-- Dynamic QR Codes -->
                                                <div class="w-1/2">
                                                    @if ($request->document_type == 'Baptismal Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/baptismal_certificate.jpg') }}"
                                                        alt="Baptismal Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Marriage Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/marriage_certificate.jpg') }}"
                                                        alt="Marriage Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Death Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/death_certificate.jpg') }}"
                                                        alt="Death Certificate"
                                                        class="w-full h-auto">
                                                    @elseif ($request->document_type == 'Confirmation Certificate')
                                                    <img
                                                        src="{{ asset('assets/img/confirmation_certificate.jpg') }}"
                                                        alt="Confirmation Certificate"
                                                        class="w-full h-auto">
                                                    @else
                                                    <img
                                                        src="{{ asset('assets/img/gcash-popup.png') }}"
                                                        alt="Default GCash QR Code"
                                                        class="w-full h-auto">
                                                    @endif
                                                </div>

                                                <!-- Payment Summary and Transaction ID -->
                                                <div class="w-2/3 space-y-4">
                                                    <!-- Payment Summary -->
                                                    <div>
                                                        <dl class="flex items-center justify-between gap-4">
                                                            <dt class="text-gray-500 dark:text-gray-400">
                                                                {{ $request->document_type }}
                                                            </dt>
                                                            <dd class="text-base font-medium text-gray-900 dark:text-white">
                                                                ₱ {{ $request->certificate_type->amount ?? '' }}
                                                            </dd>

                                                        </dl>
                                                    </div>

                                                    <!-- Transaction ID and Number of Copies -->
                                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                                                        <input
                                                            type="text"
                                                            id="number-copies"
                                                            name="number_copies"
                                                            placeholder="Number of Copies"
                                                            required
                                                            class="input input-bordered w-full"
                                                            oninput="updateTotal()">

                                                            <label class="input input-bordered flex items-center gap-2 text-gray-400">
                                                                Transaction ID:
                                                                    <input
                                                                    type="file" 
                                                                    name="transaction_id" 
                                                                    placeholder="Transaction ID"
                                                                    id="transaction_id"
                                                                    accept="image/*"
                                                                    class="grow border-none focus:ring-0 focus:border-none"
                                                                    value="{{ $request->payment->transaction_id ?? '' }}"
                                                                    required
                                                                    />
                                                            </label>
                                                        <input
                                                            type="text"
                                                            name="to_pay"
                                                            placeholder="Paid Amount"
                                                            id="to_pay"
                                                            oninput="validatePayment()"
                                                            class="input input-bordered w-full"
                                                            required>
                                                    </div>
                                                    <div class="text-base font-medium text-black" style="color: black !important;">
                                                        Total: ₱ <span id="total-amount">0</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4">

                                            <!-- Modal Actions -->
                                            <div class="flex justify-end gap-2">
                                                <button
    class="btn bg-gray-200 hover:bg-gray-300 text-black border-0"
    type="button"
    onclick="event.preventDefault(); this.closest('dialog').close();">
    Close
</button>

                                                <button
                                                    name="submit"
                                                    id="complete-payment-button"
                                                    class="btn bg-blue-600 text-white hover:bg-blue-700 border-0"
                                                    type="submit"
                                                    disabled>
                                                    Complete Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
                                <!-- View Modal -->
                                <dialog id="viewModal{{ $request->id }}" class="modal">
                                    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl">
                                        <div class="flex items-center gap-4">
                                            <button class="btn bg-gray-200 hover:bg-gray-300 text-black me-2 border-0"
                                                type="button" onclick="viewModal{{ $request->id }}.close()">
                                                <i class='bx bx-left-arrow-alt'></i>
                                            </button>
                                            <div>
                                                <h2 class="text-lg font-bold">{{ $request->document_type }}
                                                    Details</h2>
                                                <p class="text-gray-600 text-[12px]">Requested by:
                                                    {{ $request->requested_by }}
                                                </p>
                                                <p class="text-gray-600 text-[12px]">Requested on:
                                                    {{ $request->created_at }}
                                                </p>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        @if ($request->document_type == 'Baptismal Certificate' && $request->id)
                                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Certificate Type:
                                                    <input type="text" name="certificate_type"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->certificate_type }}"
                                                        readonly />
                                                </label>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Name of Child:
                                                    <input type="text" name="name_of_child"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->name_of_child }}"
                                                        readonly />
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Date of Birth:
                                                    <input type="text" name="date_of_birth"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->date_of_birth }}"
                                                        readonly />
                                                </label>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Place of Birth:
                                                    <input type="text" name="place_of_birth"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->place_of_birth }}"
                                                        readonly />
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                            <div class="sm:col-span-6">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Date of Baptism:
                                                    <input type="text" name="baptism_schedule"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->baptism_schedule }}"
                                                        readonly />
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Name of Father:
                                                    <input type="text" name="name_of_father"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->name_of_father }}"
                                                        readonly />
                                                </label>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="input input-bordered flex items-center gap-2">
                                                    Name of Mother:
                                                    <input type="text" name="name_of_mother"
                                                        class="grow border-none focus:ring-0 focus:border-none"
                                                        value="{{ $request->certificate_detail->name_of_mother }}"
                                                        readonly />
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                        @if ($request->document_type == 'Marriage Certificate' && $request->id && isset($request->certificate_detail) && property_exists($request->certificate_detail, 'bride_name'))
                                        <div class="flex flex-col gap-4">
                                            <h3 class="text-md font-bold mb-4">Bride Information</h3>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Bride Name:
                                                        <input type="text" name="bride_name"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->bride_name ?? 'N/A' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Birthdate of Bride:
                                                        <input type="text" name="birthdate_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->birthdate_bride ?? 'N/A' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Age of Bride:
                                                        <input type="text" name="age_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->age_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Birthplace of Bride:
                                                        <input type="text" name="birthplace_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->birthplace_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Citizenship of Bride:
                                                        <input type="text" name="citizenship_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->citizenship_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Religion of Bride:
                                                        <input type="text" name="religion_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->religion_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Residence of Bride:
                                                        <input type="text" name="residence_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->residence_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Civil Status of Bride:
                                                        <input type="text" name="civil_status_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->civil_status_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Father of Bride:
                                                        <input type="text" name="name_of_father_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_father_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Mother of Bride:
                                                        <input type="text" name="name_of_mother_bride"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_mother_bride }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <h3 class="text-md font-bold mb-4">Groom Information</h3>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Groom:
                                                        <input type="text" name="name_of_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Birthdate of Groom:
                                                        <input type="text" name="birthdate_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->birthdate_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Age of Groom:
                                                        <input type="text" name="age_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->age_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Birthplace of Groom:
                                                        <input type="text" name="birthplace_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->birthplace_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Citizenship of Groom:
                                                        <input type="text" name="citizenship_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->citizenship_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Religion of Groom:
                                                        <input type="text" name="religion_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->religion_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Residence of Groom:
                                                        <input type="text" name="residence_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->residence_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Civil Status of Groom:
                                                        <input type="text" name="civil_status_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->civil_status_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Father of Groom:
                                                        <input type="text" name="name_of_father_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_father_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Mother of Groom:
                                                        <input type="text" name="name_of_mother_groom"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_mother_groom }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if ($request->document_type == 'Death Certificate' && $request->id)
                                        <div class="flex flex-col gap-4">
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        First Name of Deceased:
                                                        <input type="text" name="first_name_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->first_name_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Middle Name of Deceased:
                                                        <input type="text" name="middle_name_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->middle_name_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Last Name of Deceased:
                                                        <input type="text" name="last_name_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->last_name_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Date of Birth:
                                                        <input type="text" name="date_of_birth_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->date_of_birth_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Date of Death:
                                                        <input type="text" name="date_of_death_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->date_of_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Minister of Deceased:
                                                        <input type="text" name="minister_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->minister_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Death Certificate (Hospital Record):
                                                        <input type="text" name="file_death"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->file_death ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if ($request->document_type == 'Confirmation Certificate' && $request->id)
                                        <div class="flex flex-col gap-4">
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Name of Confirmand:
                                                        <input type="text" name="name_of_confirmand"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->name_of_confirmand ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Date of Birth:
                                                        <input type="date" name="date_of_birth_confirmand"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->date_of_birth_confirmand ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label
                                                        class="input input-bordered flex items-center gap-2">
                                                        Date of Confirmation:
                                                        <input type="date" name="date_of_confirmation"
                                                            class="grow border-none focus:ring-0 focus:border-none"
                                                            value="{{ $request->certificate_detail->date_of_confirmation ?? '' }}"
                                                            readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    @if($request->certificate_detail->file_confirmation ?? null)
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900 mb-2">Confirmation Document:</p>
                                                            <img src="{{ $request->certificate_detail->file_confirmation }}" 
                                                                 alt="Confirmation Certificate" 
                                                                 class="max-w-full rounded border border-gray-300"
                                                                 style="max-height: 300px; object-fit: contain;">
                                                        </div>
                                                    @else
                                                        <label class="input input-bordered flex items-center gap-2">
                                                            Confirmation Document:
                                                            <span class="grow text-gray-500">No file uploaded</span>
                                                        </label>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                            @endif
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                    type="button"
                                                    onclick="document.getElementById('viewModal{{ $request->id }}').close()">Close</button>
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

    


    <!-- Add Document Modal -->
    <dialog id="addModal" class="modal">
        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl bg-white" style="background-color: white !important;">
            <div class="flex items-center">
                <button class="btn bg-white text-black border border-gray-300 hover:bg-green-700 hover:text-white me-2" 
                    style="background-color: white !important; color: black !important;" type="button"
                    onclick="addModal.close()">
                    <i class='bx bx-left-arrow-alt'></i>
                </button>
                <h3 class="text-lg font-bold text-black" style="color: black !important;">Add Request</h3>
            </div>
            <hr class="my-4">
            <form action="{{ route('request.store') }}" method="POST" id="addRequestForm"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="requested_by" value="{{ Auth::user()->name }}">
                <input type="hidden" name="status" value="Pending">
                <input type="hidden" name="is_paid" value="Unpaid">
                <div class="flex items-center justify-center">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="St. Michael the Archangel Parish Logo"
                        class="w-24 mb-4">
                    <h3 class="text-lg font-bold mb-2 uppercase ms-4 text-center text-black" style="color: black !important;">St. Michael the Archangel
                        Parish Diocese of Calbayog<br>Basey, Samar 6720 Philippines</h3>
                </div>

                <ol
                    class="flex items-center mb-2 w-full p-3 space-x-2 text-sm font-medium text-center text-gray-500 bg-white border border-gray-200 rounded-lg shadow-s sm:text-base sm:p-4 sm:space-x-4 rtl:space-x-reverse">
                    <li id="documentDetails" class="flex items-center text-blue-600 ">
                        <span
                            class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-blue-600 rounded-full shrink-0">
                            1
                        </span>
                        <span class="hidden sm:inline-flex sm:ms-2">Request for Document</span>
                        <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4" />
                        </svg>
                    </li>
                    <li id="documentDetails" class="flex items-center">
                        <span
                            class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 ">
                            2
                        </span>
                        <span class="hidden sm:inline-flex sm:ms-2">Document Details</span>
                        <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4" />
                        </svg>
                    </li>
                    <li id="documentDetails" class="flex items-center">
                        <span
                            class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-gray-500 rounded-full shrink-0 ">
                            3
                        </span>
                        Payment Information
                    </li>
                </ol>


                <div class="mb-4">
                    <label class="block text-black font-medium" style="color: black !important;">Document Type</label>
                    <select name="document_type" id="document_type"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black"
                        style="background-color: white !important; color: black !important;" required>
                        <option value="" readonly style="color: black;">Select Document Type</option>
                        <option value="Baptismal Certificate" style="color: black;">Baptismal Certificate</option>
                        <option value="Marriage Certificate" style="color: black;">Marriage Certificate</option>
                        <option value="Death Certificate" style="color: black;">Death Certificate</option>
                        <option value="Confirmation Certificate" style="color: black;">Confirmation Certificate</option>
                    </select>
                </div>
                <hr class="my-4">
                <div id="baptism" class="hidden">
                    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Baptismal Certificate Details</h2>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">First Name of Child: </label>
                            <input type="text" name="first_name_child" placeholder=" First Name of Child"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Middle Name of Child: </label>
                            <input type="text" name="middle_name_child" placeholder=" Middle Name of Child"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Last Name of Child: </label>
                            <input type="text" name="last_name_child" placeholder=" Last Name of Child"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Date of Birth: </label>
                            <input type="date" name="date_of_birth" placeholder="Date of Birth"
                                class="input input-bordered w-full required bg-white text-black border-gray-300" 
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Place of Birth: </label>
                            <input type="text" name="place_of_birth" placeholder="Place of Birth"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Date of Baptism: </label>
                            <input type="date" name="baptism_schedule" placeholder="Date of Baptism"
                                class="input input-bordered w-full required bg-white text-black border-gray-300" 
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Father: </label>
                            <input type="text" name="name_of_father" placeholder="Name of Father"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Mother: </label>
                            <input type="text" name="name_of_mother" placeholder="Name of Mother"
                                class="input input-bordered w-full required bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
    <label for="gmail" class="block text-black font-medium" style="color: black !important;">Gmail:</label>
    <input type="email" 
           name="gmail" 
           id="gmail"
           placeholder="Enter your Gmail"
           class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
           style="background-color: white !important; color: black !important;"
           value="{{ old('gmail', $request->user->email ?? '') }}" />
</div>

                    </div>
                </div>

                <div id="marriage" class="hidden">
                    <h2 class="text-lg font-bold mb-4">Marriage Certificate Details</h2>
                    <h3 class="text-md font-bold mb-4">Bride Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name of Bride: </label>
                            <input type="text" name="bride_first_name" placeholder="First Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name of Bride: </label>
                            <input type="text" name="bride_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name of Bride: </label>
                            <input type="text" name="bride_last_name" placeholder="Last Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthdate of Bride: </label>
                            <input type="date" id="birthdate_bride" name="birthdate_bride"
                                placeholder="Birthdate of Bride" class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Age of Bride: </label>
                            <input type="number" id="age_bride" name="age_bride" placeholder="Age of Bride"
                                min="18" max="120" class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthplace of Bride: </label>
                            <input type="text" name="birthplace_bride" placeholder="Birthplace of Bride"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Citizenship of Bride: </label>
                            <input type="text" name="citizenship_bride" placeholder="Citizenship of Bride"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Religion of Bride: </label>
                            <input type="text" name="religion_bride" placeholder="Religion of Bride"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Residence of Bride: </label>
                            <input type="text" name="residence_bride" placeholder="Residence of Bride"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Civil Status of Bride: </label>
                            <input type="text" name="civil_status_bride" placeholder="Civil Status of Bride"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Father of Bride: </label>
                            <input type="text" name="name_of_father_bride" placeholder="Name of Father of Bride"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Mother of Bride: </label>
                            <input type="text" name="name_of_mother_bride" placeholder="Name of Mother of Bride"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <h3 class="text-md font-bold mb-4">Groom Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name of Groom: </label>
                            <input type="text" name="groom_first_name" placeholder="First Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name of Groom: </label>
                            <input type="text" name="groom_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name of Groom: </label>
                            <input type="text" name="groom_last_name" placeholder="Last Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthdate of Groom: </label>
                            <input type="date" id="birthdate_groom" name="birthdate_groom"
                                placeholder="Birthdate of Groom" class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Age of Groom: </label>
                            <input type="number" id="age_groom" name="age_groom" placeholder="Age of Groom"
                                min="18" max="120" class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthplace of Groom: </label>
                            <input type="text" name="birthplace_groom" placeholder="Birthplace of Groom"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Citizenship of Groom: </label>
                            <input type="text" name="citizenship_groom" placeholder="Citizenship of Groom"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Religion of Groom: </label>
                            <input type="text" name="religion_groom" placeholder="Religion of Groom"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Residence of Groom: </label>
                            <input type="text" name="residence_groom" placeholder="Residence of Groom"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Civil Status of Groom: </label>
                            <input type="text" name="civil_status_groom" placeholder="Civil Status of Groom"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Father of Groom: </label>
                            <input type="text" name="name_of_father_groom" placeholder="Name of Father of Groom"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Name of Mother of Groom: </label>
                            <input type="text" name="name_of_mother_groom" placeholder="Name of Mother of Groom"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
    <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
    <input type="email" 
           name="gmail" 
           id="gmail"
           placeholder="Enter your Gmail"
           class="input input-bordered w-full" 
           value="{{ old('gmail', $request->user->email ?? '') }}" />
</div>
                    </div>
                </div>

                <div id="death" class="hidden">
    <h2 class="text-lg font-bold mb-4">Death Certificate Details</h2>
    <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">

        <!-- First Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">First Name of Deceased:</label>
            <input type="text" name="first_name_death" placeholder="First Name"
                   class="input input-bordered w-full required">
        </div>

        <!-- Middle Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Middle Name of Deceased:</label>
            <input type="text" name="middle_name_death" placeholder="Middle Name"
                   class="input input-bordered w-full required">
        </div>

        <!-- Last Name -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Last Name of Deceased:</label>
            <input type="text" name="last_name_death" placeholder="Last Name"
                   class="input input-bordered w-full required">
        </div>

        <!-- Date of Birth -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Date of Birth:</label>
            <input type="date" name="date_of_birth_death" class="input input-bordered w-full required">
        </div>

        <!-- Date of Death -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Date of Death:</label>
            <input type="date" name="date_of_death" class="input input-bordered w-full required">
        </div>

        <!-- File Upload -->
        <div class="sm:col-span-3">
            <label class="block text-gray-700 font-medium">Death Certificate (Hospital Record):</label>
            <input type="file" name="file_death" class="input input-bordered w-full ">
            <p class="text-gray-500 text-sm italic mt-1">
                Note: Please attach the death certificate of the deceased.
            </p>
        </div>

        <!-- Gmail -->
        <div class="sm:col-span-3">
            <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
            <input type="email" 
                   name="gmail" 
                   id="gmail"
                   placeholder="Enter your Gmail"
                   class="input input-bordered w-full" 
                   value="{{ old('gmail', $request->user->email ?? '') }}" />
        </div>

    </div>
</div>


                <div id="confirmation" class="hidden">
                    <h2 class="text-lg font-bold mb-4">Confirmation Certificate Details</h2>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name: </label>
                            <input type="text" name="confirmation_first_name" placeholder="First Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name: </label>
                            <input type="text" name="confirmation_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name: </label>
                            <input type="text" name="confirmation_last_name" placeholder="Last Name"
                                class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Birth: </label>
                            <input type="date" name="confirmation_date_of_birth" placeholder="Date of Birth"
                                class="input input-bordered w-full required">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Confirmation: </label>
                            <input type="date" name="confirmation_date_of_confirmation"
                                placeholder="Date of Confirmation" class="input input-bordered w-full required">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Upload Confirmation Document: </label>
                            <input type="file" name="file_confirmation"
                                class="file-input file-input-bordered w-full" accept="image/*,.pdf">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <!-- Gmail -->
                        <div class="sm:col-span-3">
                            <label for="gmail" class="block text-gray-700 font-medium">Gmail:</label>
                            <input type="email" 
                                   name="gmail" 
                                   id="gmail"
                                   placeholder="Enter your Gmail"
                                   class="input input-bordered w-full" 
                                   value="{{ old('gmail', $request->user->email ?? '') }}" />
                        </div>
                    </div>
                </div>
                <hr class="my-4">

                <div class="flex justify-end">
                    <button class="btn bg-white text-black border border-gray-300 hover:bg-red-700 hover:text-white mr-2" 
                        style="background-color: white !important; color: black !important;" type="button"
                        onclick="document.getElementById('addModal').close()">Close</button>
                    <button id="submitRequestBtn"
    type="submit"
    class="btn bg-blue-700 hover:bg-blue-800 text-white">
    Submit Request
</button>

                </div>

            </form>
        </div>
    </dialog>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const popup = document.getElementById('gcash-popup');
        const transactionContainer = document.getElementById('transaction-id-container');

        function togglePopup(show) {
            if (show) {
                console.log('show');
                popup.classList.remove('hidden');
                popup.classList.add('flex');
                transactionContainer.classList.remove('hidden');
            } else {
                popup.classList.add('hidden');
                popup.classList.remove('flex');
                transactionContainer.classList.add('hidden');
            }
        }

        function closePopup() {
            popup.classList.add('hidden');
            popup.classList.remove('flex');
        }

        function closePayment() {

            popup.classList.add('hidden');
            popup.classList.remove('flex');
            transactionContainer.classList.add('hidden');
        }
    <script>
// Show/hide fields based on selected document type
document.getElementById('document_type').addEventListener('change', function() {
    const value = this.value;
    const sections = ['baptism', 'marriage', 'death', 'confirmation'];

    sections.forEach(section => {
        const div = document.getElementById(section);
        if(div) {
            div.classList.add('hidden');
            // Remove previous required highlights
            const inputs = div.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.style.borderColor = '';
            });
        }
    });

    if(value === 'Baptismal Certificate') document.getElementById('baptism').classList.remove('hidden');
    if(value === 'Marriage Certificate') document.getElementById('marriage').classList.remove('hidden');
    if(value === 'Death Certificate') document.getElementById('death').classList.remove('hidden');
    if(value === 'Confirmation Certificate') document.getElementById('confirmation').classList.remove('hidden');
});

// Form validation on submit
document.getElementById('addRequestForm').addEventListener('submit', function(e) {
    const docType = document.getElementById('document_type').value;
    let requiredFields = [];

    // Define required fields based on document type
    if(docType === 'Baptismal Certificate') {
        requiredFields = ['name_of_child','date_of_birth','place_of_birth','baptism_schedule','name_of_father','name_of_mother','gmail'];
    } else if(docType === 'Marriage Certificate') {
        requiredFields = ['bride_name','birthdate_bride','name_of_groom','birthdate_groom'];
    } else if(docType === 'Death Certificate') {
        requiredFields = ['first_name_death','middle_name_death','last_name_death','date_of_birth_death','date_of_death','file_death'];
    } else if(docType === 'Confirmation Certificate') {
        requiredFields = ['confirmation_first_name','confirmation_middle_name','confirmation_last_name','confirmation_date_of_birth','confirmation_date_of_confirmation'];
    }

    let valid = true;

    requiredFields.forEach(name => {
        const field = document.getElementsByName(name)[0];
        if(field) {
            // Check if field is empty
            if(!field.value) {
                field.style.borderColor = 'red';
                // Add Required Label if not exists
                if(!field.nextElementSibling || !field.nextElementSibling.classList.contains('required-label')) {
                    const label = document.createElement('span');
                    label.textContent = 'Required';
                    label.className = 'required-label text-red-600 text-sm ms-1';
                    field.parentNode.insertBefore(label, field.nextSibling);
                }
                valid = false;
            } else {
                field.style.borderColor = '';
                // Remove existing required label
                if(field.nextElementSibling && field.nextElementSibling.classList.contains('required-label')) {
                    field.nextElementSibling.remove();
                }
            }
        }
    });

    if(!valid) {
        e.preventDefault();
        alert('Please complete all required fields.');
    }
});
</script>

    <script>
function handleDocumentType(requestId) {
    const select = document.getElementById(`document_type_${requestId}`);

    const sections = {
        baptism: document.getElementById(`baptism_${requestId}`),
        marriage: document.getElementById(`marriage_${requestId}`),
        death: document.getElementById(`death_${requestId}`),
        confirmation: document.getElementById(`confirmation_${requestId}`)
    };

    // Hide all first
    Object.values(sections).forEach(section => {
        if (section) section.classList.add('hidden');
    });

    // Show based on selected value
    switch (select.value) {
        case 'Baptismal Certificate':
            sections.baptism.classList.remove('hidden');
            break;
        case 'Marriage Certificate':
            sections.marriage.classList.remove('hidden');
            break;
        case 'Death Certificate':
            sections.death.classList.remove('hidden');
            break;
        case 'Confirmation Certificate':
            sections.confirmation.classList.remove('hidden');
            break;
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addRequestForm');
    const docTypeSelect = document.getElementById('document_type');
    const sections = ['baptism', 'marriage', 'death', 'confirmation'];

    function showSection() {
        const docType = docTypeSelect.value;

        sections.forEach(section => {
            const div = document.getElementById(section);
            if(div) div.classList.add('hidden');
        });

        switch(docType) {
            case 'Baptismal Certificate':
                document.getElementById('baptism').classList.remove('hidden');
                break;
            case 'Marriage Certificate':
                document.getElementById('marriage').classList.remove('hidden');
                break;
            case 'Death Certificate':
                document.getElementById('death').classList.remove('hidden');
                break;
            case 'Confirmation Certificate':
                document.getElementById('confirmation').classList.remove('hidden');
                break;
        }
    }

    docTypeSelect.addEventListener('change', showSection);
    showSection(); // initialize on load

    form.addEventListener('submit', function(e) {
        let valid = true;

        // Only check visible inputs
        const inputs = form.querySelectorAll('input[data-required="true"], select[data-required="true"]');
        inputs.forEach(input => {
            if(input.offsetParent !== null) { // visible
                if(!input.value) {
                    input.style.borderColor = 'red';
                    if(!input.nextElementSibling || !input.nextElementSibling.classList.contains('required-label')) {
                        const label = document.createElement('span');
                        label.textContent = 'Required';
                        label.className = 'required-label';
                        input.parentNode.insertBefore(label, input.nextSibling);
                    }
                    valid = false;
                } else {
                    input.style.borderColor = '';
                    if(input.nextElementSibling && input.nextElementSibling.classList.contains('required-label')) {
                        input.nextElementSibling.remove();
                    }
                }
            }
        });

        if(!valid) {
            e.preventDefault();
            alert('Please complete all required fields.');
        }
    });
});
</script>



    <script>
        $(document).ready(function() {
            $('#document_type').change(function() {
                const baptism = $('#baptism');
                const marriage = $('#marriage');
                const death = $('#death');
                const confirmation = $('#confirmation');

                if ($(this).val() === 'Baptismal Certificate') {
                    baptism.removeClass('hidden');
                    marriage.addClass('hidden');
                    death.addClass('hidden');
                    confirmation.addClass('hidden');
                    $('input[required]').attr('required', true);
                } else if ($(this).val() === 'Marriage Certificate') {
                    marriage.removeClass('hidden');
                    baptism.addClass('hidden');
                    death.addClass('hidden');
                    confirmation.addClass('hidden');
                    $('input[required]').attr('required', true);
                } else if ($(this).val() === 'Death Certificate') {
                    death.removeClass('hidden');
                    marriage.addClass('hidden');
                    baptism.addClass('hidden');
                    confirmation.addClass('hidden');
                    $('input[required]').removeAttr('required');
                } else if ($(this).val() === 'Confirmation Certificate') {
                    confirmation.removeClass('hidden');
                    marriage.addClass('hidden');
                    baptism.addClass('hidden');
                    death.addClass('hidden');
                    $('input[required]').removeAttr('required');
                }
            });

            function calculateAge(birthdateInput, ageInput) {
                var birthdate = new Date($(birthdateInput).val());
                var today = new Date();
                var age = today.getFullYear() - birthdate.getFullYear();
                var monthDifference = today.getMonth() - birthdate.getMonth();

                if (monthDifference < 0) {
                    age--;
                } else if (monthDifference === 0) {
                    var dayDifference = today.getDate() - birthdate.getDate();
                    if (dayDifference < 0) {
                        age--;
                    }
                }

                $(ageInput).val(age);
            }

            $('#birthdate_bride').change(function() {
                calculateAge('#birthdate_bride', '#age_bride');
            });

            $('#birthdate_groom').change(function() {
                calculateAge('#birthdate_groom', '#age_groom');
            });

            $('select[name="payment_method"]').change(function() {
                if ($(this).val() === 'GCash') {
                    $('#GCashQR').show();
                } else {
                    $('#GCashQR').hide();
                }
            });

            $('select[name="payment_method"]').change(function() {
                if ($(this).val() === 'Cash') {
                    $('#WalkInQR').show();
                    $('label[for="transaction_id"]').hide();
                    $('input[name="transaction_id"]').hide();
                } else {
                    $('#WalkInQR').hide();
                    $('label[for="transaction_id"]').show();
                    $('input[name="transaction_id"]').show();
                }
            });
        });
    </script>
    <script>
        function updateTotal() {
            const inputElement = document.getElementById('number-copies');
            inputElement.value = inputElement.value.replace(/[^0-9]/g, ''); // Enforce numeric input
            const numberOfCopies = parseInt(inputElement.value) || 0;
            const totalAmount = 100 * numberOfCopies;
            document.getElementById('total-amount').textContent = totalAmount.toFixed(2);
            validatePayment(); // Re-validate button state after total changes
        }

        function validatePayment() {
            const totalAmount = parseFloat(document.getElementById('total-amount').textContent) || 0;
            const paidAmount = parseFloat(document.getElementById('to_pay').value.replace(/[^0-9]/g, '')) || 0;
            const submitButton = document.getElementById('complete-payment-button');

            if (paidAmount === totalAmount && totalAmount > 0) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }
    </script>

</x-app-layout>
