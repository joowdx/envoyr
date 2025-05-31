@extends('layouts.app')
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-black dark:bg-gray-800 shadow-md">
      <div class="max-w-8xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-primary dark:text-pink-400">Envoyr</h1>
        <div class="flex items-center gap-4">
          <div class="flex justify-end w-full">
                        @include('theme-switcher')
          </div>
        <div class="flex space-x-4">
            <button
              class="text-sm px-4 py-1 border border-primary text-white dark:text-pink-400 rounded hover:bg-primary transition-colors duration-300"
            >
              Login
            </button>
            <button
              class="text-sm px-6 py-2 border border-primary text-white dark:text-pink-400 rounded hover:bg-primary transition-colors duration-300 whitespace-nowrap"
            >
              Sign Up
            </button>
        </div>
      </div>
    </header>

    <!-- Hero -->
    <section class="min-h-screen bg-black text-white flex items-center justify-center px-6 py-20 bg-gradient-to-br from-gray-900 to-gray-800 text-left">
      <div class="max-w-3xl">
        <h2 class="text-5xl md:text-7xl font-extrabold mb-6">
          Track Documents <span class="text-pink-400">Smarter</span>
        </h2>
        <p class="text-lg mb-4 text-gray-300">
          Envoyr makes document tracking seamless, efficient, and fully transparent.
        </p>
        <p class="text-lg mb-8 text-gray-300">
          Powered by smart QR codes.
        </p>
        <a href="#features" class="bg-primary text-white px-6 py-3 rounded border border-indigo-500 font-semibold text-lg hover:bg-pink-400 transition">
          Get Started
        </a>
      </div>
      <div class="fade-in-scale flex justify-center">
  <img
    src="/images/heroIMG2.webp"
    alt="Person handling documents"
    onerror="this.src='/images/heroIMG2.webp';"
    class="w-[400px] md:w-[500px] lg:w-[600px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"
  />
</div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 bg-black dark:bg-gray-800 px-6">
      <div class="max-w-6xl mx-auto text-center mb-16">
        <h3 class="text-4xl font-bold text-primary dark:text-white">Key Features</h3>
      </div>
      <div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto">
        <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
          <i class="fas fa-file-alt text-4xl text-white mb-4"></i>
          <h4 class="text-xl text-primary dark:text-pink-400 font-bold mb-2">Document Tracker</h4>
          <p class="text-white dark:text-white">Monitor documents as they move across offices in real-time.</p>
        </div>
        <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
          <i class="fas fa-qrcode text-4xl text-white mb-4"></i>
          <h4 class="text-xl text-primary dark:text-pink-400 font-bold mb-2">QR Code Integration</h4>
          <p class="text-white dark:text-white">Each document gets a unique, scannable code for fast updates.</p>
        </div>
        <div class="bg-black text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition">
          <i class="fas fa-map-marker-alt text-4xl text-white mb-4"></i>
          <h4 class="text-xl text-primary dark:text-pink-400 font-bold mb-2">Live Location</h4>
          <p class="text-white dark:text-white">Know exactly where any document is—anytime, anywhere.</p>
        </div>
      </div>
    </section>

    <!-- Steps -->
    <section class="min-h-screen bg-black flex items-center justify-center px-6 py-20 bg-gradient-to-br from-gray-900 to-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-6xl items-center">
        <div class="flex justify-center">
          <img
            src="images/HowItWorksIMG1.webp"
            alt="Person handling documents"
            class="w-[400px] md:w-[500px] lg:w-[600px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"
          />
        </div>
        <div class="space-y-6">
          <h3 class="text-4xl font-bold text-white dark:text-pink-400 text-center md:text-left">How It Works</h3>

          <!-- Step 1 -->
          <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
            <div class="bg-red-500 text-white w-20 flex flex-col items-center justify-center p-4">
              <span class="text-3xl font-bold">01</span>
              <span class="uppercase text-xs tracking-widest">Step</span>
            </div>
            <div class="p-6 text-primary text-left">
              <h4 class="text-xl text-primary dark:text-white font-semibold">Create & Register</h4>
              <p class="text-white dark:text-white">Originating office creates and inputs the document into the system.</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
            <div class="bg-blue-500 text-white w-20 flex flex-col items-center justify-center p-4">
              <span class="text-3xl font-bold">02</span>
              <span class="uppercase text-xs tracking-widest">Step</span>
            </div>
            <div class="p-6 text-left">
              <h4 class="text-xl text-primary dark:text-white font-semibold">Assign QR Code</h4>
              <p class="text-white dark:text-white">System generates a unique code for the document to enable fast scans.</p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex bg-gray-700 rounded-lg overflow-hidden shadow-lg">
            <div class="bg-green-500 text-white w-20 flex flex-col items-center justify-center p-4">
              <span class="text-3xl font-bold">03</span>
              <span class="uppercase text-xs tracking-widest">Step</span>
            </div>
            <div class="p-6 text-left">
              <h4 class="text-xl text-primary dark:text-white font-semibold">Track & Update</h4>
              <p class="text-white dark:text-white">QR code scans update the location and status in real-time.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-black dark:bg-gray-800 text-black dark:text-white py-20 text-center px-6">
      <h3 class="text-4xl font-extrabold mb-4">Start Tracking <span class="text-pink-400">Smarter</span> Today</h3>
      <p class="text-lg mb-6">Sign up now and streamline your document workflow with Envoyr.</p>
      <a
        href="#"
        class="bg-white text-black dark:bg-black dark:text-white px-6 py-3 rounded hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors duration-300 font-semibold"
      >
        Create Free Account
      </a>
    </section>

    <!-- Tech Stack -->
    <section class="min-h-screen bg-black bg-gradient-to-br from-gray-900 to-gray-800 text-white flex flex-col items-center justify-center px-6 py-20">
      <h2 class="text-4xl font-bold mb-4">The tech <span class="text-p400">STACK</span></h2>
      <p class="text-gray-400 max-w-xl text-center">
        The technologies we use to build this application. We use the latest technologies to build this application.
      </p>
      <div class="mt-12 grid grid-cols-3 gap-4 place-items-center">
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/php-logo.png" alt="PHP" class="h-10" />
        </div>
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/laravel_logo.png" alt="Laravel" class="h-10" />
        </div>
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/filament-logo.jpg" alt="Filament" class="h-10" />
        </div>
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/tailwind-logo.webp" alt="TailwindCSS" class="h-10" />
        </div>
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/next.js-logo.png" alt="NextJS" class="h-10" />
        </div>
        <div class="bg-gray-800 p-6 rounded-xl flex items-center justify-center w-24 h-24 shadow-lg">
          <img src="images/github-logo.png" alt="GitHub" class="h-10" />
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black dark:bg-gray-800 text-center py-6">
      <p class="text-sm text-white dark:text-white">&copy; 2025 Envoyr. All rights reserved.</p>
      <div class="flex justify-center gap-4 mt-3 text-white">
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

