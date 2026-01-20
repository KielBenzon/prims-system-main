<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="relative py-12">
        <!-- Overlay Container -->
        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center z-10">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center">
                <h3 class="text-lg font-bold">Overlay Container</h3>
                <p class="text-gray-700 mt-2">This is the overlay content that appears on top of the child component.</p>
                <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Dismiss
                </button>
            </div>
        </div>

        <!-- Child Content -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
