<!DOCTYPE html>
<html lang="en" class="system">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Envoyr</title>

    <!-- Google Fonts: Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Theme Toggle -->
    <script>
      (function () {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
          document.documentElement.classList.remove('light');
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
          document.documentElement.classList.add('light');
        }
      })();
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Animations -->
    <style>
      @keyframes fadeInScale {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
      }

      .fade-in-scale {
        animation: fadeInScale 1.5s ease-out forwards;
      }

      @keyframes waveAnimation {
        0% { transform: translateX(0); }
        50% { transform: translateX(-25px); }
        100% { transform: translateX(0); }
      }

      .animated-wave {
        animation: waveAnimation 8s ease-in-out infinite;
      }

      header {
        position: sticky;
        top: 0;
        z-index: 50;
      }
    </style>
  </head>

  <body class="transition-colors duration-300 font-sans bg-white text-black dark:bg-black dark:text-white">
    @yield('content')
  </body>
</html>
