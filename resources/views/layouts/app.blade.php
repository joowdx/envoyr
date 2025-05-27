<!DOCTYPE html>
<html lang="en" class="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Envoyr</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
      rel="stylesheet"
    />

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
            },
          },
        },
      };
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
        0% {
          transform: translateX(0);
        }
        50% {
          transform: translateX(-25px);
        }
        100% {
          transform: translateX(0);
        }
      }

      .animated-wave {
        animation: waveAnimation 8s ease-in-out infinite;
      }

      header {
        position: sticky;
        top: 0;
        z-index: 50;
      }

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
        color: rgb(0, 0, 0) !important;
      }

      .light .text-primary {
        color: #c83ebf !important;
      }

      .light .text-white {
        color: rgb(9, 9, 9) !important;
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