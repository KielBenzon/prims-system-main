<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="flex flex-col items-center text-center mb-8">
        <img src="{{ asset('assets/img/logo.png') }}" alt="logo" class="w-20">
        <h1 class="text-3xl font-bold text-gray-800 mt-4">
            St. Michael the Archangel Parish Records
        </h1>
        <p class="text-lg text-gray-600">Information Management System</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="max-w-md mx-auto p-8 rounded-lg">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">
                Email Address
            </label>
            <div class="flex items-center border border-gray-400 rounded-lg overflow-hidden">
                <span class="px-4 text-gray-500 bg-gray-100">
                    <i class='bx bxs-user'></i>
                </span>
                <x-text-input
                    id="email"
                    class="w-full p-3 bg-white text-gray-900 border-0 focus:ring-0 focus:outline-none"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter email address" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
        </div>

        <!-- Password -->
        <div class="mb-2">
            <label for="password" class="block text-gray-700 font-semibold mb-2">
                Password
            </label>
            <div class="flex items-center border border-gray-400 rounded-lg overflow-hidden relative">
                <span class="px-4 text-gray-500 bg-gray-100">
                    <i class='bx bxs-lock-alt'></i>
                </span>
                <x-text-input
                    id="password"
                    class="w-full p-3 pr-12 bg-white text-gray-900 border-0 focus:ring-0 focus:outline-none"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter password" />
                <span class="absolute right-4 text-gray-500 cursor-pointer" id="togglePassword">
                    <i class='bx bx-show' id="togglePasswordIcon"></i>
                </span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm" />
        </div>

        <!-- Password Criteria -->
<div class="mt-2 text-sm text-gray-600" id="passwordCriteria">
    <p class="font-semibold mb-1">Password must contain:</p>
    <ul class="space-y-1">
        <li id="length" class="flex items-center gap-2 text-red-500">
            <span>•</span> At least 8 characters
        </li>
        <li id="uppercase" class="flex items-center gap-2 text-red-500">
            <span>•</span> One uppercase letter
        </li>
        <li id="lowercase" class="flex items-center gap-2 text-red-500">
            <span>•</span> One lowercase letter
        </li>
        <li id="special" class="flex items-center gap-2 text-red-500">
            <span>•</span> One special character (!@#$%^&)
        </li>
    </ul>
</div>


        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="flex justify-end mb-4">
                <a href="{{ route('password.request') }}"
                   class="text-sm text-indigo-600 hover:underline">
                    Forgot password?
                </a>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('register'))
                <a class="text-sm text-indigo-600 hover:underline"
                   href="{{ route('register') }}">
                    {{ __("Don't have an account?") }}
                </a>
            @endif

            <x-primary-button
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <!-- Google Login -->
        <div class="flex flex-col items-center mt-6">
            <hr class="w-full border-t border-gray-300 mb-3">

            <a href="{{ route('google-auth') }}"
               class="w-full flex items-center justify-center gap-2 bg-blue-100 p-3 shadow-sm border rounded-md text-blue-900">
                <img src="{{ asset('assets/img/google.png') }}" class="w-5 h-5">
                Login with Google
            </a>
        </div>
    </form>

    <!-- Password Toggle Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#togglePassword').on('click', function () {
                const passwordInput = $('#password');
                const icon = $('#togglePasswordIcon');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bx-show').addClass('bx-hide');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bx-hide').addClass('bx-show');
                }
            });
        });
    </script>

    <script>
    $('#password').on('keyup', function () {
        const password = $(this).val();

        // Criteria
        const length = password.length >= 8;
        const uppercase = /[A-Z]/.test(password);
        const lowercase = /[a-z]/.test(password);
        const special = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        toggleCriteria('#length', length);
        toggleCriteria('#uppercase', uppercase);
        toggleCriteria('#lowercase', lowercase);
        toggleCriteria('#special', special);
    });

    function toggleCriteria(id, condition) {
        if (condition) {
            $(id).removeClass('text-red-500').addClass('text-green-600');
        } else {
            $(id).removeClass('text-green-600').addClass('text-red-500');
        }
    }
</script>

</x-guest-layout>
