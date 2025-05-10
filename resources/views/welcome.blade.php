<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Envoyr</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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

    .light body {
      background-color: white;
      color: #1a202c; 
    }

    .light .bg-gray-50 {
      background-color: rgb(226, 232, 237);
    }

    .light .bg-white {
      background-color: white;    
    }

    .light .text-indigo-600 {
      color: rgb(200, 62, 191); 
    }

    .light .border-indigo-600 {
      border-color: #4f46e5;
    }

    .light .hover\:bg-indigo-50:hover {
      background-color: #eef2ff;
    }

    .light footer {
      background-color: rgb(234, 60, 213);
      color: black !important;
    }

    .light footer a {
      color: rgb(255, 255, 255);
    }

    .light footer a:hover {
      color: rgb(205, 205, 0); 
    }
  </style>
</head>

<body class="bg-white text-gray-900 transition-colors duration-300 font-sans dark:bg-gray-600 dark:text-gray-100 light:bg-white light:text-gray-900">

  <header class="flex justify-between items-center p-6 shadow-sm bg-gray-50 dark:bg-gray-800">
    <h1 class="text-2xl font-bold text-indigo-600 dark:text-pink-400">Envoyr</h1>
    <div class="flex items-center gap-4">
  <!-- Theme Toggle Button -->
  <button 
    onclick="toggleLightMode()" 
    aria-label="Toggle dark/light mode"
    class="text-sm px-4 py-2 rounded border border-indigo-500 dark:border-pink-400 text-indigo-600 dark:text-pink-400 hover:bg-indigo-100 dark:hover:bg-pink-900 transition flex items-center gap-2"
  >
    <i id="theme-icon" class="fas fa-moon"></i>
  </button>

  <!-- Login Button -->
  <button
    class="text-sm px-4 py-2 rounded border border-indigo-500 dark:border-pink-400 text-indigo-600 dark:text-pink-400 hover:bg-indigo-100 dark:hover:bg-pink-900 transition"
  >
    Login
  </button>

  <!-- Sign Up Button -->
  <button
    class="text-sm px-4 py-2 rounded bg-indigo-600 text-white dark:bg-pink-500 dark:text-white hover:bg-indigo-700 dark:hover:bg-pink-600 transition"
  >
    Sign Up
  </button>
