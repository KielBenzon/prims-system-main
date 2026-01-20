<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="w-auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('St. Michael the Archangel Parish Church') }}</title>
    <link rel="icon" href="{{ asset('assets/img/logo.png') }}" type="image/png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Global input styling for auth pages */
        input, textarea, select {
            background-color: white !important;
            color: black !important;
            border: 1px solid #d1d5db !important;
        }
        
        input::placeholder, textarea::placeholder {
            color: #6b7280 !important;
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100 p-3 bg-cover no-repeat"
    style="background-image: url('{{ asset('assets/img/church.jpg') }}');">
    <div class="min-h-screen flex flex-col justify-center items-center">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <script>
        if ($('#success').length > 0) {
            setTimeout(() => {
                $('#success').remove();
            }, 3000);
        }

        if ($('#error').length > 0) {
            setTimeout(() => {
                $('#error').remove();
            }, 3000);
        }
    </script>
</body>

</html>
