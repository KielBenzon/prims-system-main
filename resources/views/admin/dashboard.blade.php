<x-app-layout>
    <div class="py-2 h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-gray-200 overflow-hidden">
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Payment Request Card -->
                        <a href="{{ route('payment', ['filter' => 'monthly']) }}"
                            class="card bg-blue-100 hover:bg-blue-200 transition duration-200 w-full">
                                <div class="card-body">
                                    <h2 class="card-title text-black">Total Payment as of This Month</h2>
                                    <p id="donation-total" class="text-2xl font-bold">
                                        {{ number_format($monthlyPayment, 2) }}
                                    </p>
                                </div>
                            </a>

                        <!-- Donation Requests Card -->
                        <a href="{{ route('donations', ['filter' => 'monthly']) }}"
                        class="card bg-blue-100 hover:bg-blue-200 transition duration-200 w-full">
                            <div class="card-body">
                                <h2 class="card-title text-black">Total Donation as of This Month</h2>
                                <p id="donation-total" class="text-2xl font-bold">
                                    {{ number_format($monthlyTotal, 2) }}
                                </p>
                            </div>
                        </a>

                        <!-- Approved Requests Card -->
                        <a href="{{ route('approval_request', ['status' => 'Approved']) }}"
                            class="card bg-blue-100 shadow-xl hover:bg-blue-200 transition duration-200">
                            <div class="card-body">
                                <h2 class="card-title text-black">Approved Requests</h2>
                                <p class="text-2xl font-bold">{{ $approved }}</p>
                            </div>
                        </a>

                        <!-- Completed Requests Card for Admin -->
<a href="{{ route('approval_request', ['status' => 'Completed']) }}"
    class="card bg-green-100 hover:bg-green-200 transition duration-200 w-full">
    <div class="card-body">
        <h2 class="card-title text-black">Completed Transactions</h2>
        <p class="text-2xl font-bold">
            {{ $completed }}
        </p>
    </div>
