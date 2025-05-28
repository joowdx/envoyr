<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            300: '#f9a8d4',
                            400: '#f472b6',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="relative flex items-center justify-center min-h-screen">
        <div class="relative z-10 w-full max-w-md p-8 mx-auto bg-white dark:bg-gray-800 rounded-2xl">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Access Denied
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    You do not have an assigned office.
                </p>

                <div class="bg-primary-50 dark:bg-primary-500/10 rounded-lg p-4 mb-6">
                    <p class="text-sm text-primary-700 dark:text-primary-300">
                        Please contact your administrator to request office assignment.
                    </p>
                </div>

                <div class="space-y-3">
                    <a href="mailto:admin@example.com" class="block w-full px-4 py-2 text-sm font-medium text-center text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors duration-200">
                        Contact Administrator
                    </a>
                    <a href="{{ route('filament.auth.auth.login') }}" class="block w-full px-4 py-2 text-sm font-medium text-center text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20 rounded-lg transition-colors duration-200">
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
