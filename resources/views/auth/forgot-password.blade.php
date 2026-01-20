<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-orange-500 to-orange-300 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-10 text-center">

            <!-- Icon / Image -->
            <img src="{{ asset('assets\img\logo.png') }}"
                 alt="Forgot Password"
                 class="w-28 mx-auto mb-6">

            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Find your Account
            </h2>

            <!-- Description -->
            <p class="text-sm text-gray-500 mb-6">
                Enter your email so that we can send you a password reset link.
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <x-text-input
                        id="email"
                        class="w-full px-4 py-3 rounded-full border border-gray-300 focus:ring-orange-500 focus:border-orange-500"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        placeholder="e.g. username@email.com" />

                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-full transition">
                    Send Email
                </button>
            </form>

            <!-- Back to Login -->
            <a href="{{ route('login') }}"
               class="inline-block mt-6 text-sm text-gray-500 hover:underline">
                ← Back to Login
            </a>
        </div>
    </div>
</x-guest-layout>
