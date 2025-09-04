<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Button Styles -->
    <style>
        /* Base button styles */
        .btn {
            @apply inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-medium text-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
        }

        /* Primary button */
        .btn-primary {
            @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
        }

        /* Secondary button */
        .btn-secondary {
            @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
        }

        /* Success button */
        .btn-success {
            @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
        }

        /* Danger button */
        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
        }

        /* Warning button */
        .btn-warning {
            @apply bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500;
        }

        /* Outline buttons */
        .btn-outline-primary {
            @apply border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white focus:ring-blue-500;
        }

        .btn-outline-secondary {
            @apply border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white focus:ring-gray-500;
        }

        /* Button sizes */
        .btn-sm {
            @apply px-3 py-1 text-xs;
        }

        .btn-lg {
            @apply px-6 py-3 text-base;
        }

        /* Block button */
        .btn-block {
            @apply w-full;
        }

        /* Icon buttons */
        .btn-icon {
            @apply p-2 rounded-full;
        }

        /* Loading state */
        .btn-loading {
            @apply opacity-75 cursor-wait;
        }

        .btn-loading::before {
            content: "";
            @apply inline-block w-4 h-4 mr-2 border-2 border-current border-t-transparent rounded-full animate-spin;
        }
    </style>
</head>
<body class="antialiased">
    @yield('content')
</body>
</html>
