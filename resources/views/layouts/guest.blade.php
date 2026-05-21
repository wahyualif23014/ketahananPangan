<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SIKAP PRESISI - POLDA JAWA TIMUR</title>
        <link rel="icon" type="image/png" href="{{ asset('logo-sikap.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('logo-sikap.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- NProgress for smooth page transitions -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
        <script>
            NProgress.configure({ showSpinner: false, minimum: 0.1, speed: 400 });
            NProgress.start();
        </script>

        <style>
            /* Mencegah flickering saat elemen Alpine.js dimuat */
            [x-cloak] { display: none !important; }
            
            /* Haluskan scrollbar untuk vibe modern */
            ::-webkit-scrollbar { width: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

            /* NProgress custom color */
            #nprogress .bar { background: #10b981 !important; height: 3px !important; }
            #nprogress .peg { box-shadow: 0 0 10px #10b981, 0 0 5px #10b981 !important; }
            #nprogress .spinner-icon { border-top-color: #10b981 !important; border-left-color: #10b981 !important; }

            /* Smooth Page Transition */
            .page-transition-enter { animation: fadeInPage 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
            .page-transition-leave { opacity: 0.5; transform: scale(0.995); pointer-events: none; transition: all 0.3s ease-in-out; }
            @keyframes fadeInPage { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased overflow-x-hidden bg-[#f8fafc]">
        
        <div id="main-content" class="min-h-screen selection:bg-emerald-500 selection:text-white page-transition-enter">
            {{ $slot }}
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => NProgress.done());

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript') || link.getAttribute('target') === '_blank') return;
                if (link.hasAttribute('download') || link.origin !== window.location.origin) return;
                if (link.pathname === window.location.pathname && link.search === window.location.search) return;

                NProgress.start();
                document.getElementById('main-content')?.classList.add('page-transition-leave');
            });

            document.addEventListener('submit', function(e) {
                if (e.defaultPrevented) return;
                NProgress.start();
                document.getElementById('main-content')?.classList.add('page-transition-leave');
            });

            window.addEventListener('pageshow', function(e) {
                NProgress.done();
                document.getElementById('main-content')?.classList.remove('page-transition-leave');
            });
        </script>
    </body>
</html>