</div>
  </header>

  <main class="min-h-screen grid grid-cols-1 md:grid-cols-2 gap-10 items-center px-6 py-20 bg-white dark:bg-gray-700">
    <div class="text-center md:text-left fade-in-scale">
      <h2 class="text-8xl font-extrabold text-indigo-600 dark:text-pink-400">Envoyr</h2>
      <p class="text-lg mt-4 max-w-md mx-auto md:mx-0">
        Your seamless communication solution for modern document tracking.
      </p>
      <button class="mt-6 text-sm border border-indigo-600 dark:border-pink-400 text-indigo-600 dark:text-pink-400 px-6 py-2 rounded hover:bg-indigo-50 dark:hover:bg-pink-900 transition">
        Learn More
      </button>
    </div>

    <div class="fade-in-scale flex justify-center">
    <img 
  src="{{ asset('images/hero-docs.jpg') }}" 
  alt="Person handling documents" 
  class="w-[500px] md:w-[600px] lg:w-[700px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"
          />
    </div>
  </main>

  <section class="py-20 px-6 bg-gray-50 dark:bg-gray-800">
    <div class="text-center">
      <h3 class="text-3xl font-semibold text-indigo-600 dark:text-pink-400">Features</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12 max-w-6xl mx-auto">
      <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-md transition text-center">
        <i class="fas fa-file-alt text-4xl text-indigo-600 dark:text-pink-400"></i>
        <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400 mt-4">Document Tracker</h4>
        <p class="mt-2 text-sm">Monitor documents as they move through departments in real-time.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-md transition text-center">
        <i class="fas fa-qrcode text-4xl text-indigo-600 dark:text-pink-400"></i>
        <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400 mt-4">QR Code Integration</h4>
        <p class="mt-2 text-sm">Assign a unique QR code to each document for secure, fast tracking.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-md transition text-center">
        <i class="fas fa-map-marker-alt text-4xl text-indigo-600 dark:text-pink-400"></i>
        <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400 mt-4">Location Status</h4>
        <p class="mt-2 text-sm">Instantly view the current location and status of any document.</p>
      </div>
    </div>
  </section>

  <section class="py-20 px-6">
  <div class="text-center">
    <h3 class="text-3xl font-semibold text-indigo-600 dark:text-pink-400">How It Works</h3>
  </div>
  <div class="mt-16 relative max-w-5xl mx-auto">
    <div class="hidden md:block absolute left-1/2 transform -translate-x-1/2 h-full border-l-2 border-indigo-300 dark:border-pink-400"></div>

    <div class="mb-12 flex flex-col md:flex-row items-center md:justify-start relative">
      <div class="w-full md:w-1/2 md:pr-8 text-center md:text-left">
        <div class="inline-block w-full md:w-auto p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow">
          <div class="flex justify-center md:justify-end mb-4">
    
            <svg class="w-12 h-12 text-indigo-600 dark:text-pink-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" />
              <path d="M14 2v6h6" />
            </svg>
          </div>
          <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400">Step 1: Document Creation</h4>
          <p class="mt-2 text-sm text-black-700 dark:text-white-700">A document is created and registered in the system by the originating office.</p>
        </div>
      </div>
    </div>

    <div class="mb-12 flex flex-col md:flex-row items-center md:justify-end relative">
      <div class="w-full md:w-1/2 md:pl-8 text-center md:text-left">
        <div class="inline-block w-full md:w-auto p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow">
          <div class="flex justify-center md:justify-start mb-4">
            
            <svg class="w-12 h-12 text-indigo-600 dark:text-pink-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M3 3h5v5H3V3zm13 0h5v5h-5V3zM3 16h5v5H3v-5zm13 5v-5h-2m4 0h2v2m-2 2v2M13 3v10H3" />
            </svg>
          </div>
          <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400">Step 2: QR Code Assignment</h4>
          <p class="mt-2 text-sm text-black-700 dark:text-white-700">A unique QR code is generated and attached for seamless tracking.</p>
        </div>
      </div>
    </div>

    <div class="mb-12 flex flex-col md:flex-row items-center md:justify-start relative">
      <div class="w-full md:w-1/2 md:pr-8 text-center md:text-left">
        <div class="inline-block w-full md:w-auto p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow">
          <div class="flex justify-center md:justify-end mb-4">
            
            <svg class="w-12 h-12 text-indigo-600 dark:text-pink-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M4 4v6h6M20 20v-6h-6M20 4h-6v6M4 20h6v-6" />
            </svg>
          </div>
          <h4 class="text-xl font-bold text-indigo-600 dark:text-pink-400">Step 3: Status Update</h4>
          <p class="mt-2 text-sm text-black-700 dark:text-white-700">Receiving offices scan the QR to update the document's current location.</p>
        </div>
      </div>
    </div>
  </div>
</section>

  <section class="py-20 px-6 bg-gray-50 dark:bg-gray-700">
  <div class="max-w-4xl mx-auto text-center">
    <h3 class="text-3xl font-semibold text-indigo-600 dark:text-pink-400 mb-8">What Users Say</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
        <p class="italic">"Envoyr completely changed how we handle document tracking. It's seamless, fast, and reliable!"</p>
        <div class="mt-4 text-right font-semibold text-indigo-600 dark:text-pink-400">– Jordan C., Registrar</div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
        <p class="italic">"The QR code feature is a game changer. We can now locate files instantly!"</p>
        <div class="mt-4 text-right font-semibold text-indigo-600 dark:text-pink-400">– Aimee R., Admin Staff</div>
      </div>
    </div>
  </div>
</section>

  <section class="relative bg-cover bg-center bg-no-repeat py-24 px-6" style="background-image: url('https://images.unsplash.com/photo-1581090700227-1e8e9dc88007?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');">
  <div class="bg-gray-50 dark:bg-gray-800 bg-opacity-90 rounded-xl max-w-4xl mx-auto text-center p-10 text-black-700">
    <h3 class="text-3xl font-bold mb-4">Ready to Streamline Your Workflow?</h3>
    <p class="text-lg mb-6">Try Envoyr today and experience efficient document tracking like never before.</p>
    <a href="#contact" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded shadow transition">Get Started</a>
  </div>
</section>

  <footer class="text-center p-6 mt-12 dark:bg-gray-900 bg-indigo-100 text-black dark:text-white">
  <p>&copy; 2025 Envoyr. All rights reserved.</p>
  <div class="flex justify-center space-x-4 mt-3">
    <a href="#" class="hover:text-yellow-300"><i class="fab fa-facebook-f"></i></a>
    <a href="#" class="hover:text-yellow-300"><i class="fab fa-twitter"></i></a>
    <a href="#" class="hover:text-yellow-300"><i class="fab fa-linkedin-in"></i></a>
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
        const html = document.documentElement;
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
      }
    });
  </script>

</body>
</html>
