<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden">
                <div class="p-1 text-gray-900">

                    {{-- Success / Error Toasts --}}
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

                    {{-- Search Form --}}
                    <div class="mb-4">
                        <form action="{{ route('approval_request') }}" method="GET" class="flex items-center"
                              id="searchForm">
                            <input type="text" name="search" placeholder="Search Requests..."
                                   class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                                   style="background-color: white !important; color: black !important;" />
                            <button type="submit"
                                    class="ml-2 bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>

                    {{-- Requests Table --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 table-auto">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Document Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Requested By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payment</th>
                    
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date & Time</th>
                </tr>
            </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($requests as $request)
                <tr class="hover:bg-gray-100" >
                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->document_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->status }}</td>
                    {{-- STATUS COLUMN --}}
<td class="px-6 py-4 whitespace-nowrap">

    {{-- 🟢 COMPLETED --}}
    @if($request->status === 'Completed')
        <span class="px-3 py-1 bg-green-600 text-white rounded">
            Completed
        </span>

    {{-- 🟢 PAYMENT VERIFIED (still Received, not Completed yet) --}}
    @elseif(
        $request->status === 'Received' &&
        $request->payment?->payment_status === 'Verified'
    )
        <span class="px-3 py-1 bg-green-400 text-white rounded">
            Received
        </span>

    {{-- 🟡 APPROVED BUT NO PAYMENT --}}
    @elseif(
        $request->status === 'Approved' &&
        !$request->payment
    )
        <span class="px-3 py-1 bg-yellow-500 text-white rounded">
            Waiting Payment
        </span>

    {{-- 🔵 VERIFY PAYMENT (CLICKABLE) --}}
    @elseif(
        $request->status === 'Approved' &&
        $request->payment &&
        $request->payment->payment_status === 'Pending'
    )
        <button
            type="button"
            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
            onclick="document.getElementById('verifyPaymentModal{{ $request->id }}').showModal()">
            Verify Payment
        </button>
    @endif

</td>

<td class="px-6 py-4 whitespace-nowrap">

    {{-- CLICKABLE ONLY WHEN PENDING --}}
    @if($request->status === 'Pending')
        <button
            class="btn bg-green-600 hover:bg-green-700 text-white"
            onclick="document.getElementById('viewModal{{ $request->id }}').showModal()">
            View Request
        </button>
    @else
        <button
            class="btn bg-gray-300 text-gray-500 cursor-not-allowed"
            disabled>
            View Request
        </button>
    @endif

