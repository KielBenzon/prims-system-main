<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg shadow-md cursor-pointer" data-type="Baptismal Certificate">
                    <h3 class="text-lg font-semibold text-black">Baptismal Certificates</h3>
                    <p class="text-2xl font-bold text-black">{{ $baptismal }}</p>
                </div>
                <div class="bg-[#f5f5dc] p-4 rounded-lg shadow-md cursor-pointer" data-type="Marriage Certificate">
                    <h3 class="text-lg font-semibold text-black">Marriage Certificates</h3>
                    <p class="text-2xl font-bold text-black">{{ $marriage }}</p>
                </div>
                <div class="bg-gray-500 p-4 rounded-lg shadow-md cursor-pointer" data-type="Death Certificate">
                    <h3 class="text-lg font-semibold text-black">Death Certificates</h3>
                    <p class="text-2xl font-bold text-black">{{ $death }}</p>
                </div>
                <div class="bg-[#c3b091] p-4 rounded-lg shadow-md cursor-pointer" data-type="Confirmation Certificate">
                    <h3 class="text-lg font-semibold text-black">Confirmation Certificates</h3>
                    <p class="text-2xl font-bold text-black">{{ $confirmation }}</p>
                </div>
            </div>
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
                        <h3 class="text-lg font-semibold">Documents List</h3>
                        <div class="flex space-x-4">
                            <button class="btn bg-blue-700 text-white hover:bg-blue-800" onclick="addModal.showModal()">
                                Add Document
                            </button>
                            <button onclick="restoreModal.showModal()">
                                <img src="{{ asset('assets/img/history-ui-web-svgrepo-com.svg') }}" class="w-[22px] h-[22px]">
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded At</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>    
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($documents as $document)
                                    <tr class="cursor-pointer" data-document-type="{{ $document->document_type }}" onclick="viewModal{{ $document->id }}.showModal()">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $document->document_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $document->full_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ strlen($document->file) > 10 ? substr($document->file, 0, 10) . '...' : $document->file }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $document->uploaded_by }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($document->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button class="btn bg-blue-700 hover:bg-blue-800 text-white" onclick="event.stopPropagation(); editModal{{ $document->id }}.showModal()">
                                                Edit
                                            </button>
                                            <button class="btn bg-red-700 hover:bg-red-800 text-white" onclick="event.stopPropagation(); destroyModal{{ $document->id }}.showModal()">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <dialog id="editModal{{ $document->id }}" class="modal">
                        <div class="modal-box rounded-lg shadow-lg bg-white w-11/12 max-w-5xl">
                            <h3 class="text-lg font-bold mb-4 text-black">Edit Document</h3>
                            <hr class="my-4">
                            <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="uploaded_by" value="{{ Auth::user()->name }}">
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Full Name</label>
                                    <input type="text" name="full_name" placeholder="Enter donor name" value="{{ $document->full_name }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black" required>
                                </div>
                                
                                <!-- Current Document Preview -->
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium mb-2">Current Document</label>
                                    @if(str_contains($document->file, '.pdf'))
                                        <a href="{{ $document->file }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            Open PDF Document
                                        </a>
                                    @else
                                        <img src="{{ $document->file }}" 
                                            alt="Current Document" 
                                            class="max-w-full h-auto rounded-lg border border-gray-300 cursor-pointer hover:shadow-lg transition-shadow" 
                                            style="max-height: 400px;"
                                            onclick="window.open('{{ $document->file }}', '_blank')">
                                    @endif
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium">Upload New File (Optional)</label>
                                    <input type="file" name="file" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black file:text-black file:bg-gray-100 file:border-0 file:px-4 file:py-2 file:rounded file:mr-4">
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current file</p>
                                </div>
                                <hr class="my-4">
                                <div class="flex justify-end gap-2">
                                    <button class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" type="submit">Update</button>
                                    <button class="btn bg-white text-black border border-black hover:bg-gray-100 px-4 py-2 rounded" type="button" onclick="editModal{{ $document->id }}.close()">Close</button>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $document->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg bg-white">
                                            <h3 class="text-lg font-bold mb-4 text-black">Delete Document</h3>
                                            <p class="text-gray-700 mb-4">Are you sure you want to delete this document?</p>
                                            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" id="deleteForm{{ $document->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="flex justify-end gap-2 mt-4">
                                                    <button class="btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded" type="submit">Delete</button>
                                                    <button class="btn bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded" type="button" onclick="destroyModal{{ $document->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $document->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl bg-white">
                                            <h3 class="text-lg font-bold mb-4 text-black">View Document</h3>
                                            <div class="mb-4">
                                                <p class="text-black mb-2"><strong>Document Type:</strong> {{ $document->document_type }}</p>
                                                <p class="text-black mb-2"><strong>Full Name:</strong> {{ $document->full_name }}</p>
                                                <p class="text-black mb-2"><strong>Uploaded By:</strong> {{ $document->uploaded_by }}</p>
                                                <p class="text-black mb-4"><strong>Uploaded At:</strong> {{ \Carbon\Carbon::parse($document->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
                                            </div>
                                            
                                            <!-- Document Display -->
                                            <div class="mb-4">
                                                @if(str_contains($document->file, '.pdf'))
                                                    <iframe src="{{ $document->file }}" class="w-full rounded-lg border border-gray-300" style="height: 600px;"></iframe>
                                                @else
                                                    <img src="{{ $document->file }}" alt="{{ $document->full_name }}" class="max-w-full h-auto mx-auto rounded-lg border border-gray-300" style="max-height: 70vh;">
                                                @endif
                                            </div>
                                            
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn bg-white text-black border border-black hover:bg-gray-100 px-4 py-2 rounded" type="button" onclick="viewModal{{ $document->id }}.close()">Close</button>
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

        <!-- Deleted Documents Modal -->
        <dialog id="restoreModal" class="modal">
            <div class="modal-box rounded-lg shadow-lg extra">
                <table class="min-w-full divide-y divide-white table-auto">
                    <thead class="bg-white-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>    
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($deletedDocuments as $document)
                            <tr class="cursor-pointer" data-document-type="{{ $document->document_type }}">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $document->document_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $document->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ strlen($document->file) > 10 ? substr($document->file, 0, 10) . '...' : $document->file }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($document->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('documents.restore', $document->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn bg-white text-green-700 border border-green-700 hover:bg-green-700 hover:text-white hover:border-green-700" onclick="event.stopPropagation();">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-end">
                    <button class="btn bg-white text-red-700 border border-red-700 hover:bg-red-700 hover:text-white hover:border-red-700 mt-6" type="button" onclick="restoreModal.close()">Close</button>
                </div>
            </div>
        </dialog>

        <!-- Add Document Modal -->
        <dialog id="addModal" class="modal">
            <div class="modal-box rounded-lg shadow-lg bg-white">
                <h3 class="text-lg font-bold mb-4 text-black">Add Document</h3>
                <p class="text-sm text-gray-600 mb-4">Upload your document and select its classification type.</p>
                <hr class="my-4">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="uploaded_by" value="{{ Auth::user()->name }}">

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Full Name</label>
                        <input type="text" name="full_name" placeholder="Enter full name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">Document Type</label>
                        <select name="document_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black" required>
                            <option value="">Select Document Type</option>
                            <option value="Baptismal Certificate">Baptismal Certificate</option>
                            <option value="Marriage Certificate">Marriage Certificate</option>
                            <option value="Death Certificate">Death Certificate</option>
                            <option value="Confirmation Certificate">Confirmation Certificate</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">File (PDF, Image, or Document)</label>
                        <input type="file" name="file" id="file_input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt,.rtf,.bmp,.webp" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-3 transition duration-150 ease-in-out bg-white text-black file:text-black file:bg-gray-100 file:border-0 file:px-4 file:py-2 file:rounded file:mr-4" required>
                        <p class="text-xs text-gray-500 mt-2">Supports: PDF, Images (JPG, PNG, BMP, WebP), Word Documents (DOC, DOCX), Text (TXT, RTF). Max size: 10MB</p>
                    </div>
                    <hr class="my-4">
                    <div class="flex justify-end gap-2">
                        <button class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" type="submit">Upload Document</button>
                        <button class="btn bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded" type="button" onclick="addModal.close()">Close</button>
                    </div>
                </form>
            </div>
        </dialog>

        <style>
            .dataTables_length select { width: 60px; }
            .dataTables_filter { position: relative; }
            .dataTables_filter input { padding-left: 2rem; }
            .dataTables_filter i { position: absolute; color: #666; right: 0.5rem; top: 50%; transform: translateY(-50%); pointer-events: none; }
            .extra { background: white; border: 1px solid black; }
        </style>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                const table = $('table').DataTable({
                    "dom": '<"flex justify-between items-center mb-4"lf>t<"flex justify-between items-center mt-4"ip>',
                    "order": [[4, "desc"]], // Sort by Uploaded At column (index 4) descending
                    "language": {
                        "search": "",
                        "searchPlaceholder": "Search documents...",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": { "previous": "Previous", "next": "Next" }
                    }
                });
                $('.dataTables_filter').prepend("<i class='bx bx-search'></i>");

                $('.grid div').click(function() {
                    const type = $(this).data('type');
                    $('tbody tr').each(function() {
                        if ($(this).data('document-type') === type) $(this).show();
                        else $(this).hide();
                    });
                });

                $('#addModal').on('close.modal', function() { $('#file_input').val(''); });
            });

            document.addEventListener("DOMContentLoaded", function () {
                const filterDivs = document.querySelectorAll("[data-type]");
                const documentTypeInput = document.getElementById("selected_document_type");
                const addModal = document.getElementById("addModal");

                filterDivs.forEach(div => {
                    div.addEventListener("click", function () {
                        const selectedType = this.getAttribute("data-type");
                        documentTypeInput.value = selectedType;
                    });
                });
                document.querySelector("form").addEventListener("submit", function () {
                    if (!documentTypeInput.value) documentTypeInput.value = "Baptismal Certificate";
                });
            });
        </script>
</x-app-layout>
