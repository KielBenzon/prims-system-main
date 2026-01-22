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
                        <form action="{{ route('donations') }}" method="GET" class="flex items-center"
                            id="searchForm">
                            <input type="text" placeholder="Search Donation....."
                                class="input input-bordered w-full max-w-xs bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                                style="background-color: white !important; color: black !important;" />
                            <button type="submit"
                                class="ml-2 bg-green-500 text-white rounded-md px-4 py-3 hover:bg-green-600">
                                Search
                            </button>
                        </form>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Donations List</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Donor Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date and Time</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 p-3">
                                @foreach ($donations as $donation)
                                    <tr class="cursor-pointer" onclick="viewModal{{ $donation->id }}.showModal()">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->donor_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
    {{ \Carbon\Carbon::parse($donation->created_at)->setTimezone('Asia/Manila')->format('F d, Y g:i A') }}

</td>

                                        <td class="px-6 py-4 whitespace-nowrap">{{ $donation->status }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">

                                        <!-- <form action="{{ route('donation.updateStatus', $donation->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Received">

                                            <button type="submit" class="hover:bg-green-100 p-3 rounded-md">
                                                <img src="{{ asset('assets/img/save.svg') }}" class="w-[22px] h-[22px]">
                                            </button>
                                        </form> -->
                                        

                                            @if($donation->status !== 'Received')
                                            <button title="Update Status"
    class="hover:bg-green-100 p-3 rounded-md"
    onclick="event.stopPropagation(); updateStatus{{ $donation->id }}.showModal()">
    <img src="{{ asset('assets/img/save.svg') }}" class="w-[22px] h-[22px]">
</button>
                                            @else
                                            <button title="Already Received" disabled
    class="opacity-50 cursor-not-allowed p-3 rounded-md">
    <img src="{{ asset('assets/img/save.svg') }}" class="w-[22px] h-[22px]">
</button>
                                            @endif

<button title="View Donation"
    class="hover:bg-blue-100 p-3 rounded-md"
    onclick="event.stopPropagation(); editModal{{ $donation->id }}.showModal()">
    <img src="{{ asset('assets\img\eye.jpg') }}" class="w-[20px] h-[20px]">
</button>
</td>
                                    </tr>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $donation->id }}" class="modal">
                                        <div class="modal-backdrop"></div>
                                        <div class="modal-box max-w-3xl rounded-xl shadow-2xl bg-white p-0 overflow-hidden">
                                            <!-- Header -->
                                            <div class="bg-blue-600 text-white px-6 py-4">
                                                <h3 class="text-2xl font-bold">Donation Details</h3>
                                            </div>
                                            
                                            <!-- Content -->
                                            <div class="p-6">
                                                <div class="grid grid-cols-2 gap-6 mb-6">
                                                    <div class="bg-gray-50 p-4 rounded-lg">
                                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Donor Name</label>
                                                        <p class="text-xl font-semibold text-gray-900 mt-1">{{ $donation->donor_name }}</p>
                                                    </div>
                                                    
                                                    <div class="bg-gray-50 p-4 rounded-lg">
                                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                                                        <p class="mt-2">
                                                            <span class="px-4 py-1.5 rounded-full text-sm font-semibold {{ $donation->status === 'Received' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                {{ $donation->status }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="bg-gray-50 p-4 rounded-lg">
                                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</label>
                                                        <p class="text-lg text-gray-900 mt-1">{{ $donation->donor_email }}</p>
                                                    </div>
                                                    
                                                    <div class="bg-gray-50 p-4 rounded-lg">
                                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</label>
                                                        <p class="text-lg text-gray-900 mt-1">{{ $donation->donor_phone }}</p>
                                                    </div>
                                                    
                                                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border-2 border-green-200">
                                                        <label class="text-xs font-semibold text-green-700 uppercase tracking-wide">Amount</label>
                                                        <p class="text-3xl font-bold text-green-700 mt-1">₱{{ number_format($donation->amount, 2) }}</p>
                                                    </div>
                                                    
                                                    <div class="bg-gray-50 p-4 rounded-lg">
                                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</label>
                                                        <p class="text-lg text-gray-900 mt-1">{{ \Carbon\Carbon::parse($donation->donation_date)->format('F d, Y') }}</p>
                                                    </div>
                                                </div>

                                                <!-- Transaction Proof -->
                                                <div class="bg-gray-50 p-6 rounded-lg">
                                                    <label class="text-sm font-bold text-gray-700 mb-4 block">Transaction Proof</label>
                                                    @php
                                                        $imageUrl = $donation->transaction_url ?? $donation->transaction_id ?? null;
                                                    @endphp
                                                    @if($imageUrl)
                                                        <div class="bg-white p-2 rounded-lg border-2 border-gray-200">
                                                            <a href="{{ $imageUrl }}" target="_blank" class="block">
                                                                <img src="{{ $imageUrl }}" 
                                                                     alt="Transaction Proof" 
                                                                     class="w-full h-auto rounded-md" 
                                                                     style="max-height: 500px; object-fit: contain;"
                                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-red-50 border-2 border-red-200 rounded-lg p-8 text-center\'><svg class=\'mx-auto h-16 w-16 text-red-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\'/></svg><p class=\'text-red-600 font-semibold mt-3\'>Failed to load image</p><p class=\'text-red-500 text-sm mt-1\'>{{ $imageUrl }}</p></div>';" />
                                                            </a>
                                                            <p class="text-sm text-center text-gray-600 mt-3 font-medium">Click image to view full size in new tab</p>
                                                        </div>
                                                    @else
                                                        <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
                                                            <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            <p class="text-gray-400 mt-4 font-medium">No transaction proof uploaded</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Footer -->
                                            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t">
                                                <button class="px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors"
                                                    type="button"
                                                    onclick="viewModal{{ $donation->id }}.close()">Close</button>
                                            </div>
                                        </div>
                                    </dialog>

                                    <!-- Update status Modal -->
                                    <dialog id="updateStatus{{ $donation->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg bg-white">
                                            <h3 class="text-lg font-bold text-gray-900">Receive Donation?</h3>
                                            <hr class="my-4">
                                            <form action="{{ route('donation.updateStatus', $donation->id) }}"
                                                method="POST" enctype="multipart/form-data" id="updateStatus">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-4">
                                                    <p class="text-gray-700">Are you sure you want to mark this donation as received?</p>
                                                    <input type="hidden" name="status" value="Received">
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end gap-2">
                                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition duration-200"
                                                        type="submit">Receive</button>
                                                    <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded transition duration-200"
                                                        type="button"
                                                        onclick="updateStatus{{ $donation->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- View (Read-Only) Donation Modal -->
