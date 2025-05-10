<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Envoyr</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#c83ebf',
            dark: '#0f0f0f',
            light: '#ffffff',
            grayish: '#1f1f1f',
          }
        }
      }
    }
  </script>

  <style>
    @keyframes fadeInScale {
      0% {
        opacity: 0;
        transform: scale(0.8);
      }
      100% {
        opacity: 1;
        transform: scale(1);
      }
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

    /* Light theme overrides */
    .light {
      background-color: white;
      color: #1a202c;
    }

    .light section,
    .light .bg-dark,
    .light .bg-gray-800,
    .light .bg-gray-700 {
      background-color: #f8f9fa !important;
      color: #1a202c;
    }

    .light .text-gray-300 {
      color:rgb(0, 0, 0) !important;
    }

    .light .text-primary {
      color: #c83ebf !important;
    }

    .light .shadow,
    .light .shadow-md,
    .light .shadow-lg {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .light .bg-gradient-to-br,
    .light .bg-gradient-to-r {
      background: linear-gradient(to right, #fff, #e2e8f0) !important;
    }

    .light .bg-black,
    .light .bg-dark {
      background-color: #ffffff !important;
    }

    .light footer {
      background-color: #f1f5f9 !important;
      color: #1a202c !important;
    }

    .light footer a {
      color: #000000;
    }

    .light footer a:hover {
      color: #c83ebf;
    }
  </style>
</head>
<body class="transition-colors duration-300 font-sans">

  <!-- Header -->
  <header class="sticky top-0 z-50 bg-dark shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-primary">Envoyr</h1>
      <div class="flex items-center gap-4">
        <button 
          onclick="toggleLightMode()" 
          aria-label="Toggle dark/light mode"
          class="text-sm px-4 py-2 rounded border border-indigo-500 dark:border-pink-400 text-indigo-600 dark:text-pink-400 hover:bg-indigo-100 dark:hover:bg-pink-900 transition flex items-center gap-2"
        >
          <i id="theme-icon" class="fas fa-moon"></i>
        </button>
        <button class="text-sm px-4 py-2 border border-primary text-black dark:text-white rounded hover:bg-primary transition-colors duration-300">
  Login
</button>
<button class="text-sm px-4 py-2 bg-primary text-white rounded hover:bg-pink-400 transition-colors duration-300">
  Sign Up
</button>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="min-h-screen bg-black text-white flex items-center justify-center px-6 py-20 bg-gradient-to-br from-gray-900 to-gray-800 text-left">
    <div class="max-w-3xl">
      <h2 class="text-5xl md:text-7xl font-extrabold mb-6">Track Documents <span class="text-primary">Smarter</span></h2>
      <p class="text-lg mb-8 text-gray-300">Envoyr makes document tracking seamless, efficient, and fully transparent—powered by smart QR codes.</p>
      <a href="#features" class="bg-primary text-white px-6 py-3 rounded font-semibold text-lg hover:bg-pink-400 transition">Get Started</a>
    </div>
    <div class="fade-in-scale flex justify-center">
      <img src="{{ asset('images/heroIMG1.webp') }}" alt="Person handling documents" class="w-[400px] md:w-[500px] lg:w-[600px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"/>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="py-20 bg-dark px-6">
    <div class="max-w-6xl mx-auto text-center mb-16">
      <h3 class="text-4xl font-bold text-primary">Key Features</h3>
    </div>
    <div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto">
      <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
        <i class="fas fa-file-alt text-4xl text-primary mb-4"></i>
        <h4 class="text-xl font-bold mb-2">Document Tracker</h4>
        <p class="text-gray-300">Monitor documents as they move across offices in real-time.</p>
      </div>
      <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
        <i class="fas fa-qrcode text-4xl text-primary mb-4"></i>
        <h4 class="text-xl font-bold mb-2">QR Code Integration</h4>
        <p class="text-gray-300">Each document gets a unique, scannable code for fast updates.</p>
      </div>
      <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
        <i class="fas fa-map-marker-alt text-4xl text-primary mb-4"></i>
        <h4 class="text-xl font-bold mb-2">Live Location</h4>
        <p class="text-gray-300">Know exactly where any document is—anytime, anywhere.</p>
      </div>
    </div>
  </section>

  <!-- Steps -->
  <section class="min-h-screen flex items-center justify-center px-6 py-20 bg-gradient-to-br from-gray-900 to-gray-800">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-6xl items-center">
      <div class="flex justify-center">
        <img src="{{ asset('images/HowItWorksIMG1.webp') }}" alt="Person handling documents" class="w-[400px] md:w-[500px] lg:w-[600px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"/>
      </div>
      <div class="space-y-6">
        <h3 class="text-4xl font-bold text-primary text-center md:text-left">How It Works</h3>
        <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
          <div class="bg-red-500 text-white w-20 flex flex-col items-center justify-center p-4">
            <span class="text-3xl font-bold">01</span>
            <span class="uppercase text-xs tracking-widest">Step</span>
          </div>
          <div class="p-6 text-primary text-left">
            <h4 class="text-xl font-semibold">Create & Register</h4>
            <p class="text-gray-300">Originating office creates and inputs the document into the system.</p>
          </div>
        </div>
        <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
          <div class="bg-blue-500 text-white w-20 flex flex-col items-center justify-center p-4">
            <span class="text-3xl font-bold">02</span>
            <span class="uppercase text-xs tracking-widest">Step</span>
          </div>
          <div class="p-6 text-left">
            <h4 class="text-xl text-primary font-semibold">Assign QR Code</h4>
            <p class="text-gray-300">System generates a unique code for the document to enable fast scans.</p>
          </div>
        </div>
        <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
          <div class="bg-green-500 text-white w-20 flex flex-col items-center justify-center p-4">
            <span class="text-3xl font-bold">03</span>
            <span class="uppercase text-xs tracking-widest">Step</span>
          </div>
          <div class="p-6 text-left">
            <h4 class="text-xl text-primary font-semibold">Track & Update</h4>
            <p class="text-gray-300">QR code scans update the location and status in real-time.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="py-20 px-6 bg-dark">
    <div class="max-w-4xl mx-auto text-center mb-10">
      <h3 class="text-4xl font-bold text-primary mb-6">What Our Users Say</h3>
    </div>
    <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
      <div class="bg-gray-800 p-6 rounded-xl shadow">
        <p class="italic text-gray-300">"Envoyr revolutionized how we track documents. Fast, efficient, and totally stress-free!"</p>
        <div class="mt-4 text-right font-semibold text-primary">– Jordan C., Registrar</div>
      </div>
      <div class="bg-gray-800 p-6 rounded-xl shadow">
        <p class="italic text-gray-300">"The QR codes are genius. No more missing files!"</p>
        <div class="mt-4 text-right font-semibold text-primary">– Aimee R., Admin Staff</div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="bg-gradient-to-r from-primary to-pink-400 text-white py-20 text-center px-6">
    <h3 class="text-4xl font-extrabold mb-4">Start Tracking Smarter Today</h3>
    <p class="text-lg mb-6">Sign up now and streamline your document workflow with Envoyr.</p>
    <a href="#" class="bg-white text-black dark:bg-black dark:text-white px-6 py-3 rounded hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors duration-300 font-semibold">
  Create Free Account
</a>

  </section>

  <!-- Tech Stack -->
  <section class="min-h-screen bg-black text-white flex flex-col items-center justify-center px-6 py-20">
    <h2 class="text-4xl font-bold mb-4">The tech <span class="text-p400">STACK</span></h2>
    <p class="text-gray-400 max-w-xl text-center">The technologies we use to build this application. We use the latest technologies to build this application.</p>
    <div class="mt-12 grid grid-cols-3 gap-4 place-items-center">
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/php-logo.png" alt="PHP" class="h-10"></div>
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/laravel_logo.png" alt="Laravel" class="h-10"></div>
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/filament-logo.jpg" alt="Filament" class="h-10"></div>
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/tailwind-logo.webp" alt="TailwindCSS" class="h-10"></div>
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/next.js-logo.png" alt="NextJS" class="h-10"></div>
      <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg"><img src="images/github-logo.png" alt="GitHub" class="h-10"></div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-center py-6">
    <p class="text-sm text-gray-400">&copy; 2025 Envoyr. All rights reserved.</p>
    <div class="flex justify-center gap-4 mt-3 text-primary">
      <a href="#"><i class="fab fa-facebook-f hover:text-white"></i></a>
      <a href="#"><i class="fab fa-twitter hover:text-white"></i></a>
      <a href="#"><i class="fab fa-linkedin-in hover:text-white"></i></a>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('theme');
      const html = document.documentElement;

      if (savedTheme === 'dark') {
        html.classList.add('dark');
        html.classList.remove('light');
      } else {
        html.classList.remove('dark');
        html.classList.add('light');
      }

      updateToggleIcon();

      window.toggleLightMode = function () {
        const isDark = html.classList.contains('dark');

        if (isDark) {
          html.classList.remove('dark');
          html.classList.add('light');
          localStorage.setItem('theme', 'light');
        } else {
          html.classList.remove('light');
          html.classList.add('dark');
          localStorage.setItem('theme', 'dark');
        }

        updateToggleIcon();
      };

      function updateToggleIcon() {
        const icon = document.getElementById('theme-icon');
        const isDark = document.documentElement.classList.contains('dark');
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        icon.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
      }
    });
  </script>

</body>
</html>