</td>




                    <td class="px-6 py-4 whitespace-nowrap">{{ strlen($request->notes) > 1 ? substr($request->notes, 0, 10) . '...' : $request->notes }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
    {{ \Carbon\Carbon::parse($request->created_at)->setTimezone('Asia/Manila')->format('F d, Y g:i A') }}

</td>



                </tr>

                
                

        


                                    <dialog id="viewModal{{ $request->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 bg-white" style="background-color: white !important;">
                                            <div class="flex items-center">
                                                <button class="btn bg-gray-200 hover:bg-gray-300 text-black me-2 border-0"
                                                    type="button" onclick="viewModal{{ $request->id }}.close()">
                                                    <i class='bx bx-left-arrow-alt'></i>
                                                </button>
                                                <h3 class="text-lg font-bold text-black">Verify Request</h3>
                                            </div>
                                            <hr class="my-4">
                                            @if ($request->document_type == 'Baptismal Certificate')
                                                <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Baptismal Certificate Details</h2>
                                                <div class="flex flex-col gap-4">
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="certificate_type"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Certificate
                                                                Type</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="certificate_type"
                                                                    class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300"
                                                                    style="background-color: white !important; color: black !important;"
                                                                    value="{{ $request->certificate_detail->certificate_type }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_child"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Name
                                                                of Child</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_child"
                                                                    class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300"
                                                                    style="background-color: white !important; color: black !important;"
                                                                    value="{{ $request->certificate_detail->name_of_child }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="date_of_birth"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Date
                                                                of Birth</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="date_of_birth"
                                                                    class=\"input input-bordered w-full max-w-xs bg-white text-black border-gray-300\"
                                                                    style=\"background-color: white !important; color: black !important;\"
                                                                    value="{{ $request->certificate_detail->date_of_birth }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="place_of_birth"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Place
                                                                of Birth</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="place_of_birth"
                                                                    class=\"input input-bordered w-full max-w-xs bg-white text-black border-gray-300\"
                                                                    style=\"background-color: white !important; color: black !important;\"
                                                                    value="{{ $request->certificate_detail->place_of_birth }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="baptism_schedule"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Date
                                                                of Baptism</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="baptism_schedule"
                                                                    class=\"input input-bordered w-full max-w-xs bg-white text-black border-gray-300\"
                                                                    style=\"background-color: white !important; color: black !important;\"
                                                                    value="{{ $request->certificate_detail->baptism_schedule }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_father"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Name
                                                                of Father</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_father"
                                                                    class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300"
                                                                    style="background-color: white !important; color: black !important;"
                                                                    value="{{ $request->certificate_detail->name_of_father }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_mother"
                                                                class="block text-sm/6 font-medium text-black" style="color: black !important;">Name
                                                                of Mother</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_mother"
                                                                    class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300"
                                                                    style="background-color: white !important; color: black !important;"
                                                                    value="{{ $request->certificate_detail->name_of_mother }}"
                                                                    readonly />
                                                            </div>
                                                            </div>
                                                            <div class="sm:col-span-3">
    <label for="gmail" class="block text-sm/6 font-medium text-gray-900">Gmail</label>
    <div class="mt-2">
        <input type="email" 
               name="gmail" 
               id="gmail"
               class="input input-bordered w-full max-w-xs" 
               value="{{ $request->user->email }}" 
               readonly />
    </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($request->document_type == 'Marriage Certificate' && isset($request->certificate_detail) && property_exists($request->certificate_detail, 'bride_name'))
                                                <h2 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Marriage Certificate Details</h2>
                                                <div class="flex flex-col gap-4">
                                                    <h3 class="text-md font-bold mb-4 text-black" style="color: black !important;">Bride Information</h3>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="bride_name"
                                                                class="block text-sm/6 font-medium text-gray-900">Bride
                                                                Name</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="bride_name"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->bride_name }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="age_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Age
                                                                of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="age_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->age_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="birthdate_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Birthdate
                                                                of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="birthdate_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->birthdate_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="birthplace_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Birthplace
                                                                of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="birthplace_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->birthplace_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="religion_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Religion
                                                                of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="religion_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->religion_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="residence_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Residence
                                                                of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="residence_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->residence_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="civil_status_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Civil
                                                                Status of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="civil_status_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->civil_status_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_father_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Name
                                                                of Father of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_father_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_father_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_mother_bride"
                                                                class="block text-sm/6 font-medium text-gray-900">Name
                                                                of Mother of Bride</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_mother_bride"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_mother_bride }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr class="my-4">
                                                    <h3 class="text-md font-bold mb-4">Groom Information</h3>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Groom
                                                                Name</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="age_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Age
                                                                of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="age_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->age_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="birthdate_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Birthdate
                                                                of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="birthdate_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->birthdate_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="birthplace_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Birthplace
                                                                of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="birthplace_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->birthplace_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="citizenship_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Citizenship
                                                                of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="citizenship_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->citizenship_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="religion_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Religion
                                                                of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="religion_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->religion_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="civil_status_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Civil
                                                                Status of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="civil_status_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->civil_status_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_father_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Name
                                                                of Father of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_father_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_father_groom }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_mother_groom"
                                                                class="block text-sm/6 font-medium text-gray-900">Name
                                                                of Mother of Groom</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_mother_groom"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_mother_groom }}"
                                                                    readonly />
                                                            </div>
                                                            </div>
                                                            <div class="sm:col-span-3">
    <label for="gmail" class="block text-sm/6 font-medium text-gray-900">Gmail</label>
    <div class="mt-2">
        <input type="email" 
               name="gmail" 
               id="gmail"
               class="input input-bordered w-full max-w-xs" 
               value="{{ $request->user->email }}" 
               readonly />
    </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($request->document_type == 'Death Certificate' && isset($request->certificate_detail) && property_exists($request->certificate_detail, 'first_name_death'))
                                                <h2 class="text-lg font-bold mb-4">Death Certificate Details</h2>
                                                <div class="flex flex-col gap-4">
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="first_name_burial"
                                                                class="block text-sm/6 font-medium text-gray-900">First
                                                                Name of Deceased</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="first_name_death"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail?->first_name_death }}"

                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="middle_name_burial"
                                                                class="block text-sm/6 font-medium text-gray-900">Middle
                                                                Name of Deceased</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="middle_name_death"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail?->middle_name_death }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="last_name_burial"
                                                                class="block text-sm/6 font-medium text-gray-900">Last
                                                                Name of Deceased</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="last_name_death"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail?->middle_name_death }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="date_of_birth_death"
                                                                class="block text-sm/6 font-medium text-gray-900">Date
                                                                of Birth of Deceased</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="date_of_birth_death"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail?->date_of_birth_death }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="date_of_death"
                                                                class="block text-sm/6 font-medium text-gray-900">Date
                                                                of Death</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="date_of_death"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail?->date_of_death }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
    <label for="gmail" class="block text-sm/6 font-medium text-gray-900">Gmail</label>
    <div class="mt-2">
        <input type="email" 
               name="gmail" 
               id="gmail"
               class="input input-bordered w-full max-w-xs" 
               value="{{ $request->user->email }}" 
               readonly />
    </div>
    </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="file_death"
                                                                class="block text-sm/6 font-medium text-gray-900">Death Certificate (Hospital Record)</label>
                                                            <div class="mt-2">
                                                                @if($request->certificate_detail?->file_death)
                                                                    <img src="{{ $request->certificate_detail->file_death }}" 
                                                                         alt="Death Certificate" 
                                                                         class="max-w-xs rounded border border-gray-300"
                                                                         style="max-height: 400px; object-fit: contain;">
                                                                @else
                                                                    <p class="text-gray-500">No file uploaded</p>
                                                                @endif
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($request->document_type == 'Confirmation Certificate' && isset($request->certificate_detail))
                                                <h2 class="text-lg font-bold mb-4">Confirmation Certificate Details
                                                </h2>
                                                <div class="flex flex-col gap-4">
                                                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="name_of_confirmand"
                                                                class="block text-sm/6 font-medium text-gray-900">Name of Confirmand</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="name_of_confirmand"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->name_of_confirmand ?? '' }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="date_of_birth_confirmand"
                                                                class="block text-sm/6 font-medium text-gray-900">Date of Birth</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="date_of_birth_confirmand"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->date_of_birth_confirmand ?? '' }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="date_of_confirmation"
                                                                class="block text-sm/6 font-medium text-gray-900">Date of Confirmation</label>
                                                            <div class="mt-2">
                                                                <input type="text" name="date_of_confirmation"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->certificate_detail->date_of_confirmation ?? '' }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label for="file_confirmation"
                                                                class="block text-sm/6 font-medium text-gray-900">Confirmation Document</label>
                                                            <div class="mt-2">
                                                                @if($request->certificate_detail->file_confirmation ?? null)
                                                                    <img src="{{ $request->certificate_detail->file_confirmation }}" 
                                                                         alt="Confirmation Certificate" 
                                                                         class="max-w-xs rounded border border-gray-300"
                                                                         style="max-height: 400px; object-fit: contain;">
                                                                @else
                                                                    <p class="text-gray-500">No file uploaded</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                        <div class="sm:col-span-3">
                                                            <label for="gmail"
                                                                class="block text-sm/6 font-medium text-gray-900">Gmail</label>
                                                            <div class="mt-2">
                                                                <input type="email"
                                                                    name="gmail"
                                                                    id="gmail"
                                                                    class="input input-bordered w-full max-w-xs"
                                                                    value="{{ $request->user->email }}"
                                                                    readonly />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <hr class="my-4">
                                            <h2 class="hidden">Payment Information</h2>
                                            <div class="mt-4 grid grid-cols-1 gap-y-6">
                                                <!-- Amount Field -->
                                                <div>
                                                    <label for="amount" class="hidden">Amount</label>
                                                    <div class="hidden">
                                                        <input type="text" name="amount"
                                                            class="input input-bordered w-full"
                                                            value="{{ $request->payment->amount ?? '' }}" readonly />
                                                    </div>
                                                </div>

                                                <!-- Transaction ID Field -->
                                                <div>
                                                    <label for="transaction_id" class="hidden">
                                                        Transaction ID
                                                    </label>
                                                    <div class="hidden">
                                                        @if(!empty($request->payment->transaction_id))
                                                            <img src="{{ str_starts_with($request->payment->transaction_id, 'http') ? $request->payment->transaction_id : asset('assets/payment/' . $request->payment->transaction_id) }}" 
                                                                alt="Transaction Image" 
                                                                class="w-full max-w-lg rounded-lg border"> <!-- Ensures image is responsive -->
                                                        @else
                                                            <p class="text-gray-500">No transaction image uploaded</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4">
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="hidden">
                                                    <label for="payment_method"
                                                        class="hidden">Payment
                                                        Method</label>
                                                    <div class="mt-2">
                                                        <input type="text" name="hidden"
                                                            class="input input-bordered w-full max-w-xs"
                                                            value="{{ $request->payment->payment_method ?? '' }}"
                                                            readonly />
                                                    </div>
                                                    </div>
                                                    </div>
                                                
    
        <p class="mt-4 font-semibold">
            Are you sure you want to approve this request?
        </p>

        <div class="flex justify-end mt-6 space-x-2">
            <!-- DECLINE -->
            <button class="btn bg-red-600 text-white hover:bg-red-700 border-0"
                onclick="
                    document.getElementById('declineModal{{ $request->id }}').showModal();
                    document.getElementById('verifyModal{{ $request->id }}').close();
                ">
                Declined
            </button>

            <!-- CANCEL -->
