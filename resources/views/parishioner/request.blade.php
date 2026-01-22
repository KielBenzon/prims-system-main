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
<dialog id="viewModal{{ $request->id }}" class="modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl" style="background-color: white !important; color: black !important;">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-bold" style="color: black !important;">Request Details</h2>
                <p class="text-sm" style="color: #4b5563 !important;">View full information of this request</p>
            </div>
            <button class="btn border border-gray-300" 
                style="background-color: white !important; color: black !important;"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm" style="color: black !important;">
            <div><strong>Document Type:</strong> {{ $request->document_type }}</div>
            <div><strong>Requested By:</strong> {{ $request->user->name }}</div>
            <div><strong>Date & Time Requested:</strong> {{ $request->created_at }}</div>
            <div><strong>Approved By:</strong> {{ $request->request_approved->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ $request->status }}</div>
            <div><strong>Paid:</strong> {{ $request->is_paid }}</div>
            <div class="col-span-2">
                <strong>Notes:</strong>
                <p class="mt-1 p-2 rounded" style="background-color: #f3f4f6 !important; color: black !important;">{{ $request->notes ?: 'No notes provided.' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button class="btn border border-gray-300"
                style="background-color: white !important; color: black !important;"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">Close</button>
        </div>
    </div>
</dialog>


<dialog id="editModal{{ $request->id }}" class="modal">
    <div class="modal-box bg-white max-w-4xl" style="background-color: white !important;">
        <h3 class="font-bold text-xl mb-4 text-black" style="color: black !important;">Edit {{ $request->document_type }} Request</h3>

        <form method="POST" action="{{ route('request.update', $request->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Hidden field to preserve document type -->
            <input type="hidden" name="document_type" value="{{ $request->document_type }}">

            @if($request->document_type == 'Baptismal Certificate')
                @php
                    $nameParts = [];
                    if($request->certificate_detail && $request->certificate_detail->name_of_child) {
                        $nameParts = explode(' ', trim($request->certificate_detail->name_of_child));
                    }
                    $firstName = $nameParts[0] ?? '';
                    $lastName = count($nameParts) > 1 ? array_pop($nameParts) : '';
                    $middleName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
                @endphp

                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">First Name of Child:</label>
                            <input type="text" name="first_name_child" 
                                value="{{ old('first_name_child', $firstName) }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Middle Name of Child:</label>
                            <input type="text" name="middle_name_child" 
                                value="{{ old('middle_name_child', $middleName) }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Last Name of Child:</label>
                            <input type="text" name="last_name_child" 
                                value="{{ old('last_name_child', $lastName) }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Birth:</label>
                            <input type="date" name="date_of_birth" 
                                value="{{ old('date_of_birth', $request->certificate_detail->date_of_birth ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Place of Birth:</label>
                            <input type="text" name="place_of_birth" 
                                value="{{ old('place_of_birth', $request->certificate_detail->place_of_birth ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Baptism:</label>
                            <input type="date" name="baptism_schedule" 
                                value="{{ old('baptism_schedule', $request->certificate_detail->baptism_schedule ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important; color-scheme: light;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Name of Father:</label>
                            <input type="text" name="name_of_father" 
                                value="{{ old('name_of_father', $request->certificate_detail->name_of_father ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Name of Mother:</label>
                            <input type="text" name="name_of_mother" 
                                value="{{ old('name_of_mother', $request->certificate_detail->name_of_mother ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                    </div>
                </div>

            @elseif($request->document_type == 'Marriage Certificate')
                @php
                    $brideNameParts = [];
                    $groomNameParts = [];
                    if($request->certificate_detail) {
                        if($request->certificate_detail->bride_name) {
                            $brideNameParts = explode(' ', trim($request->certificate_detail->bride_name));
                        }
                        if($request->certificate_detail->name_of_groom) {
                            $groomNameParts = explode(' ', trim($request->certificate_detail->name_of_groom));
                        }
                    }
                    $brideFirst = $brideNameParts[0] ?? '';
                    $brideLast = count($brideNameParts) > 1 ? array_pop($brideNameParts) : '';
                    $brideMiddle = count($brideNameParts) > 1 ? implode(' ', array_slice($brideNameParts, 1)) : '';
                    
                    $groomFirst = $groomNameParts[0] ?? '';
                    $groomLast = count($groomNameParts) > 1 ? array_pop($groomNameParts) : '';
                    $groomMiddle = count($groomNameParts) > 1 ? implode(' ', array_slice($groomNameParts, 1)) : '';
                @endphp

                <div class="grid grid-cols-1 gap-6">
                    <h3 class="text-lg font-semibold text-black">Bride Information</h3>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">First Name:</label>
                            <input type="text" name="bride_first_name" 
                                value="{{ old('bride_first_name', $brideFirst) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Middle Name:</label>
                            <input type="text" name="bride_middle_name" 
                                value="{{ old('bride_middle_name', $brideMiddle) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Last Name:</label>
                            <input type="text" name="bride_last_name" 
                                value="{{ old('bride_last_name', $brideLast) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Birthdate:</label>
                            <input type="date" name="birthdate_bride" 
                                value="{{ old('birthdate_bride', $request->certificate_detail->birthdate_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Age:</label>
                            <input type="number" name="age_bride" 
                                value="{{ old('age_bride', $request->certificate_detail->age_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Birthplace:</label>
                            <input type="text" name="birthplace_bride" 
                                value="{{ old('birthplace_bride', $request->certificate_detail->birthplace_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Citizenship:</label>
                            <input type="text" name="citizenship_bride" 
                                value="{{ old('citizenship_bride', $request->certificate_detail->citizenship_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Religion:</label>
                            <input type="text" name="religion_bride" 
                                value="{{ old('religion_bride', $request->certificate_detail->religion_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Residence:</label>
                            <input type="text" name="residence_bride" 
                                value="{{ old('residence_bride', $request->certificate_detail->residence_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Civil Status:</label>
                            <input type="text" name="civil_status_bride" 
                                value="{{ old('civil_status_bride', $request->certificate_detail->civil_status_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Father's Name:</label>
                            <input type="text" name="name_of_father_bride" 
                                value="{{ old('name_of_father_bride', $request->certificate_detail->name_of_father_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Mother's Name:</label>
                            <input type="text" name="name_of_mother_bride" 
                                value="{{ old('name_of_mother_bride', $request->certificate_detail->name_of_mother_bride ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h3 class="text-lg font-semibold text-black">Groom Information</h3>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">First Name:</label>
                            <input type="text" name="groom_first_name" 
                                value="{{ old('groom_first_name', $groomFirst) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Middle Name:</label>
                            <input type="text" name="groom_middle_name" 
                                value="{{ old('groom_middle_name', $groomMiddle) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Last Name:</label>
                            <input type="text" name="groom_last_name" 
                                value="{{ old('groom_last_name', $groomLast) }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Birthdate:</label>
                            <input type="date" name="birthdate_groom" 
                                value="{{ old('birthdate_groom', $request->certificate_detail->birthdate_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Age:</label>
                            <input type="number" name="age_groom" 
                                value="{{ old('age_groom', $request->certificate_detail->age_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Birthplace:</label>
                            <input type="text" name="birthplace_groom" 
                                value="{{ old('birthplace_groom', $request->certificate_detail->birthplace_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Citizenship:</label>
                            <input type="text" name="citizenship_groom" 
                                value="{{ old('citizenship_groom', $request->certificate_detail->citizenship_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Religion:</label>
                            <input type="text" name="religion_groom" 
                                value="{{ old('religion_groom', $request->certificate_detail->religion_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Residence:</label>
                            <input type="text" name="residence_groom" 
                                value="{{ old('residence_groom', $request->certificate_detail->residence_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Civil Status:</label>
                            <input type="text" name="civil_status_groom" 
                                value="{{ old('civil_status_groom', $request->certificate_detail->civil_status_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Father's Name:</label>
                            <input type="text" name="name_of_father_groom" 
                                value="{{ old('name_of_father_groom', $request->certificate_detail->name_of_father_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Mother's Name:</label>
                            <input type="text" name="name_of_mother_groom" 
                                value="{{ old('name_of_mother_groom', $request->certificate_detail->name_of_mother_groom ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>
                </div>

            @elseif($request->document_type == 'Death Certificate')
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">First Name of Deceased:</label>
                            <input type="text" name="first_name_death" 
                                value="{{ old('first_name_death', $request->certificate_detail->first_name_death ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Middle Name of Deceased:</label>
                            <input type="text" name="middle_name_death" 
                                value="{{ old('middle_name_death', $request->certificate_detail->middle_name_death ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Last Name of Deceased:</label>
                            <input type="text" name="last_name_death" 
                                value="{{ old('last_name_death', $request->certificate_detail->last_name_death ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Birth:</label>
                            <input type="date" name="date_of_birth_death" 
                                value="{{ old('date_of_birth_death', $request->certificate_detail->date_of_birth_death ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Death:</label>
                            <input type="date" name="date_of_death" 
                                value="{{ old('date_of_death', $request->certificate_detail->date_of_death ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                    </div>

                    <div>
                        <label class="block text-black font-medium mb-2">Death Certificate (Hospital Record):</label>
                        <input type="file" name="file_death" 
                            class="file-input file-input-bordered w-full bg-white text-black">
                        @if($request->certificate_detail && $request->certificate_detail->file_death)
                            <p class="text-sm text-gray-600 mt-1">Current file: 
                                <a href="{{ $request->certificate_detail->file_death }}" target="_blank" class="text-blue-600">View</a>
                            </p>
                        @endif
                    </div>
                </div>

            @elseif($request->document_type == 'Confirmation Certificate')
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">First Name:</label>
                            <input type="text" name="confirmation_first_name" 
                                value="{{ old('confirmation_first_name', $request->certificate_detail->confirmation_first_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Middle Name:</label>
                            <input type="text" name="confirmation_middle_name" 
                                value="{{ old('confirmation_middle_name', $request->certificate_detail->confirmation_middle_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Last Name:</label>
                            <input type="text" name="confirmation_last_name" 
                                value="{{ old('confirmation_last_name', $request->certificate_detail->confirmation_last_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Place of Birth:</label>
                            <input type="text" name="confirmation_place_of_birth" 
                                value="{{ old('confirmation_place_of_birth', $request->certificate_detail->confirmation_place_of_birth ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Baptism:</label>
                            <input type="date" name="confirmation_date_of_baptism" 
                                value="{{ old('confirmation_date_of_baptism', $request->certificate_detail->confirmation_date_of_baptism ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Father's Name:</label>
                            <input type="text" name="confirmation_fathers_name" 
                                value="{{ old('confirmation_fathers_name', $request->certificate_detail->confirmation_fathers_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Mother's Name:</label>
                            <input type="text" name="confirmation_mothers_name" 
                                value="{{ old('confirmation_mothers_name', $request->certificate_detail->confirmation_mothers_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-black font-medium mb-2">Date of Confirmation:</label>
                            <input type="date" name="confirmation_date_of_confirmation" 
                                value="{{ old('confirmation_date_of_confirmation', $request->certificate_detail->confirmation_date_of_confirmation ?? '') }}"
                                class="input input-bordered w-full bg-white text-black" style="color-scheme: light;">
                        </div>
                        <div>
                            <label class="block text-black font-medium mb-2">Sponsor's Name:</label>
                            <input type="text" name="confirmation_sponsors_name" 
                                value="{{ old('confirmation_sponsors_name', $request->certificate_detail->confirmation_sponsors_name ?? '') }}"
                                class="input input-bordered w-full bg-white text-black">
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn bg-gray-200 hover:bg-gray-300 text-black"
                    onclick="document.getElementById('editModal{{ $request->id }}').close()">
                    Close
                </button>
                <button type="submit" class="btn bg-blue-600 hover:bg-blue-700 text-white">
                    Update Request
                </button>
            </div>
        </form>
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
<dialog id="viewModal{{ $request->id }}" class="modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl" style="background-color: white !important; color: black !important;">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-bold" style="color: black !important;">Request Details</h2>
                <p class="text-sm" style="color: #4b5563 !important;">View full information of this request</p>
            </div>
            <button class="btn border border-gray-300" 
                style="background-color: white !important; color: black !important;"
                onclick="document.getElementById('viewModal{{ $request->id }}').close()">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm" style="color: black !important;">
            <div><strong>Document Type:</strong> {{ $request->document_type }}</div>
            <div><strong>Requested By:</strong> {{ $request->user->name }}</div>
            <div><strong>Date & Time Requested:</strong> {{ $request->created_at }}</div>
            <div><strong>Approved By:</strong> {{ $request->request_approved->name ?? 'N/A' }}</div>
            <div><strong>Status:</strong> {{ $request->status }}</div>
            <div><strong>Paid:</strong> {{ $request->is_paid }}</div>
            <div class="col-span-2">
                <strong>Notes:</strong>
                <p class="mt-1 p-2 rounded" style="background-color: #f3f4f6 !important; color: black !important;">{{ $request->notes ?: 'No notes provided.' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button class="btn border border-gray-300"
                style="background-color: white !important; color: black !important;"
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
                                                                @php
                                                                    $amount = $certificate_types->get($request->document_type, 100);
                                                                @endphp
                                                                ₱ {{ number_format($amount, 2) }}
                                                            </dd>

                                                        </dl>
                                                    </div>

                                                    <!-- Hidden input to store the certificate price -->
                                                    <input type="hidden" id="certificate-price-{{ $request->id }}" value="{{ $amount }}">

                                                    <!-- Transaction ID and Number of Copies -->
                                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                                                        <input
                                                            type="text"
                                                            id="number-copies-{{ $request->id }}"
                                                            name="number_copies"
                                                            placeholder="Number of Copies"
                                                            required
                                                            class="input input-bordered w-full"
                                                            oninput="updateTotal{{ $request->id }}()">

                                                            <label class="input input-bordered flex items-center gap-2 text-gray-400">
                                                                Transaction ID:
                                                                    <input
                                                                    type="file" 
                                                                    name="transaction_id" 
                                                                    placeholder="Transaction ID"
                                                                    id="transaction_id-{{ $request->id }}"
                                                                    accept="image/*"
                                                                    class="grow border-none focus:ring-0 focus:border-none"
                                                                    value="{{ $request->payment->transaction_id ?? '' }}"
                                                                    required
                                                                    onchange="validatePayment{{ $request->id }}()"
                                                                    />
                                                            </label>
                                                        <input
                                                            type="text"
                                                            name="to_pay"
                                                            placeholder="Paid Amount"
                                                            id="to_pay-{{ $request->id }}"
                                                            oninput="validatePayment{{ $request->id }}()"
                                                            class="input input-bordered w-full">
                                                    </div>
                                                    <div class="text-base font-medium text-black" style="color: black !important;">
                                                        Total: ₱ <span id="total-amount-{{ $request->id }}">0</span>
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
                                                    id="complete-payment-button-{{ $request->id }}"
                                                    class="btn bg-blue-600 text-white hover:bg-blue-700 border-0"
                                                    type="submit"
                                                    disabled>
                                                    Complete Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>

                                <script>
                                    function updateTotal{{ $request->id }}() {
                                        const inputElement = document.getElementById('number-copies-{{ $request->id }}');
                                        inputElement.value = inputElement.value.replace(/[^0-9]/g, '');
                                        const numberOfCopies = parseInt(inputElement.value) || 0;
                                        const certificatePrice = parseFloat(document.getElementById('certificate-price-{{ $request->id }}').value) || 0;
                                        const totalAmount = certificatePrice * numberOfCopies;
                                        document.getElementById('total-amount-{{ $request->id }}').textContent = totalAmount.toFixed(2);
                                        validatePayment{{ $request->id }}();
                                    }

                                    function validatePayment{{ $request->id }}() {
                                        const totalAmount = parseFloat(document.getElementById('total-amount-{{ $request->id }}').textContent) || 0;
                                        const paidAmount = parseFloat(document.getElementById('to_pay-{{ $request->id }}').value.replace(/[^0-9]/g, '')) || 0;
                                        const submitButton = document.getElementById('complete-payment-button-{{ $request->id }}');
                                        const transactionFile = document.getElementById('transaction_id-{{ $request->id }}');

                                        if (paidAmount === totalAmount && totalAmount > 0 && transactionFile.files.length > 0) {
                                            submitButton.disabled = false;
                                        } else {
                                            submitButton.disabled = true;
                                        }
                                    }
                                </script>

                                <!-- View Modal -->
                                <dialog id="viewModal{{ $request->id }}" class="modal" style="background-color: rgba(0, 0, 0, 0.5);">
                                    <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl" style="background-color: white !important; color: black !important;">
                                        <div class="flex items-center gap-4">
                                            <button class="btn border border-gray-300"
                                                style="background-color: white !important; color: black !important;"
                                                type="button" onclick="viewModal{{ $request->id }}.close()">
                                                <i class='bx bx-left-arrow-alt'></i>
                                            </button>
                                            <div>
                                                <h2 class="text-lg font-bold" style="color: black !important;">{{ $request->document_type }}
                                                    Details</h2>
                                                <p class="text-[12px]" style="color: #4b5563 !important;">Requested by:
                                                    {{ $request->requested_by }}
                                                </p>
                                                <p class="text-[12px]" style="color: #4b5563 !important;">Requested on:
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
                        style="background-color: white !important; color: black !important;">
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
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Middle Name of Child: </label>
                            <input type="text" name="middle_name_child" placeholder=" Middle Name of Child"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Last Name of Child: </label>
                            <input type="text" name="last_name_child" placeholder=" Last Name of Child"
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
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" required 
                                style="background-color: white !important; color: black !important;">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-black font-medium" style="color: black !important;">Name of Mother: </label>
                            <input type="text" name="name_of_mother" placeholder="Name of Mother"
                                class="input input-bordered w-full bg-white text-black border-gray-300 placeholder-gray-500" required 
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
                    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Marriage Certificate Details</h2>
                    <h3 class="text-md font-bold mb-4 text-black" style="color: black !important;">Bride Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name of Bride: </label>
                            <input type="text" name="bride_first_name" placeholder="First Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name of Bride: </label>
                            <input type="text" name="bride_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name of Bride: </label>
                            <input type="text" name="bride_last_name" placeholder="Last Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Birthdate of Bride: </label>
                            <input type="date" id="birthdate_bride" name="birthdate_bride"
                                placeholder="Birthdate of Bride" class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Age of Bride: </label>
                            <input type="number" id="age_bride" name="age_bride" placeholder="Age of Bride"
                                min="18" max="120" class="input input-bordered w-full">
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
                    <h3 class="text-md font-bold mb-4 text-black" style="color: black !important;">Groom Information</h3>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">First Name of Groom: </label>
                            <input type="text" name="groom_first_name" placeholder="First Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Middle Name of Groom: </label>
                            <input type="text" name="groom_middle_name" placeholder="Middle Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Last Name of Groom: </label>
                            <input type="text" name="groom_last_name" placeholder="Last Name"
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
                            <input type="number" id="age_groom" name="age_groom" placeholder="Age of Groom"
                                min="18" max="120" class="input input-bordered w-full">
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

                <div id="death" class="hidden">
    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Death Certificate Details</h2>
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
            <input type="file" name="file_death" class="input input-bordered w-full flex items-center py-2">
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
                    <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Confirmation Certificate Details</h2>
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
                            <label class="block text-gray-700 font-medium">Place of Birth: </label>
                            <input type="text" name="confirmation_place_of_birth" placeholder="Place of Birth"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Baptism: </label>
                            <input type="date" name="confirmation_date_of_baptism" placeholder="Date of Baptism"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Fathers Name: </label>
                            <input type="text" name="confirmation_fathers_name" placeholder="Fathers Name"
                                class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Mothers Name: </label>
                            <input type="text" name="confirmation_mothers_name" placeholder="Mothers Name"
                                class="input input-bordered w-full">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Date of Confirmation: </label>
                            <input type="date" name="confirmation_date_of_confirmation"
                                placeholder="Date of Confirmation" class="input input-bordered w-full">
                        </div>
                    </div>
                    <div class="mb-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium">Confirmation Sponsors Name: </label>
                            <input type="text" name="confirmation_sponsors_name" placeholder="Confirmation Sponsors Name"
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
// Show/hide fields based on selected document type
document.getElementById('document_type').addEventListener('change', function() {
    const value = this.value;
    const sections = ['baptism', 'marriage', 'death', 'confirmation'];

    sections.forEach(section => {
        const div = document.getElementById(section);
        if(div) {
            div.classList.add('hidden');
            // Remove required attribute from all fields in hidden sections
            const inputs = div.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.removeAttribute('required');
                input.style.borderColor = '';
                // Remove any existing required labels
                if(input.nextElementSibling && input.nextElementSibling.classList.contains('required-label')) {
                    input.nextElementSibling.remove();
                }
            });
        }
    });

    // Show selected section and add required to ALL input fields (except file uploads)
    if(value === 'Baptismal Certificate') {
        const div = document.getElementById('baptism');
        div.classList.remove('hidden');
        div.querySelectorAll('input:not([type="file"])').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
    if(value === 'Marriage Certificate') {
        const div = document.getElementById('marriage');
        div.classList.remove('hidden');
        div.querySelectorAll('input:not([type="file"])').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
    if(value === 'Death Certificate') {
        const div = document.getElementById('death');
        div.classList.remove('hidden');
        div.querySelectorAll('input').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
    if(value === 'Confirmation Certificate') {
        const div = document.getElementById('confirmation');
        div.classList.remove('hidden');
        div.querySelectorAll('input:not([type="file"])').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
});

// Form validation on submit - just highlight missing fields, no alert
document.getElementById('addRequestForm').addEventListener('submit', function(e) {
    const form = this;
    const docType = form.querySelector('#document_type').value;
    
    // Get the visible section
    let visibleSection = null;
    if(docType === 'Baptismal Certificate') visibleSection = document.getElementById('baptism');
    else if(docType === 'Marriage Certificate') visibleSection = document.getElementById('marriage');
    else if(docType === 'Death Certificate') visibleSection = document.getElementById('death');
    else if(docType === 'Confirmation Certificate') visibleSection = document.getElementById('confirmation');
    
    if(!visibleSection) return;

    let valid = true;
    
    // Check all required fields in the visible section
    visibleSection.querySelectorAll('input[required]').forEach(field => {
        let isEmpty = false;
        
        // Special handling for file inputs
        if(field.type === 'file') {
            isEmpty = !field.files || field.files.length === 0;
        } else {
            isEmpty = !field.value || field.value.trim() === '';
        }
        
        if(isEmpty) {
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
    });

    if(!valid) {
        e.preventDefault();
        // Scroll to first error
        const firstError = visibleSection.querySelector('input[style*="red"]');
        if(firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

    <script>
        // Handle edit modal document type changes
        function handleDocumentType(requestId) {
            const select = document.getElementById(`document_type_${requestId}`);
            const sections = ['baptism', 'marriage', 'death', 'confirmation'];
            
            sections.forEach(section => {
                const el = document.getElementById(`${section}_${requestId}`);
                if(el) el.classList.add('hidden');
            });

            const value = select.value;
            if(value === 'Baptismal Certificate') document.getElementById(`baptism_${requestId}`).classList.remove('hidden');
            if(value === 'Marriage Certificate') document.getElementById(`marriage_${requestId}`).classList.remove('hidden');
            if(value === 'Death Certificate') document.getElementById(`death_${requestId}`).classList.remove('hidden');
            if(value === 'Confirmation Certificate') document.getElementById(`confirmation_${requestId}`).classList.remove('hidden');
        }

        // Initialize when DOM loaded
        $(document).ready(function() {
            // Age calculator function
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
        });
    </script>

</x-app-layout>