<dialog id="editModal{{ $donation->id }}" class="modal">
    <div class="modal-box rounded-lg shadow-lg bg-white">
        <h3 class="text-lg font-bold mb-4 text-gray-900">View Donation</h3>
        <hr class="my-4">
        <form>
            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Donor Name</label>
                <input type="text" value="{{ $donation->donor_name }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Donor Email</label>
                <input type="email" value="{{ $donation->donor_email }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Donor Phone</label>
                <input type="text" value="{{ $donation->donor_phone }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Amount</label>
                <input type="number" value="{{ $donation->amount }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Date</label>
                <input type="text" value="{{ \Carbon\Carbon::parse($donation->donation_date)->format('F d, Y') }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Status</label>
                <input type="text" value="{{ $donation->status }}" readonly
                    class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-gray-100 text-gray-900">
            </div>

            <!-- Transaction File Preview -->
            <div class="mb-4">
                <label class="block text-gray-900 font-medium">Transaction File</label>
                @php
                    $imageUrl = $donation->transaction_url ?? $donation->transaction_id ?? null;
                @endphp
                @if($imageUrl)
                    <div class="mt-2 p-2 border border-gray-300 rounded bg-white">
                        <a href="{{ $imageUrl }}" target="_blank" class="text-blue-600 hover:underline text-sm mb-2 block">
                            Open in new tab →
                        </a>
                        <img src="{{ $imageUrl }}" 
                             alt="Transaction Image" 
                             class="max-w-full h-auto rounded"
                             onerror="console.error('Failed to load image:', '{{ $imageUrl }}'); this.parentElement.innerHTML = '<div class=\'text-red-600 p-4\'><p class=\'font-semibold\'>Failed to load image</p><p class=\'text-sm mt-2 break-all\'>URL: {{ $imageUrl }}</p><p class=\'text-xs mt-2 text-gray-600\'>The file may not exist at this path. Check Supabase Storage.</p></div>';" />
                    </div>
                @else
                    <span class="text-gray-500">No transaction image uploaded</span>
                @endif
            </div>

            <hr class="my-4">
            <div class="flex justify-end">
                <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded transition duration-200" type="button"
                    onclick="editModal{{ $donation->id }}.close()">Close</button>
            </div>
        </form>
    </div>
</dialog>


                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $donation->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg">
                                            <h3 class="text-lg font-semibold mb-4">Delete Donation</h3>
                                            <p>Are you sure you want to delete this donation?</p>
                                            <div class="flex justify-end mt-4">
                                                <form action="{{ route('donation.destroy', $donation->id) }}"
                                                    method="POST" id="deleteForm">
                                                    @csrf
                                                    @method('DELETE')
                                                    <hr class="my-4">
                                                    <button class="btn bg-red-700 hover:bg-red-800 text-white me-2"
                                                        type="submit">Delete</button>
                                                    <button class="btn text-black hover:bg-red-700 hover:text-white"
                                                        type="button"
                                                        onclick="destroyModal{{ $donation->id }}.close()">Close</button>
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
        </div>

        <!-- Add Modal -->
        <dialog id="addModal" class="modal">
            <div class="modal-box rounded-lg shadow-lg">
                <h3 class="text-lg font-bold mb-4">Add Donation</h3>
                <hr class="my-4">
                <form action="{{ route('donation.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Donor Type</label>
                        <select name="donor_type"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                            required>
                            <option value="">Select Donor Type</option>
                            <option value="Individual">Individual</option>
                            <option value="Organization">Organization</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Donor Name</label>
                        <input type="text" name="donor_name" placeholder="Enter donor name"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Donor Email</label>
                        <input type="email" name="donor_email" placeholder="Enter donor email"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Donor Phone</label>
                        <input type="text" name="donor_phone" placeholder="Enter donor phone"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Amount</label>
                        <input type="number" name="amount" placeholder="Enter amount"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Date</label>
                        <input type="date" name="date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Note</label>
                        <input type="text" name="note" placeholder="Enter note"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out">
                    </div>
                    <hr class="my-4">
                    <div class="flex justify-end">
                        <button type="button" onclick="addModal.close()"
                            class="mr-2 text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out">Close</button>
                        <button type="submit"
                            class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">Add</button>
                    </div>
                </form>
            </div>
        </dialog>
</x-app-layout>
