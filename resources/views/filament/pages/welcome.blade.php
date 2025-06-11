@extends('layouts.app')

@section('content')
<!-- Header -->
<header class="sticky top-0 y-20 text-white bg-gray-200 dark:bg-gray-950 shadow-md">
  <div class="max-w-7xl mx-auto px-12 py-2 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-amber-500 dark:text-amber-500">Envoyr</h1>
    <div class="text-amber-500 dark:text-amber-500 flex items-center gap-4">
      @include('theme-switcher')

      <div class="flex space-x-4 whitespace-nowrap">
        <x-filament::button outlined tag="a" href="{{ route('filament.auth.auth.login') }}" class="hover:bg-amber-600 hover:text-white transition duration-300">
          Login
        </x-filament::button>
        <x-filament::button outlined tag="a" href="{{ route('filament.auth.auth.register') }}" class="hover:bg-amber-600 hover:text-white transition duration-300">
          Sign Up
        </x-filament::button>
      </div>
    </div>
  </div>
</header>

<!-- Hero -->
<section class="min-h-screen bg-white text-gray-900 dark:bg-neutral-900 dark:text-white flex items-center justify-center px-6 py-20 text-left">
  <div class="max-w-2xl" data-aos="fade-right" data-aos-delay="100">
    <h2 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6">
      Track Documents <span class="text-amber-500">Smarter</span>
    </h2>
    <p class="text-lg mb-4 text-gray-700 dark:text-gray-300">Envoyr makes document tracking seamless, efficient, and fully transparent.</p>
  <p class="text-lg mb-8 text-gray-700 dark:text-gray-300">Powered by smart QR codes.</p>
    <x-filament::button outlined tag="a" href="{{ route('filament.auth.auth.register') }}" icon="gmdi-rocket-launch-o" size="xl" class="hover:bg-amber-600 hover:text-white transition duration-300">
      Get Started
    </x-filament::button>
  </div>
  <div class="fade-in-scale flex justify-center" data-aos="zoom-in" data-aos-delay="200">
    <img
      src="/images/heroIMG2.webp"
      alt="Person handling documents"
      onerror="this.src='/images/heroIMG2.webp';"
      class="w-[300px] md:w-[400px] lg:w-[500px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"
    />
  </div>
</section>

<!-- Features -->
<section id="features" class="py-20 bg-gray-300 dark:bg-neutral-800 px-6">
  <div class="max-w-6xl mx-auto text-center mb-16" data-aos="fade-up">
    <h3 class="text-4xl font-bold text-amber-500 dark:text-amber-500">Key Features</h3>
  </div>
  <div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto">
    <div class="bg-gray-200 dark:bg-gray-900 text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-up" data-aos-delay="100">
      <i class="fas fa-file-alt text-4xl text-white mb-4"></i>
      <h4 class="text-xl text-amber-500 dark:text-amber-500 font-bold mb-2">Document Tracker</h4>
      <p class="text-gray-900 dark:text-white">Monitor documents as they move across offices in real-time.</p>
    </div>
    <div class="bg-gray-200 dark:bg-gray-900 text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-up" data-aos-delay="200">
      <i class="fas fa-qrcode text-4xl text-white mb-4"></i>
      <h4 class="text-xl text-amber-500 dark:text-amber-500 font-bold mb-2">QR Code Integration</h4>
      <p class="text-gray-900 dark:text-white">Each document gets a unique, scannable code for fast updates.</p>
    </div>
    <div class="bg-gray-200 dark:bg-gray-900 text-primary p-8 rounded-xl shadow-md hover:shadow-lg transition" data-aos="fade-up" data-aos-delay="300">
      <i class="fas fa-map-marker-alt text-4xl text-white mb-4"></i>
      <h4 class="text-xl text-amber-500 dark:text-amber-500 font-bold mb-2">Live Location</h4>
      <p class="text-gray-900 dark:text-white">Know exactly where any document is—anytime, anywhere.</p>
    </div>
  </div>
</section>

