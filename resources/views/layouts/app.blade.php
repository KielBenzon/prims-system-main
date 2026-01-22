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
    
    <!-- Supabase Realtime Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Global input styling - Override DaisyUI dark theme */
        input, textarea, select {
            background-color: white !important;
            color: black !important;
            border: 1px solid #d1d5db !important;
            color-scheme: light !important;
        }
        
        /* Specific input types */
        input[type="text"], input[type="email"], input[type="password"], 
        input[type="number"], input[type="tel"], input[type="date"], 
        input[type="search"], input[type="file"] {
            background-color: white !important;
            color: black !important;
        }
        
        /* Placeholder styling */
        input::placeholder, textarea::placeholder {
            color: #6b7280 !important;
        }
        
        /* File input styling */
        input[type="file"]::-webkit-file-upload-button {
            background-color: #f3f4f6 !important;
            color: black !important;
        }
        
        /* Date input calendar button */
        input[type="date"]::-webkit-calendar-picker-indicator {
            color: black !important;
            opacity: 1 !important;
            background-color: white !important;
            filter: invert(0) !important;
        }
        
        /* Modal specific styling */
        .modal-box input, .modal-box textarea, .modal-box select {
            background-color: white !important;
            color: black !important;
        }
        
        /* Search bars specifically */
        .input-bordered {
            background-color: white !important;
            color: black !important;
            border-color: #d1d5db !important;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-200 w-full">
        <div class="flex">
            <!-- Sidebar -->
            <div class="hidden lg:block">
                @include('layouts.sidebar')
            </div>
            <main class="w-full">
            <div class="sticky top-0 z-50 bg-white shadow-md">
                <x-navbar />
            </div>
                <div class="flex-grow p-4">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    <!-- Footer -->
<footer class="bg-gray-100 text-gray-700 p-4 mt-4">
    <div class="container mx-auto text-center">
        <p class="font-bold">Copyright &copy; {{ date('Y') }} - All right reserved by LNU IT Students</p>
</footer>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#birthdate_bride').on('change', function() {
                var birthdate = new Date($(this).val());
                var today = new Date();
                var age = today.getFullYear() - birthdate.getFullYear();
                var monthDifference = today.getMonth() - birthdate.getMonth();

                if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthdate
                .getDate())) {
                    age--;
                }

                $('#age_bride').val(age);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#toggle-drawer').click(function() {
                $('#my-drawer').prop('checked', function(i, val) {
                    return !val;
                });
            });

            $('.menu-link').click(function() {
                $('#my-drawer').prop('checked', false);
            });
        });

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

        // Initialize Supabase Realtime
        if (typeof window.supabase === 'undefined') {
            console.error('Supabase library not loaded');
        } else {
            var supabase = window.supabase.createClient(
                'https://lruvxbhfiogqolwztovs.supabase.co',
                'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImxydXZ4YmhmaW9ncW9sd3p0b3ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Njg4NDE5MzcsImV4cCI6MjA4NDQxNzkzN30.K5MnbKEYPLVNbqNKmSua_DjzQ-NwwIRGzYHxSxTc6pE'
            );

            console.log('Supabase Realtime initialized');

            // Listen to requests
            supabase.channel('requests').on('postgres_changes', 
                { event: '*', schema: 'public', table: 'trequests' },
                function(payload) {
                    console.log('Request updated:', payload);
                    if (window.location.pathname.includes('request') || window.location.pathname.includes('approval')) {
                        location.reload();
                    }
                }
            ).subscribe(function(status) { console.log('Requests channel:', status); });

            // Listen to payments
            supabase.channel('payments').on('postgres_changes',
                { event: '*', schema: 'public', table: 'tpayments' },
                function(payload) {
                    console.log('Payment updated:', payload);
                    if (window.location.pathname.includes('payment') || window.location.pathname.includes('request')) {
                        location.reload();
                    }
                }
            ).subscribe(function(status) { console.log('Payments channel:', status); });

            // Listen to donations
            supabase.channel('donations').on('postgres_changes',
                { event: '*', schema: 'public', table: 'tdonations' },
                function(payload) {
                    console.log('Donation updated:', payload);
                    if (window.location.pathname.includes('donation')) {
                        location.reload();
                    }
                }
            ).subscribe(function(status) { console.log('Donations channel:', status); });

            // Listen to announcements
            supabase.channel('announcements').on('postgres_changes',
                { event: '*', schema: 'public', table: 'tannouncements' },
                function(payload) {
                    console.log('Announcement updated:', payload);
                    if (window.location.pathname === '/' || window.location.pathname.includes('announcement')) {
                        location.reload();
                    }
                }
            ).subscribe(function(status) { console.log('Announcements channel:', status); });

            // Listen to certificate details updates
            supabase.channel('certificate_details').on('postgres_changes',
                { event: '*', schema: 'public', table: 'tcertificate_details' },
                function(payload) {
                    console.log('Certificate detail updated:', payload);
                    if (window.location.pathname.includes('request') || window.location.pathname.includes('approval')) {
                        location.reload();
                    }
                }
            ).subscribe(function(status) { console.log('Certificate details channel:', status); });
        }
    </script>
</body>

</html>