<button class="btn bg-gray-200 hover:bg-gray-300 text-black border-0"
    onclick="document.getElementById('viewModal{{ $request->id }}').close()">
    Cancel
</button>

            <!-- APPROVE -->
            <form action="{{ route('approve_request', $request->id) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="status" value="Approved">
                <input type="hidden" name="approved_by" value="{{ Auth::id() }}">
<input type="hidden" name="requested_by" value="{{ $request->user->id }}">

                <input type="hidden" name="document_type"
       value="{{ $request->certificate_detail->document_type ?? '' }}">

                <input type="hidden" name="number_of_copies"
                       value="{{ $request->certificate_detail->number_of_copies ?? 1 }}">
                <input type="hidden" name="notes" value="">

                <button type="submit"
                        class="btn bg-green-600 text-white hover:bg-green-700 border-0">
                    Yes, Approve
                </button>
            </form>
        </div>
    </div>
</dialog>
<dialog id="declineModal{{ $request->id }}" class="modal">
    <div class="modal-box bg-white" style="background-color: white !important;">
        <h3 class="font-bold text-lg text-red-600">Decline Request</h3>
        <p class="mt-2 text-sm text-black" style="color: black !important;">Please provide the reason for declining:</p>

        <form action="{{ route('approve_request', $request->id) }}" method="POST" class="mt-4">
    @csrf
    @method('PUT')

    <input type="hidden" name="status" value="Declined">
