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
                            <div class="alert alert-error bg-red-500 text-white">
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Announcements List</h3>
                        <button class="btn btn-primary" onclick="document.getElementById('addModal').showModal()">
                            Add Announcement
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto max-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Content
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assigned Priest
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($announcements as $announcement)
                                    <tr class="cursor-pointer" onclick="viewModal{{ $announcement->id }}.showModal()">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            {{ strlen($announcement->title) > 5 ? substr($announcement->title, 0, 5) . '...' : $announcement->title }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            {{ strlen($announcement->content) > 5 ? substr($announcement->content, 0, 5) . '...' : $announcement->content }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">REV. FR.
                                            {{ $announcement->priest->first_name }}
                                            {{ $announcement->priest->middle_name }}
                                            {{ $announcement->priest->last_name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            {{ $announcement->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <button class="btn bg-green-700 hover:bg-green-800 text-white"
                                                onclick="event.stopPropagation(); editModal{{ $announcement->id }}.showModal()">
                                                Edit
                                            </button>
                                            <button class="btn bg-red-700 hover:bg-red-800 text-white"
                                                onclick="event.stopPropagation(); destroyModal{{ $announcement->id }}.showModal()">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $announcement->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg max-w-2xl">
                                            <h3 class="text-lg font-bold mb-4">View Announcement</h3>
                                            <hr class="my-4">
                                            <form>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Title</label>
                                                    <input type="text" value="{{ $announcement->title }}"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                                                        readonly>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Content</label>
                                                    <textarea
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                                                        readonly>{{ $announcement->content }}</textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Assigned
                                                        Priest</label>
                                                    <select name="assigned_priest"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out text-left"
                                                        required>
                                                        <option value="">Select a Priest</option>
                                                        @foreach ($priests as $priest)
                                                            <option value="{{ $priest->id }}"
                                                                {{ $announcement->assigned_priest == $priest->id ? 'selected' : '' }}>
                                                                REV. FR. {{ $priest->first_name }}
                                                                {{ $priest->middle_name }} {{ $priest->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end">
                                                    <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                        type="button"
                                                        onclick="viewModal{{ $announcement->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Edit Modal -->
                                    <dialog id="editModal{{ $announcement->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg max-w-2xl">
                                            <h3 class="text-lg font-bold mb-4">Edit Announcement</h3>
                                            <hr class="my-4">
                                            <form action="{{ route('announcement.update', $announcement->id) }}"
                                                method="POST" enctype="multipart/form-data" id="editForm">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Title</label>
                                                    <input type="text" name="title" placeholder="Enter title"
                                                        value="{{ $announcement->title }}"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out">
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Content</label>
                                                    <textarea type="text" name="content" placeholder="Enter content"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out">{{ $announcement->content }}</textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Assigned
                                                        Priest</label>
                                                    <select name="assigned_priest"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out text-left"
                                                        required>
                                                        <option value="">Select a Priest</option>
                                                        @foreach ($priests as $priest)
                                                            <option value="{{ $priest->id }}"
                                                                {{ $announcement->assigned_priest == $priest->id ? 'selected' : '' }}>
                                                                REV. FR. {{ $priest->first_name }}
                                                                {{ $priest->middle_name }} {{ $priest->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end">
                                                    <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                        type="button"
                                                        onclick="editModal{{ $announcement->id }}.close()">Close</button>
                                                    <button type="submit"
                                                        class="btn bg-blue-700 hover:bg-blue-800 text-white">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $announcement->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg max-w-2xl">
                                            <h3 class="text-lg font-semibold mb-4">Delete Announcement</h3>
                                            <p>Are you sure you want to delete this announcement?</p>
                                            <div class="flex justify-end mt-4">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                    type="button"
                                                    onclick="destroyModal{{ $announcement->id }}.close()">Cancel</button>
                                                <form action="{{ route('announcement.destroy', $announcement->id) }}"
                                                    method="POST" id="deleteForm">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn bg-red-700 hover:bg-red-800 text-white">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </dialog>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Modal -->
            <dialog id="addModal" class="modal">
                <div class="modal-box rounded-lg shadow-lg max-w-2xl">
                    <h3 class="text-lg font-bold mb-4">Add Announcement</h3>
                    <hr class="my-4">
                    <form action="{{ route('announcement.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium">Title</label>
                            <input type="text" name="title" placeholder="Enter title"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                                required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium">Content</label>
                            <textarea type="text" name="content" placeholder="Enter content"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                                required></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium">Assigned Priest</label>
                            <select name="assigned_priest"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                                required>
                                <option value="">Select a Priest</option>
                                @foreach ($priests as $priest)
                                    <option value="{{ $priest->id }}">REV. FR. {{ $priest->first_name }}
                                        {{ $priest->middle_name }} {{ $priest->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr class="my-4">
                        <div class="flex justify-end">
                            <button class="btn text-black hover:bg-red-700 hover:text-white" type="button"
                                onclick="addModal.close()">Close</button>
                            <button class="btn bg-blue-700 hover:bg-blue-800 text-white" type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </dialog>
        </div>
    </div>
</x-app-layout>