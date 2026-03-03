<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg shadow-md cursor-pointer" data-type="Baptismal Certificate">
                    <h3 class="text-lg font-semibold text-black">Baptismal Certificates</h3>
                    <p class="text-2xl font-bold">{{ $baptismal }}</p>
                </div>
                <div class="bg-[#f5f5dc] p-4 rounded-lg shadow-md cursor-pointer" data-type="Marriage Certificate">
                    <h3 class="text-lg font-semibold text-black">Marriage Certificates</h3>
                    <p class="text-2xl font-bold">{{ $marriage }}</p>
                </div>
                <div class="bg-gray-500 p-4 rounded-lg shadow-md cursor-pointer" data-type="Death Certificate">
                    <h3 class="text-lg font-semibold text-black">Death Certificates</h3>
                    <p class="text-2xl font-bold">{{ $death }}</p>
                </div>
                <div class="bg-[#c3b091] p-4 rounded-lg shadow-md cursor-pointer" data-type="Confirmation Certificate">
                    <h3 class="text-lg font-semibold text-black">Confirmation Certificates</h3>
                    <p class="text-2xl font-bold">{{ $confirmation }}</p>
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
                            <button class="btn btn-primary" onclick="addModal.showModal()">
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
                                        <div class="modal-box rounded-lg shadow-lg">
                                            <h3 class="text-lg font-bold mb-4">Edit Document</h3>
                                            <hr class="my-4">
                                            <form action="{{ route('documents.update', $document->id) }}" 
                                                method="POST" enctype="multipart/form-data" id="editForm">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="uploaded_by" value="{{ Auth::user()->name }}">
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">Full Name</label>
                                                    <input type="text" name="full_name" placeholder="Enter donor name" value="{{ $document->full_name }}" 
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 
                                                    focus:border-blue-500 p-3 transition duration-150 ease-in-out" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-gray-700 font-medium">File</label>
                                                    <input type="file" name="file" placeholder="Enter note" value="{{ $document->file }}" 
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 
                                                    focus:border-blue-500 p-3 transition duration-150 ease-in-out">
                                                </div>
                                                <hr class="my-4">
                                                <div class="flex justify-end">
                                                    <button class="btn bg-blue-700 hover:bg-blue-800 text-white" type="submit">Update</button>
                                                    <button class="btn text-black hover:bg-red-700 hover:text-white" type="button" 
                                                    onclick="editModal{{ $document->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- Delete Modal -->
                                    <dialog id="destroyModal{{ $document->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg">
                                            <h3 class="text-lg font-bold mb-4">Delete Document</h3>
                                            <p>Are you sure you want to delete this document?</p>
                                            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" id="deleteForm{{ $document->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="flex justify-end mt-4">
                                                    <button class="btn bg-red-700 hover:bg-red-800 text-white" type="submit">Delete</button>
                                                    <button class="btn text-black hover:bg-red-700 hover:text-white" type="button" 
                                                    onclick="destroyModal{{ $document->id }}.close()">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>

                                    <!-- View Modal -->
                                    <dialog id="viewModal{{ $document->id }}" class="modal">
                                        <div class="modal-box rounded-lg shadow-lg w-11/12 max-w-5xl">
                                            <h3 class="text-lg font-bold mb-4">View Document</h3>
                                            <p><strong>Full Name:</strong> {{ $document->full_name }}</p>
                                            <p><strong>Uploaded By:</strong> {{ $document->uploaded_by }}</p>
                                            <p><strong>Uploaded At:</strong> {{ \Carbon\Carbon::parse($document->created_at)
                                            ->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
                                            @php
                                                $documentPath = match($document->document_type) {
                                                    'Baptismal Certificate' => 'Baptismal Certificate',
                                                    'Marriage Certificate' => 'Marriage Certificate',
                                                    'Death Certificate' => 'Death Certificate',
                                                    'Confirmation Certificate' => 'Confirmation Certificate',
                                                    default => 'Unknown',
                                                };
                                            @endphp
                                            <img src="{{ asset('assets/documents/' . $documentPath . '/' . $document->file) }}" 
                                            class="max-w-full max-h-screen mx-auto">
                                            <hr class="my-4">
                                            <div class="flex justify-end">
                                                <button class="btn text-black hover:bg-red-700 hover:text-white" type="button" 
                                                onclick="viewModal{{ $document->id }}.close()">Close</button>
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
                                <td class="px-6 py-4 whitespace-nowrap">{{ strlen($document->file) > 10 ? substr
                                ($document->file, 0, 10) . '...' : $document->file }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($document->created_at)
                                ->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('documents.restore', $document->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn bg-white text-green-700 border border-green-700 hover:bg-green-700
                                         hover:text-white hover:border-green-700" onclick="event.stopPropagation();">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-end">
                    <button class="btn bg-white text-red-700 border border-red-700 hover:bg-red-700 hover:text-white hover:border-red-700 mt-6" 
                    type="button" onclick="restoreModal.close()">Close</button>
                </div>
            </div>
        </dialog>

        <!-- Add Document Modal -->
        <dialog id="addModal" class="modal">
            <div class="modal-box max-w-lg rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold">Add Document</h3>
                <p class="text-sm text-gray-500 mb-3">
                    Upload your document. You will choose the document type after clicking upload.</p>
                    <hr class="mb-5">
                    <form id="addDocumentForm"action="{{ route('documents.store') }}"method="POST"enctype="multipart/form-data">@csrf
                        <input type="hidden" name="uploaded_by" value="{{ Auth::user()->name }}">
                        <input type="hidden" name="document_type" id="document_type">
                        
                        <!-- Full Name -->
                        <div class="mb-4">
                            <label class="block font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text"name="full_name"placeholder="Enter full name"
                            class="w-full border border-gray-300 rounded-md px-4 py-3"required>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="mb-2">
                            <label class="block font-medium text-gray-700 mb-1">File (PDF, Image, or Document)</label>
                            <input type="file"name="file"id="fileInput"accept=".pdf,.jpg,.jpeg,.png,.bmp,.webp,.doc,.docx,.txt,.rtf"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"required>
                        </div>
                        <p class="text-xs text-gray-500 mb-6">Supports PDF, Images, Word, Text files. Max size: 10MB</p>
                        
                        <!-- Buttons -->
                        <div class="flex justify-end gap-3">
                            
                            <!-- OPEN TYPE MODAL -->
                            <button type="button"onclick="openTypeModal()"class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                                Upload Document
                            </button>
                            <button type="button"onclick="addModal.close()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        
        <!-- ================= SELECT TYPE MODAL ================= -->
        <dialog id="typeModal" class="modal">
            <div class="modal-box max-w-md">
                <h3 class="text-lg font-bold mb-4 text-center">Select Document Type</h3>
                <div class="grid grid-cols-2 gap-4">
                    <button class="btn btn-outline"onclick="setType('Baptismal Certificate')">
                        Baptismal Certificate
                    </button>
                    
                <button class="btn btn-outline"onclick="setType('Marriage Certificate')">
                    Marriage Certificate
                </button>
                
                <button class="btn btn-outline"onclick="setType('Death Certificate')">
                    Death Certificate
                </button>
                
                <button class="btn btn-outline"onclick="setType('Confirmation Certificate')">
                    Confirmation Certificate
                </button>
            </div>
            <div class="flex justify-end mt-5">
                <button class="btn"onclick="typeModal.close()">
                    Cancel
                </button>
            </div>
        </div>
    </dialog>
    
    <!-- ================= JAVASCRIPT ================= -->
    <script>
    window.addModal  = document.getElementById("addModal");
    window.typeModal = document.getElementById("typeModal");
    
    /* ===============================
    OPEN SELECT TYPE MODAL
    ================================*/
    function openTypeModal() {
        const fullName  = document.querySelector("input[name='full_name']");
        const fileInput = document.getElementById("fileInput");
        
        if (!fullName.value.trim()) {
            alert("Please enter full name.");
            return;
        }
        
        if (!fileInput.files.length) {
            alert("Please select a file first.");
            return;
        }
        typeModal.showModal();
    }
    
    /* ===============================
    DETECT TYPE FROM FILENAME
    ================================*/
    function detectTypeFromFilename(filename) {
        
        filename = filename.toLowerCase();
        
        if (filename.includes("bapt")) 
        return "Baptismal Certificate";
    
        if (filename.includes("marriage") || filename.includes("kasal")) 
        return "Marriage Certificate";
    
        if (filename.includes("death") || filename.includes("burial")) 
        return "Death Certificate";
    
        if (filename.includes("confirm")) 
        return "Confirmation Certificate";
    
        return null;
    }
    
    /* ===============================
    VALIDATE AND SUBMIT
    ================================*/
    function setType(selectedType) {
        
        const fileInput = document.getElementById("fileInput");
        const filename  = fileInput.files[0].name;
        const detectedType = detectTypeFromFilename(filename);
        
        // ❌ WRONG TYPE
        if (detectedType && detectedType !== selectedType) {
            alert("The uploaded certificate is incorrect.");
            
            // keep modal open 
            typeModal.showModal();
            return;
        }
        
        // ✅ CORRECT TYPE 
        document.getElementById("document_type").value = selectedType;
        typeModal.close();
        document.getElementById("addDocumentForm").submit();
    }
    </script>
    </x-app-layout>