<input type="hidden" name="approved_by" value="{{ Auth::id() }}">
<input type="hidden" name="requested_by" value="{{ $request->user->id }}">
<input type="hidden" name="document_type"
       value="{{ $request->certificate_detail->document_type ?? '' }}">

    <textarea name="notes"
              class="textarea textarea-bordered w-full bg-white text-black border-gray-300"
              style="background-color: white !important; color: black !important;"
              placeholder="Enter decline notes..."
              required></textarea>

    <div class="flex justify-end mt-4 space-x-2">
        <button type="button" class="btn bg-gray-200 hover:bg-gray-300 text-black"
            onclick="document.getElementById('declineModal{{ $request->id }}').close()">
            Cancel
        </button>

        <button type="submit"
                class="btn bg-red-600 text-white hover:bg-red-700">
            Confirm Decline
        </button>
    </div>
</form>

    </div>

                                           
                                        </div>
                                    </dialog>

                                    
                                    <dialog id="verifyPaymentModal{{ $request->id }}" class="modal">
    <div class="modal-box max-w-xl bg-white">

        <h3 class="font-bold text-lg mb-2 text-black">Verify Payment</h3>
        <p class="text-sm text-gray-600">
            Review the request details and submitted payment proof.
        </p>

        {{-- PAYMENT DETAILS --}}
        <div class="mt-4 space-y-4">

            {{-- Amount --}}
            <div>
                <label class="font-semibold">Amount</label>
                <input type="text"
                       class="input input-bordered w-full"
                       value="{{ $request->payment->amount ?? 'N/A' }}"
                       readonly>
            </div>

            {{-- Payment Method --}}
            <div>
                <label class="font-semibold">Payment Method</label>
                <input type="text"
                       class="input input-bordered w-full"
                       value="{{ $request->payment->payment_method ?? 'N/A' }}"
                       readonly>
            </div>
            <div>
    <label class="font-semibold">Number of Copies Requested</label>
    <input type="text"
           class="input input-bordered w-full"
           value="{{ $request->payment ? ($request->payment->amount / 100).' copies' : 'Pending' }}"
           readonly>