<!-- Steps -->
<section class="min-h-screen bg-white flex items-center justify-center px-6 py-20 dark:bg-neutral-900">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-6xl items-center">
    <div class="flex justify-center" data-aos="zoom-in-right" data-aos-delay="100">
      <img
        src="images/HowItWorksIMG1.webp"
        alt="Person handling documents"
        class="w-[400px] md:w-[500px] lg:w-[600px] rounded-lg shadow-2xl hover:scale-105 transition-transform duration-300"
      />
    </div>
    <div class="space-y-6">
      <h3 class="text-4xl font-bold text-amber-500 dark:text-amber-500 text-center md:text-left" data-aos="fade-up" data-aos-delay="100">How It Works</h3>

      <div class="flex bg-black hover:ring-2 hover:ring-amber-500 dark:bg-neutral-800 rounded-lg overflow-hidden shadow-lg transition" data-aos="fade-left" data-aos-delay="200">
        <div class="bg-amber-600 text-white w-20 flex flex-col items-center justify-center p-4">
          <span class="text-3xl font-bold">01</span>
          <span class="uppercase text-xs tracking-widest">Step</span>
        </div>
        <div class="p-6 text-primary text-left">
          <h4 class="text-xl text-white dark:text-amber-500 font-semibold">Create & Register</h4>
          <p class="text-white dark:text-white">Originating office creates and inputs the document into the system.</p>
        </div>
      </div>

      <div class="flex bg-black hover:ring-2 hover:ring-amber-500 dark:bg-neutral-800 rounded-lg overflow-hidden shadow-lg transition" data-aos="fade-left" data-aos-delay="300">
        <div class="bg-amber-700 text-white w-20 flex flex-col items-center justify-center p-4">
          <span class="text-3xl font-bold">02</span>
          <span class="uppercase text-xs tracking-widest">Step</span>
        </div>
        <div class="p-6 text-left">
          <h4 class="text-xl text-white dark:text-amber-500 font-semibold">Assign QR Code</h4>
          <p class="text-white dark:text-white">System generates a unique code for the document to enable fast scans.</p>
        </div>
      </div>

      <div class="flex bg-black hover:ring-2 hover:ring-amber-500 dark:bg-neutral-800 rounded-lg overflow-hidden shadow-lg transition" data-aos="fade-left" data-aos-delay="400">
        <div class="bg-amber-800 text-white w-20 flex flex-col items-center justify-center p-4">
          <span class="text-3xl font-bold">03</span>
          <span class="uppercase text-xs tracking-widest">Step</span>
        </div> 
        <div class="p-6 text-left">
          <h4 class="text-xl text-white dark:text-amber-500 font-semibold">Track & Update</h4>
          <p class="text-white dark:text-white">QR code scans update the location and status in real-time.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="bg-gray-300 dark:bg-gray-900 text-white py-20 text-center px-6" data-aos="zoom-in" data-aos-delay="100">
  <h3 class="text-gray-700 dark:text-gray-300 text-4xl font-extrabold mb-4">Start Tracking <span class="text-amber-500">Smarter</span> Today</h3>
  <p class="text-lg mb-4 text-gray-700 dark:text-gray-300">Sign up now and streamline your document workflow with Envoyr.</p>
  <x-filament::button outlined class="hover:bg-amber-600 hover:text-white transition duration-300" >
    Create Free Account
  </x-filament::button>
</section>

<!-- Tech Stack -->
<section class="min-h-screen bg-white dark:bg-neutral-900 text-white flex flex-col items-center justify-center px-6 py-20">
  <div class="text-center" data-aos="fade-up" data-aos-delay="100">
    <h2 class="text-5xl font-semibold text-amber-500 dark:text-amber-500">
      The tech <span class="uppercase">stack</span>
    </h2>
    <p class="mt-6 text-white dark:text-white">
      The technologies we use to build this application. We use the latest technologies to build this application.
    </p>
  </div>
  <div class="relative px-6 mt-12 -mx-6 overflow-x-auto w-fit h-fit sm:mx-auto sm:px-0">
    <div class="flex gap-3 mx-auto mb-3 w-fit" data-aos="flip-up" data-aos-delay="200">
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-10 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-php" class="fill-amber-500" />
      </div>
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-laravel" class="fill-amber-500" />
      </div>
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-16 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-filament" class="fill-amber-500" />
      </div>
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-8 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-github" class="fill-amber-500" />
      </div>
    </div>
    <div class="flex gap-3 mx-auto mb-3 w-fit" data-aos="flip-up" data-aos-delay="300">
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-9 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-tailwindcss" class="fill-amber-500" />
      </div>
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-docker" class="fill-amber-500" />
      </div>
    </div>
    <div class="flex gap-3 mx-auto mb-3 w-fit" data-aos="flip-up" data-aos-delay="400">
      <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-10 *:m-auto size-20 mx-auto">
        <x-filament::icon icon="si-mysql" class="fill-amber-500" />
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="bg-gray-300 text-white dark:bg-gray-950 text-center py-6">
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
      if (!icon) return;

      const isDark = document.documentElement.classList.contains('dark');
      icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
      icon.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }
  });
</script>
@endsection