</a>


                        <!-- Decline Requests Card -->
                        <a href="{{ route('approval_request', ['status' => 'Declined']) }}"
                            class="card bg-gray-100 shadow-xl hover:bg-gray-200 transition duration-200">
                            <div class="card-body">
                                <h2 class="card-title text-black">Declined Requests</h2>
                                <p class="text-2xl font-bold">{{ $declined }}</p>
                            </div>
                        </a>

                        <!-- Pending Requests Card -->
                        <a href="{{ route('approval_request', ['status' => 'Pending']) }}"
                            class="card bg-gray-300 shadow-xl hover:bg-gray-400 transition duration-200">
                            <div class="card-body">
                                <h2 class="card-title text-black">Pending Requests</h2>
                                <p class="text-2xl font-bold">{{ $pending }}</p>
                            </div>
                        </a>
                    </div>

                    <dialog id="addBaptismalModal" class="modal">
                        <div class="modal-box w-11/12 max-w-5xl">
                            <h3 class="text-lg font-bold">Add Baptismal Certificate</h3>
                            <hr class="my-4">
                            <form action="{{ route('baptismal.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="document_type" value="Baptismal Certificate">
                                <input type="hidden" name="requested_by" value="{{ Auth::user()->name }}">
                                <h2 class="text-lg font-bold mb-4">Baptismal Certificate Details</h2>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Child</label>
                                    <input type="text" name="name_of_child" id="name_of_child"
                                        placeholder="Name of Child" class="input input-bordered w-full" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Date of Birth</label>
                                    <div class="flex space-x-2">
                                        <select name="day_of_birth" id="day_of_birth" class="input input-bordered w-1/3"
                                            required>
                                            <option value="">Day</option>
                                            @for ($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select name="month_of_birth" id="month_of_birth"
                                            class="input input-bordered w-1/3" required>
                                            <option value="">Month</option>
                                            @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                <option value="{{ $month }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                        <select name="year_of_birth" id="year_of_birth"
                                            class="input input-bordered w-1/3" required>
                                            <option value="">Year</option>
                                            @for ($i = date('Y'); $i >= 1900; $i--)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Place of Birth</label>
                                    <input type="text" name="place_of_birth" id="place_of_birth"
                                        placeholder="Place of Birth" class="input input-bordered w-full" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Date of Baptism</label>
                                    <div class="flex space-x-2">
                                        <select name="baptism_day" id="baptism_day" class="input input-bordered w-1/3"
                                            required>
                                            <option value="">Day</option>
                                            @for ($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select name="baptism_month" id="baptism_month"
                                            class="input input-bordered w-1/3" required>
                                            <option value="">Month</option>
                                            @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                <option value="{{ $month }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                        <select name="baptism_year" id="baptism_year" class="input input-bordered w-1/3"
                                            required>
                                            <option value="">Year</option>
                                            @for ($i = date('Y'); $i >= 1900; $i--)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Father</label>
                                    <input type="text" name="name_of_father" id="name_of_father"
                                        placeholder="Name of Father" class="input input-bordered w-full" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Mother</label>
                                    <input type="text" name="name_of_mother" id="name_of_mother"
                                        placeholder="Name of Mother" class="input input-bordered w-full" required>
                                </div>
                                <hr class="my-4">
                                <div class="modal-action mb-4">
                                    <button class="btn btn-primary text-white" type="submit">Save</button>
                                    <button class="btn text-black hover:bg-red-700 hover:text-white" type="button"
                                        onclick="addBaptismalModal.close()">Close</button>
                                </div>
                            </form>
                        </div>
                    </dialog>

                    <dialog id="addMarriageModal" class="modal">
                        <div class="modal-box w-11/12 max-w-5xl">
                            <h3 class="text-lg font-bold">Add Marriage Certificate</h3>
                            <hr class="my-4">
                            <form action="{{ route('documents.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <h2 class="text-lg font-bold mb-4">Marriage Certificate Details</h2>
                                <!-- Bride Information -->
                                <h3 class="text-lg font-bold mb-4">Bride Information</h3>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Bride Name</label>
                                    <input type="text" name="bride_name" placeholder="Bride Name"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Age Bride</label>
                                    <input type="text" name="age_bride" placeholder="Age Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Birthdate Bride</label>
                                    <input type="date" name="birthdate_bride" placeholder="Birthdate Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Birthplace Bride</label>
                                    <input type="text" name="birthplace_bride" placeholder="Birthplace Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Citizenship Bride</label>
                                    <input type="text" name="citizenship_bride" placeholder="Citizenship Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Religion Bride</label>
                                    <input type="text" name="religion_bride" placeholder="Religion Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Residence Bride</label>
                                    <input type="text" name="residence_bride" placeholder="Residence Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Civil Status Bride</label>
                                    <input type="text" name="civil_status_bride" placeholder="Civil Status Bride"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Father Bride</label>
                                    <input type="text" name="name_of_father_bride"
                                        placeholder="Name of Father Bride" class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Mother Bride</label>
                                    <input type="text" name="name_of_mother_bride"
                                        placeholder="Name of Mother Bride" class="input input-bordered w-full">
                                </div>
                                <!-- Groom Information -->
                                <h3 class="text-lg font-bold mb-4">Groom Information</h3>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Groom</label>
                                    <input type="text" name="name_of_groom" placeholder="Name of Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Age of Groom</label>
                                    <input type="text" name="age_groom" placeholder="Age of Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Date of Birth</label>
                                    <input type="date" name="birthdate_groom" placeholder="Date of Birth"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Place of Birth</label>
                                    <input type="text" name="birthplace_groom" placeholder="Place of Birth"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Citizenship Groom</label>
                                    <input type="text" name="citizenship_groom" placeholder="Citizenship Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Religion Groom</label>
                                    <input type="text" name="religion_groom" placeholder="Religion Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Residence Groom</label>
                                    <input type="text" name="residence_groom" placeholder="Residence Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Civil Status Groom</label>
                                    <input type="text" name="civil_status_groom" placeholder="Civil Status Groom"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Father Groom</label>
                                    <input type="text" name="name_of_father_groom"
                                        placeholder="Name of Father Groom" class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Name of Mother Groom</label>
                                    <input type="text" name="name_of_mother_groom"
                                        placeholder="Name of Mother Groom" class="input input-bordered w-full">
                                </div>
                                <hr class="my-4">
                                <div class="modal-action">
                                    <button class="btn btn-primary text-white" type="submit">Save</button>
                                    <button class="btn text-black hover:bg-red-700 hover:text-white" type="button"
                                        onclick="addMarriageModal.close()">Close</button>
                                </div>
                            </form>
                        </div>
                    </dialog>

                    <dialog id="addDeathModal" class="modal">
                        <div class="modal-box w-11/12 max-w-5xl">
                            <h3 class="text-lg font-bold">Add Death Certificate</h3>
                            <hr class="my-4">
                            <form action="{{ route('documents.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">First Name</label>
                                    <input type="text" name="first_name_death" placeholder="First Name"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Middle Name</label>
                                    <input type="text" name="middle_name_death" placeholder="Middle Name"
                                        class="input input-bordered w-full">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Last Name</label>
                                    <input type="text" name="last_name_death" placeholder="Last Name"
                                        class="input input-bordered w-full">
                                </div>
                                <hr class="my-4">
                                <div class="modal-action">
                                    <button class="btn btn-primary text-white" type="submit">Save</button>
                                    <button class="btn " onclick="addDeathModal.close()">Close</button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
