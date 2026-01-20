<x-app-layout>
    <div class="py-12 bg-white h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success & Error Messages --}}
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

            {{-- Filter Form --}}
            <form action="{{ route('transactions.generate') }}" method="POST" class="bg-white p-4 rounded shadow mb-6 max-w-5xl mx-auto">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Type</label>
                        <select name="report_type" class="w-full border rounded-lg p-2">
                            <option value="all" {{ old('report_type') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="donations" {{ old('report_type') == 'donations' ? 'selected' : '' }}>Donations</option>
                            <option value="payments" {{ old('report_type') == 'payments' ? 'selected' : '' }}>Payments</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" 
                            class="w-full border rounded-lg p-2 bg-white text-black border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important; color-scheme: light;">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" 
                            class="w-full border rounded-lg p-2 bg-white text-black border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important; color-scheme: light;">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Parishioner Name (Optional)</label>
                        <input type="text" name="parishioner_name" value="{{ old('parishioner_name') }}" placeholder="Search name..." 
                            class="w-full border rounded-lg p-2 bg-white text-black border-gray-300 placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500" 
                            style="background-color: white !important; color: black !important;">
                    </div>
                </div>

                <div class="text-right mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Generate
                    </button>
                </div>
            </form>

            

        </div>
    </div>
</x-app-layout>
