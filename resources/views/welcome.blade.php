<html lang="en">
  <head>
    <title>Envoyr</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <link rel="preconnect" href="https://fonts.gstatic.com"/>
    <link href="template-assets/fonts/fonts.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/tailwind/tailwind.min.css"/>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon.png"/>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer="defer"></script>
  </head>
  <body class="antialiased bg-body text-body font-body">
    <div>
      <div>
        <p class="mb-0 py-3 bg-sweetPink-200 text-sweetPink-800 text-center">Want to track your Documents much easier? Visit <a href="#">www.envoyr.com</a></p>
      </div>
      <section class="bg-gradient-to-t from-pink-300 via-pink-700 to-darkPink-900" x-data="{ mobileNavOpen: false }">
        <div class="container mx-auto px-4">
          <nav class="py-5 border-b border-white border-opacity-10">
            <div class="container mx-auto px-4">
              <div class="flex items-center justify-between"><h1 class="text-2xl font-bold text-white">Envoyr</h1>
                <div class="hidden lg:flex gap-2 p-1 rounded-full bg-white bg-opacity-10"><a class="px-3 py-2 rounded-full text-white text-sm hover:bg-white hover:bg-opacity-20 transition duration-200" href="#">Home</a><a class="px-3 py-2 rounded-full text-white text-sm hover:bg-white hover:bg-opacity-20 transition duration-200" href="#">About</a><a class="px-3 py-2 rounded-full text-white text-sm hover:bg-white hover:bg-opacity-20 transition duration-200" href="#">Pricing</a><a class="px-3 py-2 rounded-full text-white text-sm hover:bg-white hover:bg-opacity-20 transition duration-200" href="#">Blog</a><a class="px-3 py-2 rounded-full text-white text-sm hover:bg-white hover:bg-opacity-20 transition duration-200" href="#">Contact</a></div><a class="hidden lg:flex items-center gap-2 text-white hover:text-pink-500 transition duration-200" href="login.html"><span class="text-sm font-medium">Sign in</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewbox="0 0 16 16" fill="none">
                    <path d="M5.5 3L10.5 8L5.5 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg></a>
                <button class="lg:hidden" x-on:click="mobileNavOpen = !mobileNavOpen">
                  <svg width="51" height="51" viewbox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="56" height="56" rx="28" fill="white"></rect>
                    <path d="M37 32H19M37 24H19" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </button>
              </div>
            </div>
          </nav>
          <div class="relative pt-24 pb-14"><img class="hidden lg:block absolute left-16 xl:left-36 2xl:left-56 3xl:left-96 top-56" src="template-assets/images/hero/stars-pattern1.svg" alt=""/><img class="hidden lg:block absolute right-4 xl:right-36 2xl:right-56 3xl:right-96 top-56" src="template-assets/images/hero/stars-pattern2.svg" alt=""/>
            <div class="relative z-50">
              <h1 class="font-heading text-white text-center text-4xl sm:text-5xl md:text-7xl font-bold max-w-6xl mx-auto mb-6">Simplifying document tracking in your offices</h1>
              <p class="text-center text-white max-w-xl mx-auto text-lg mb-12">Envoyr makes document tracking seamless, efficient, and fully transparent—powered by smart QR codes.</p>
              <div class="mb-40 text-center"><a class="group relative inline-block p-0.5 font-semibold overflow-hidden rounded-full" href="#">
                  <div class="absolute inset-0 bg-gradient-to-b from-white to-gray-500 group-focus:to-white opacity-40 group-focus:opacity-20 rounded-full"></div>
                  <div class="relative z-50 flex items-center py-2 px-4 bg-white group-hover:bg-opacity-80 group-focus:bg-opacity-80 rounded-full transition duration-200">
                    <p class="text-pinkSecondary-900">Get started for free</p>
                  </div></a></div><img class="w-full object-cover" src="images/dashboard.jpg" alt=""/>
            </div>
          </div>
        </div>
        <div class="hidden fixed top-0 left-0 bottom-0 w-5/6 max-w-xs z-50" :class="{ 'hidden': !mobileNavOpen, 'block': mobileNavOpen }">
          <div class="fixed inset-0 bg-black opacity-20" x-on:click="mobileNavOpen = !mobileNavOpen"></div>
          <nav class="relative p-8 w-full h-full bg-white overflow-y-auto">
            <div class="flex items-center justify-between"><a class="inline-block" href="#"><img src="images/logo-black.svg" alt=""/></a>
              <button x-on:click="mobileNavOpen = !mobileNavOpen">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6 18L18 6M6 6L18 18" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>
            </div>
            <ul class="flex flex-col gap-12 py-12 my-12">
              <li><a class="text-sm font-medium" href="index.html">Home</a></li>
              <li><a class="text-sm font-medium" href="about.html">About</a></li>
              <li><a class="text-sm font-medium" href="pricing.html">Pricing</a></li>
              <li><a class="text-sm font-medium" href="blog.html">Blog</a></li>
              <li><a class="text-sm font-medium" href="contact.html">Contact</a></li>
            </ul>
            <div class="text-center"><a class="inline-flex items-center gap-2 text-black hover:text-opacity-80 transition duration-200" href="login.html"><span class="text-sm font-medium">Sign in</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewbox="0 0 16 16" fill="none">
                  <path d="M5.5 3L10.5 8L5.5 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg></a></div>
          </nav>
        </div>
      </section>
      <section class="bg-darkPink-900 py-28">
        <div class="container max-w-6xl mx-auto px-4">
          <p class="uppercase text-sweetPink-400 text-xs tracking-widest mb-4">FEATURES</p>
          <h1 class="font-heading text-white text-4xl md:text-5xl font-bold mb-4">Easy tracking of documents for your business</h1>
          <p class="text-white text-opacity-70 text-lg max-w-xl mb-20">Our software provides intuitive document tracking solutions, allowing you to focus on growing your business rather than manually processing one document at a time.</p>
          <div class="rounded-2xl border border-white border-opacity-20 py-6 pr-6 pl-6 md:pl-14 mb-10">
            <div class="flex flex-wrap items-center -m-4 mb-6">
              <div class="w-full lg:w-1/3 p-4">
                <h2 class="font-heading text-white text-4xl font-bold max-w-xs">QR code Integration</h2>
              </div>
              <div class="w-full lg:w-2/3 p-4">
                <div class="rounded-xl px-6 md:px-12 py-14 bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900"><img class="rounded-xl object-cover w-full h-full" style="height:288px" src="images/QR.jpg" alt=""/></div>
              </div>
            </div>
          </div>
          <div class="rounded-2xl border border-white border-opacity-20 py-6 pr-6 pl-6 md:pl-14 mb-10">
            <div class="flex flex-wrap items-center -m-4">
              <div class="w-full lg:w-1/3 p-4">
                <h2 class="font-heading text-white text-4xl font-bold max-w-xs">Document Tracking</h2>
              </div>
              <div class="w-full lg:w-2/3 p-4">
                <div class="rounded-xl px-6 md:px-12 py-14 bg-gradient-to-t from-pink-300 via-pink-700 to-darkPink-900"><img class="rounded-xl object-cover w-full h-full" style="height:288px" src="images/DT.jpg" alt=""/></div>
              </div>
            </div>
          </div>
          <div class="rounded-2xl border border-white border-opacity-20 py-6 pr-6 pl-6 md:pl-14 mb-10">
            <div class="flex flex-wrap items-center -m-4">
              <div class="w-full lg:w-1/3 p-4">
                <h2 class="font-heading text-white text-4xl font-bold max-w-xs">System Notification</h2>
              </div>
              <div class="w-full lg:w-2/3 p-4">
                <div class="rounded-xl px-6 md:px-12 py-14 bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900"><img class="rounded-xl object-cover w-full h-full" style="height:288px" src="images/LT.jpg" alt=""/></div>
              </div>
            </div>
          </div>
      </section>
      <section class="bg-darkPink-900 py-32">
        <div class="container mx-auto px-4">
          <h1 class="font-heading text-center text-white text-4xl md:text-5xl font-bold mb-20">How it works</h1><!--img class="rounded-3xl w-full object-cover mb-16" style="height:600px" src="template-assets/images/how-it-works/illustration-pink.png" alt=""/-->
          <div class="flex flex-wrap -m-4">
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">1</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">QR Integration</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">An office creates a QR code to be used for tracking the document.</p>
            </div>
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">2</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">Assigning an Office.</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">Assign an office to where the document should be sent to.</p>
            </div>
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">3</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">QR Scanning</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">Receiving offices scans document QR code.</p>
            </div>
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">4</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">System Notification</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">System notifies the registered users where the document was last scanned.</p>
            </div>
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">5</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">Receiving office makes a decision.</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">Receiving office decides rather returning the document by delivery or for pick up.</p>
            </div>
            <div class="w-full md:w-1/3 p-4">
              <div class="flex flex-wrap gap-2 mb-2">
                <div class="bg-gradient-to-b from-pink-300 via-pink-700 to-darkPink-900 rounded-full p-px w-6 h-6">
                  <div class="bg-pinkSecondary-900 rounded-full p-1">
                    <p class="text-white text-center text-xs font-semibold">6</p>
                  </div>
                </div>
                <p class="text-white text-lg font-semibold">Document in its final state.</p>
              </div>
              <p class="text-white text-opacity-70 max-w-xs">Document is recieved by the creator.</p>
            </div>
          </div>
        </div>
      </section>
      <section class="bg-darkPink-900 pt-20 pb-44" x-data="{ activeSlide: 1, slideCount: 3 }">
        <div class="container mx-auto px-4">
          <div class="max-w-3xl mx-auto"><img class="mx-auto mb-10" src="template-assets/images/testimonials/quote.svg" alt=""/>
            <div class="overflow-hidden">
              <div class="whitespace-nowrap transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + (activeSlide - 1) * 100.5 + '%)'">
                <div class="inline-block w-full whitespace-normal">
                  <h1 class="font-heading text-center text-white text-3xl md:text-5xl font-semibold mb-10 max-w-md md:max-w-3xl mx-auto"><span>We’re </span>                                  <span class="text-pink-500">very happy</span>                                  <span>that they created this amazing Document Tracking software.</span></h1>
                </div>
                <div class="inline-block w-full whitespace-normal">
                  <h1 class="font-heading text-center text-white text-3xl md:text-5xl font-semibold mb-10 max-w-md md:max-w-3xl mx-auto"><span>Excited to</span>  
                                                      <span class="text-pink-500">Announce the Arrival</span>                                  <span>of This Fantastic Document Tracking Software.</span>
                  </h1>
                </div>
                <div class="inline-block w-full whitespace-normal">
                  <h1 class="font-heading text-center text-white text-3xl md:text-5xl font-semibold mb-10 max-w-md md:max-w-3xl mx-auto"><span>Delighted to</span>                                  <span class="text-pink-500">Introduce This</span>                                  <span>Exceptional Document Tracking Software Creation.</span></h1>
                </div>
              </div>
            </div>
            <div class="w-px h-20 mx-auto bg-white bg-opacity-20 mb-6"></div>
            <div class="flex justify-between gap-4 mb-8 sm:mb-0">
              <div class="hidden sm:block"><a class="group relative inline-block p-0.5 font-semibold overflow-hidden rounded-full shadow-pink hover:shadow-none focus:shadow-none" href="#" x-on:click.prevent="activeSlide = activeSlide &gt; 1 ? activeSlide - 1 : slideCount">
                  <div class="absolute inset-0 bg-gradient-to-b from-pink-400 to-pink-500 rounded-full"></div>
                  <div class="relative z-50 flex items-center py-2 px-4 w-14 h-14 bg-pink-500 group-hover:bg-pink-600 group-focus:bg-pink-600 rounded-full transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none">
                      <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="white" style="stroke:white;stroke-opacity:1;" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </div></a></div>
              <div class="overflow-hidden">
                <div class="whitespace-nowrap transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + (activeSlide - 1) * 100.5 + '%)'">
                  <div class="inline-block w-full whitespace-normal">
                    <div class="rounded-full mx-auto bg-white bg-opacity-10 py-2 pl-2 pr-8 flex gap-4 w-60"><img class="h-12" src="images/darkin.png" alt=""/>
                      <div>
                        <p class="text-white font-semibold">George Marc</p>
                        <p class="text-gray-400 text-sm">Founder of AIME Inc.</p>
                      </div>
                    </div>
                  </div>
                  <div class="inline-block w-full whitespace-normal">
                    <div class="rounded-full mx-auto bg-white bg-opacity-10 py-2 pl-2 pr-8 flex gap-4 w-60"><img class="h-12" src="images/jas.jpg" alt=""/>
                      <div>
                        <p class="text-white font-semibold">Dong Dobryk</p>
                        <p class="text-gray-400 text-sm">Founder & CEO</p>
                      </div>
                    </div>
                  </div>
                  <div class="inline-block w-full whitespace-normal">
                    <div class="rounded-full mx-auto bg-white bg-opacity-10 py-2 pl-2 pr-8 flex gap-4 w-60"><img class="h-12" src="images/ford.png" alt=""/>
                      <div>
                        <p class="text-white font-semibold">Gren Graz</p>
                        <p class="text-gray-400 text-sm">Head of Technology</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="hidden sm:block"><a class="group relative inline-block p-0.5 font-semibold overflow-hidden rounded-full shadow-pink hover:shadow-none focus:shadow-none" href="#" x-on:click.prevent="activeSlide = activeSlide &lt; slideCount ? activeSlide + 1 : 1">
                  <div class="absolute inset-0 bg-gradient-to-b from-pink-400 to-pink-500 rounded-full"></div>
                  <div class="relative z-50 flex items-center py-2 px-4 w-14 h-14 bg-pink-500 group-hover:bg-pink-600 group-focus:bg-pink-600 rounded-full transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none">
                      <path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="white" style="stroke:white;stroke-opacity:1;" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </div></a></div>
            </div>
            <div class="flex sm:hidden justify-center gap-4"><a class="group relative inline-block p-0.5 font-semibold overflow-hidden rounded-full shadow-pink hover:shadow-none focus:shadow-none" href="#" x-on:click.prevent="activeSlide = activeSlide &gt; 1 ? activeSlide - 1 : slideCount">
                <div class="absolute inset-0 bg-gradient-to-b from-pink-400 to-pink-500 rounded-full"></div>
                <div class="relative z-50 flex items-center py-2 px-4 w-14 h-14 bg-pink-500 group-hover:bg-pink-600 group-focus:bg-pink-600 rounded-full transition duration-200">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none">
                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="white" style="stroke:white;stroke-opacity:1;" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </div></a>                      <a class="group relative inline-block p-0.5 font-semibold overflow-hidden rounded-full shadow-pink hover:shadow-none focus:shadow-none" href="#" x-on:click.prevent="activeSlide = activeSlide &lt; slideCount ? activeSlide + 1 : 1">
                <div class="absolute inset-0 bg-gradient-to-b from-pink-400 to-pink-500 rounded-full"></div>
                <div class="relative z-50 flex items-center py-2 px-4 w-14 h-14 bg-pink-500 group-hover:bg-pink-600 group-focus:bg-pink-600 rounded-full transition duration-200">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none">
                    <path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="white" style="stroke:white;stroke-opacity:1;" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </div></a></div>
          </div>
        </div>
      </section>
      <section class="relative bg-[#7b1f3a] py-32 overflow-hidden">
        <!-- Envoyr.jpg as background overlay -->
        <img src="images/envoyr (3).jpg" alt="Envoyr Overlay" class="absolute inset-0 w-full h-full object-cover opacity-10 z-0" />
      
        <!-- Gradient overlay for better contrast -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#7b1f3a] via-transparent to-transparent z-10"></div>
      
        <div class="relative z-20 container mx-auto px-4">
          <div class="rounded-3xl w-full px-10 md:px-6 py-6 relative">
            <div class="flex flex-col justify-center items-start">
              <h1 class="font-heading rounded-2xl text-white text-4xl md:text-3xl font-bold mb-6 max-w-xl md:max-w-xl leading-snug">
                Want to have an easier way of document tracking?
              </h1>
              <div class="flex items-center py-5 px-7 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full transition duration-200">
                <a class="text-[#7b1f3a] font-semibold text-sm" href="https://www.envoyr.store">Visit Envoyr</a>
              </div>
            </div>
          </div>
        </div>
      </section>      
      <section class="bg-darkPink-900 py-20">
        <div class="container mx-auto px-4">
          <div class="pb-20 border-b border-white border-opacity-30 mb-10">
            <div class="flex flex-wrap justify-between -m-4">
              <div class="w-full lg:w-1/6 p-4"><h1 class="text-5xl font-bold text-white">Envoyr</h1></div>
              <div class="w-full sm:w-1/2 lg:w-1/6 p-4">
                <ul class="flex flex-col gap-6">
                  <li><a class="text-white hover:text-opacity-70 text-xl transition duration-200" href="#">About</a></li>
                  <li><a class="text-white hover:text-opacity-70 text-xl transition duration-200" href="#">Contact Us</a></li>
                  <li><a class="text-white hover:text-opacity-70 text-xl transition duration-200" href="#">Privacy Policy</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="flex justify-between items-center flex-wrap gap-6">
            <p class="text-white">© ENVOYR. All rights reserved.</p>
            <div class="flex flex-wrap gap-6"><a href="#"><img src="template-assets/images/logos/twitter-x-logo.svg" alt=""/></a>        <a href="#"><img src="template-assets/images/logos/linkedin-logo.svg" alt=""/></a>        <a href="#"><img src="template-assets/images/logos/instagram-logo.svg" alt=""/></a>        <a href="#"><img src="template-assets/images/logos/facebook-logo.svg" alt=""/></a></div>
          </div>
        </div>
      </section>
    </div>
  </body>
</html>