</div>

            {{-- Transaction / Receipt --}}
            <div>
                <label class="font-semibold">Payment Proof</label>

                @if(!empty($request->payment->transaction_id))
                    <img src="{{ str_starts_with($request->payment->transaction_id, 'http') ? $request->payment->transaction_id : asset('assets/payment/' . $request->payment->transaction_id) }}"
                         alt="Transaction Image"
                         class="w-full max-h-64 object-contain rounded border mt-2">
                @else
                    <p class="text-red-600 font-semibold mt-2">
                        No payment proof uploaded.
                    </p>
                @endif
            </div>

        </div>

        <p class="mt-6 font-semibold text-gray-700">
            Confirm payment verification?
        </p>

        {{-- ACTION BUTTONS --}}
        <div class="flex justify-end mt-4 gap-2">

            {{-- VERIFY PAYMENT --}}
            <form action="{{ route('verify.payment', $request->id) }}" method="POST">
                @csrf
                @method('PUT')

                <button type="submit"
                        class="btn bg-blue-600 text-white hover:bg-blue-700 border-0"
                        @if(
                            !$request->payment ||
                            $request->payment->payment_status !== 'Pending'
                        ) disabled @endif>
                    Verify Payment
                </button>
            </form>

            {{-- NO: Open decline notes --}}
            <button class="btn bg-red-600 text-white hover:bg-red-700 border-0"
                    onclick="document.getElementById('declineNotesModal{{ $request->id }}').showModal();
                             document.getElementById('verifyPaymentModal{{ $request->id }}').close();">
                Decline
            </button>

            <button class="btn bg-gray-200 hover:bg-gray-300 text-black border-0" onclick="document.getElementById('verifyPaymentModal{{ $request->id }}').close()">
                Cancel
            </button>
        </div>
    </div>
</dialog>

<dialog id="declineNotesModal{{ $request->id }}" class="modal">
    <div class="modal-box max-w-xl">
        <h3 class="font-bold text-lg">Decline Request</h3>
        <p>Please provide notes for declining:</p>

        <form action="{{ route('approval_request.update', $request->id) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="status" value="Declined">
            <input type="hidden" name="is_paid" value="1">
            <input type="hidden" name="approved_by" value="{{ Auth::user()->name }}">
            <input type="hidden" name="requested_by" value="{{ $request->user->name }}">
            <input type="hidden" name="document_type" value="{{ $request->certificate_detail->document_type ?? 'N/A' }}">
            <input type="hidden" name="number_of_copies" value="{{ $request->certificate_detail->number_of_copies ?? 1 }}">

            <textarea name="notes" class="w-full border p-2 rounded" placeholder="Enter decline reason..." required></textarea>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="submit" class="btn bg-red-600 text-white hover:bg-red-700">
                    Submit
                </button>
                <button type="button" class="btn"
                        onclick="document.getElementById('declineNotesModal{{ $request->id }}').close()">
                    Cancel
                </button>
            </div>
        </form>
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
</x-app-layout>
