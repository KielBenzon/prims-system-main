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
                        <form action="{{ route('mails') }}" method="GET" class="flex items-center" id="searchForm">
                            <input type="text" placeholder="Search Mail....."
                                class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                                style="background-color: white !important; color: black !important;" />
                            <button type="submit"
                                class="ml-2 bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600">
                                Search
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Mail List</h3>
                        <button class="btn bg-blue-700 text-white hover:bg-blue-800" onclick="addModal.showModal()">
                            Send Mail
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Recipient</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subject</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Priority</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($mails as $mail)
                                    <tr class="cursor-pointer" onclick="viewModal{{ $mail->id }}.showModal()">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ strlen($mail->title) > 5 ? substr($mail->title, 0, 5) . '...' : $mail->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ strlen($mail->recipient) > 5 ? substr($mail->recipient, 0, 5) . '...' : $mail->recipient }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ strlen($mail->subject) > 5 ? substr($mail->subject, 0, 5) . '...' : $mail->subject }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="badge {{ $mail->priority == 'High' ? 'badge-accent' : ($mail->priority == 'Medium' ? 'badge-primary' : 'badge-secondary') }} text-white">
                                                {{ $mail->priority }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="badge {{ $mail->status == 'Undelivered' ? 'badge-error' : 'badge-success' }} text-white">
                                                {{ $mail->status }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $mail->date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="btn bg-blue-700 hover:bg-blue-800 text-white"
                                                onclick="event.stopPropagation(); editModal{{ $mail->id }}.showModal()">
                                                Edit
                                            </button>
                                            <button class="btn bg-red-700 hover:bg-red-800 text-white"
                                                onclick="event.stopPropagation(); destroyModal{{ $mail->id }}.showModal()">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <dialog id="editModal{{ $mail->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg bg-white" style="background-color: white !important;">
                                            <h3 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Edit Mail</h3>
                                            <hr class="my-4">
                                            <form action="{{ route('mail.update', $mail->id) }}" method="POST"
                                                enctype="multipart/form-data" id="editForm">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Sender</label>
                                                    <input type="email" name="sender" placeholder="Enter Sender"
                                                        value="stmichaelthearcanghel@gmail.com"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                                                        style="background-color: white !important; color: black !important;"
                                                        readonly>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Title</label>
                                                    <input type="text" name="title" placeholder="Enter title"
                                                        value="{{ $mail->title }}"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                                                        style="background-color: white !important; color: black !important;"
                                                        required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Recipient</label>
                                                    <input type="email" name="recipient"
                                                        placeholder="Enter recipient email"
                                                        value="{{ $mail->recipient }}"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                                                        style="background-color: white !important; color: black !important;"
                                                        required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Subject</label>
                                                    <textarea name="subject" placeholder="Enter subject" rows="5"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                                                        style="background-color: white !important; color: black !important;"
                                                        required>{{ $mail->subject }}</textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Priority</label>
                                                    <select name="priority"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                                                        style="background-color: white !important; color: black !important;"
                                                        required>
                                                        <option value="" style="color: black;">Select priority</option>
                                                        <option value="Very High" style="color: black;"
                                                            {{ $mail->priority == 'Very High' ? 'selected' : '' }}>Very
                                                            High</option>
                                                        <option value="High" style="color: black;"
                                                            {{ $mail->priority == 'High' ? 'selected' : '' }}>High
                                                        </option>
                                                        <option value="Normal" style="color: black;"
                                                            {{ $mail->priority == 'Normal' ? 'selected' : '' }}>Normal
                                                        </option>
                                                        <option value="Low" style="color: black;"
                                                            {{ $mail->priority == 'Low' ? 'selected' : '' }}>Low
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Status</label>
                                                    <select name="status"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                                                        style="background-color: white !important; color: black !important;"
                                                        required>
                                                        <option value="" style="color: black;">Select status</option>
                                                        <option value="Undelivered" style="color: black;"
                                                            {{ $mail->status == 'Undelivered' ? 'selected' : '' }}>
                                                            Undelivered
                                                        </option>
                                                        <option value="Delivered" style="color: black;"
                                                            {{ $mail->status == 'Delivered' ? 'selected' : '' }}>
                                                            Delivered
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-black font-medium" style="color: black !important;">Date</label>
                                                    <input type="date" name="date" value="{{ $mail->date }}"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                                                        style="background-color: white !important; color: black !important; color-scheme: light;"
                                                        required>
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end">
                                                    <button
                                                        class="btn bg-white text-black border border-black hover:bg-gray-100 me-2"
                                                        type="button"
                                                        onclick="editModal{{ $mail->id }}.close()">Close</button>
                                                    <button class="btn bg-blue-700 hover:bg-blue-800 text-white"
                                                        type="submit">Send</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $mail->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg">
                                            <h3 class="text-lg font-semibold mb-4">Delete Mail</h3>
                                            <p>Are you sure you want to delete this mail entry?</p>
                                            <div class="flex justify-end mt-4">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white me-2"
                                                    type="button"
                                                    onclick="destroyModal{{ $mail->id }}.close()">Cancel</button>
                                                <form action="{{ route('mail.destroy', $mail->id) }}" method="POST"
                                                    id="deleteForm">
                                                    @csrf
                                                    @method('DELETE')
                                                    <hr class="my-4">
                                                    <button class="btn bg-red-700 hover:bg-red-800 text-white"
                                                        type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </dialog>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $mail->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-3xl bg-white" style="background-color: white !important;">
                                            <div class="flex items-center gap-2">
                                                <button class="btn bg-white text-black border border-black hover:bg-gray-100"
                                                    type="button" onclick="viewModal{{ $mail->id }}.close()">
                                                    <i class='bx bx-left-arrow-alt'></i>
                                                </button>
                                                <h3 class="text-lg font-semibold text-black" style="color: black !important;">View Mail</h3>
                                            </div>
                                            <hr class="my-4">
                                            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Title
                                                        <input type="text" name="title"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->title }}" readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Recipient
                                                        <input type="text" name="recipient"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->recipient }}" readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Subject
                                                        <input type="text" name="subject"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->subject }}" readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Priority
                                                        <input type="text" name="priority"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->priority }}" readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Status
                                                        <input type="text" name="status"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->status }}" readonly />
                                                    </label>
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Date
                                                        <input type="text" name="date"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->date }}" readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                                <div class="sm:col-span-3">
                                                    <label class="input input-bordered flex items-center gap-2 bg-white text-black" style="background-color: white !important; color: black !important;">
                                                        Message
                                                        <input type="text" name="message"
                                                            class="grow border-none focus:ring-0 focus:border-none bg-white text-black"
                                                            style="background-color: white !important; color: black !important;"
                                                            value="{{ $mail->message }}" readonly />
                                                    </label>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn bg-white text-black border border-black hover:bg-gray-100"
                                                    type="button"
                                                    onclick="viewModal{{ $mail->id }}.close()">Close</button>
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
    <div class="modal-box rounded-lg shadow-lg bg-white" style="background-color: white !important;">
        <h3 class="text-lg font-bold mb-4 text-black" style="color: black !important;">Add Mail</h3>
        <hr class="my-4">
        <form action="{{ route('mail.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="Delivered"> <!-- Auto Delivered -->

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Sender</label>
                <input type="email" name="sender" value="stmichaelthearcanghel@gmail.com" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                    style="background-color: white !important; color: black !important;" required>
            </div>

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Title</label>
                <input type="text" name="title" placeholder="Enter title" 
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                    style="background-color: white !important; color: black !important;" required>
            </div>

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Recipient</label>
                <input type="email" name="recipient" placeholder="Enter recipient email" 
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                    style="background-color: white !important; color: black !important;" required>
            </div>

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Subject</label>
                <textarea name="subject" placeholder="Enter subject" rows="5" 
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black placeholder-gray-500" 
                    style="background-color: white !important; color: black !important;" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Priority</label>
                <select name="priority" class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                    style="background-color: white !important; color: black !important;" required>
                    <option value="" style="color: black;">Select priority</option>
                    <option value="Very High" style="color: black;">Very High</option>
                    <option value="High" style="color: black;">High</option>
                    <option value="Normal" style="color: black;">Normal</option>
                    <option value="Low" style="color: black;">Low</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-black font-medium" style="color: black !important;">Date</label>
                <input type="date" name="date" 
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-black" 
                    style="background-color: white !important; color: black !important; color-scheme: light;" required>
            </div>

            <hr class="my-4">
            <div class="flex justify-end">
                <button type="button" class="btn bg-white text-black border border-black hover:bg-gray-100 me-2" onclick="addModal.close()">Close</button>
                <button type="submit" class="btn bg-blue-700 hover:bg-blue-800 text-white">Send</button>
            </div>
        </form>
    </div>
</dialog>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.role-link').on('click', function(event) {
                event.preventDefault();

                const email = $(this).data('email');
                const gmailLink =
                    `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}`;
                window.open(gmailLink, '_blank');
            });
        });
    </script>
</x-app-layout>
