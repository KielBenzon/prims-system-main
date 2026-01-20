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
                    <div class="mb-4">
                        <form action="{{ route('priests') }}" method="GET" class="flex items-center gap-3" id="searchForm">
                            <input type="text" name="search" placeholder="Search Priest....."
                                class="input input-bordered bg-white border-gray-300 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 w-full max-w-xs" value="{{ request('search') }}" />
                            <button type="submit"
                                class="bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600 transition duration-200">
                                Search
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Priests List</h3>
                        <button class="btn bg-blue-700 text-white hover:bg-blue-800" onclick="addModal.showModal()">
                            Add Priest
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Full Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email Address</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ordination Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($priests as $priest)
                                    <tr class="cursor-pointer" onclick="viewModal{{ $priest->id }}.showModal()">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            REV. FR. {{ $priest->first_name }} {{ $priest->middle_name }}
                                            {{ $priest->last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $priest->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $priest->email_address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $priest->ordination_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="btn bg-blue-700 hover:bg-blue-800 text-white"
                                                onclick="event.stopPropagation(); editModal{{ $priest->id }}.showModal()">
                                                Edit
                                            </button>
                                            <button class="btn bg-red-700 hover:bg-red-800 text-white"
                                                onclick="event.stopPropagation(); destroyModal{{ $priest->id }}.showModal()">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <dialog id="editModal{{ $priest->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl bg-white">
                                            <h3 class="text-lg font-bold mb-4 text-black">Edit Priest</h3>
                                            <hr class="my-4">
                                            <form action="{{ route('priest.update', $priest->id) }}" method="POST"
                                                enctype="multipart/form-data" id="editForm">
                                                @csrf
                                                @method('PUT')
                                                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">First Name</label>
                                                        <input type="text" name="first_name"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="First Name"
                                                            value="{{ $priest->first_name }}" />
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Middle Name</label>
                                                        <input type="text" name="middle_name"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="Middle Name"
                                                            value="{{ $priest->middle_name }}" />
                                                    </div>
                                                </div>
                                                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Last Name</label>
                                                        <input type="text" name="last_name"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="Last Name"
                                                            value="{{ $priest->last_name }}" />
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Title</label>
                                                        <input type="text" name="title"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="Enter Title (e.g., Father, Rev.)"
                                                            value="{{ $priest->title }}" />
                                                    </div>
                                                </div>
                                                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Date of Birth</label>
                                                        <input type="date" name="date_of_birth"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            value="{{ $priest->date_of_birth }}" />
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                                                        <input type="tel" name="phone_number"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="Enter Phone Number"
                                                            value="{{ $priest->phone_number }}" />
                                                    </div>
                                                </div>
                                                <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                                                        <input type="email" name="email_address"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            placeholder="Enter Email Address"
                                                            value="{{ $priest->email_address }}" />
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <label class="block text-gray-700 font-medium mb-2">Ordination Date</label>
                                                        <input type="date" name="ordination_date"
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-black"
                                                            value="{{ $priest->ordination_date }}" />
                                                    </div>
                                                </div>
                                                <div class="mt-4">
                                                    <label class="block text-gray-700 font-medium mb-2">Image</label>
                                                    <input type="file" name="image" accept="image/*"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black file:text-black file:bg-gray-100 file:border-0 file:px-4 file:py-2 file:rounded file:mr-4">
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end gap-2">
                                                    <button class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                                                        type="submit">Update</button>
                                                    <button class="btn bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded"
                                                        type="button"
                                                        onclick="editModal{{ $priest->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $priest->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg bg-white">
                                            <h3 class="text-lg font-bold mb-4 text-black">Delete Priest</h3>
                                            <p class="text-gray-700 mb-4">Are you sure you want to delete this priest?
                                            </p>
                                            <div class="flex justify-end gap-2">
                                                <form action="{{ route('priest.destroy', $priest->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                                                        type="submit">Delete</button>
                                                    <button class="btn bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded"
                                                        type="button"
                                                        onclick="destroyModal{{ $priest->id }}.close()">Close</button>
                                                </form>
                                            </div>
                                        </div>
                                    </dialog>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $priest->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl">
                                            <h3 class="text-lg font-semibold mb-4 font-bold">View Priest</h3>
                                            <hr class="my-4">
                                            <div
                                                class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                                                <img src="{{ asset('assets/priests/' . $priest->image) }}"
                                                    alt="Priest Image" class="w-1/3 rounded-3xl">
                                                <div class="flex flex-col space-y-2 w-full">
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Full
                                                            Name:</label>
                                                        <input type="text" name="full_name"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="REV. FR. {{ $priest->last_name }} {{ $priest->first_name }} {{ $priest->middle_name }}"
                                                            readonly>
                                                    </div>
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Title:</label>
                                                        <input type="text" name="title"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="{{ $priest->title }}" readonly>
                                                    </div>
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Email
                                                            Address:</label>
                                                        <input type="email" name="email_address"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="{{ $priest->email_address }}" readonly>
                                                    </div>
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Ordination
                                                            Date:</label>
                                                        <input type="date" name="ordination_date"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="{{ $priest->ordination_date }}" readonly>
                                                    </div>
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Phone
                                                            Number:</label>
                                                        <input type="tel" name="phone_number"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="{{ $priest->phone_number }}" readonly>
                                                    </div>
                                                    <div class="flex">
                                                        <label class="w-1/3 text-gray-700 font-medium">Date of
                                                            Birth:</label>
                                                        <input type="date" name="date_of_birth"
                                                            class="w-2/3 text-gray-700 border rounded"
                                                            value="{{ $priest->date_of_birth }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                    type="button"
                                                    onclick="viewModal{{ $priest->id }}.close()">Close</button>
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

        <!-- Add Modal -->
        <dialog id="addModal" class="modal">
            <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl bg-white">
                <h3 class="text-lg font-bold mb-4 text-black">Add Priest</h3>
                <hr class="my-4">
                <form action="{{ route('priest.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">First Name</label>
                            <input type="text" name="first_name"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;"
                                placeholder="First Name" />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Middle Name</label>
                            <input type="text" name="middle_name"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;"
                                placeholder="Middle Name" />
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Last Name</label>
                            <input type="text" name="last_name"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;" placeholder="Last Name" />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Title</label>
                            <select name="title"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;">
                                <option value="" disabled selected>Select Title</option>
                                <option value="Rev. Fr.">Rev. Fr.</option>
                                <option value="Msgr.">Msgr.</option>
                                <option value="Rev. Msgr.">Rev. Msgr.</option>
                                <option value="Bp.">Bp.</option>
                                <option value="Archbishop">Archbishop</option>
                                <option value="Cardinal">Cardinal</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Date of Birth</label>
                            <input type="date" name="date_of_birth"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important; color-scheme: light;"
                                placeholder="Date of Birth" />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                            <input type="tel" name="phone_number"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;"
                                placeholder="Enter Phone Number" />
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <input type="email" name="email_address"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important;"
                                placeholder="Enter Email Address" />
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-gray-700 font-medium mb-2">Ordination Date</label>
                            <input type="date" name="ordination_date"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                style="background-color: white !important; color: black !important; color-scheme: light;"
                                placeholder="Enter Ordination Date" />
                        </div>
                    </div>
                    <div class="mt-4">
        <label class="block text-gray-700 font-medium">Profile Image</label>
        <input type="file" name="image" accept="image/*" id="imageInput"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                   focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 bg-white text-black file:text-black file:bg-gray-100 file:border-0 file:px-4 file:py-2 file:rounded file:mr-4" required>

        <img id="preview" class="hidden w-32 h-32 object-cover rounded-full mt-2">
        <p id="error" class="text-red-500 text-sm mt-1 hidden">No face detected in the image.</p>
    </div>

    <div class="flex justify-end gap-2 mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition duration-200">
            Upload
        </button>
        <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded transition duration-200" type="button"
                            onclick="addModal.close()">Close</button>
    </div>
         </div>
</form>
            </div>
        </dialog>
    </div>
    </div>
    

    <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<input type="file" id="imageInput" accept="image/*">
<img id="preview" class="hidden w-32 h-32 mt-2">

<p id="error" class="text-red-500 hidden">No human face detected!</p>

<script>
const imageInput = document.getElementById('imageInput');
const preview = document.getElementById('preview');
const error = document.getElementById('error');

async function detectFace(file) {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    await img.decode(); // wait for image to load

    // Load models (from CDN)
    await faceapi.nets.ssdMobilenetv1.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/');
    
    const detection = await faceapi.detectSingleFace(img);
    return detection != null;
}

imageInput.addEventListener('change', async () => {
    const file = imageInput.files[0];
    if (!file) return;

    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
    
    const hasFace = await detectFace(file);
    if (!hasFace) {
        error.classList.remove('hidden');
        imageInput.value = ''; // clear file input
    } else {
        error.classList.add('hidden');
    }
});
</script>

<style>
/* Override DaisyUI input styling */
.modal-box input, 
.modal-box select {
    background-color: white !important;
    color: black !important;
    border: 1px solid #d1d5db !important;
}

/* Ensure date input calendar buttons are visible */
input[type="date"] {
    color-scheme: light;
    background-color: white !important;
    color: black !important;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    color: black !important;
    opacity: 1 !important;
    background-color: white !important;
    cursor: pointer;
    filter: invert(0) !important;
    width: 16px !important;
    height: 16px !important;
}

/* Fix file input styling */
input[type="file"] {
    background-color: white !important;
    color: black !important;
}

input[type="file"]::-webkit-file-upload-button {
    background-color: #f3f4f6 !important;
    color: black !important;
    border: 0;
    padding: 8px 16px;
    border-radius: 4px;
    margin-right: 16px;
    cursor: pointer;
}

/* Override any dark theme styling */
.modal-box * {
    color-scheme: light;
}
</style>

</x-app-layout>
