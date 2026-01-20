<x-app-layout>
    <div class="py-2 bg-gray-100 h-full mt-4 rounded-2xl">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="p-4 bg-white shadow sm:rounded-lg mb-4">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 bg-white shadow sm:rounded-lg mb-4">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <div class="p-4 bg-white shadow sm:rounded-lg mb-4">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
