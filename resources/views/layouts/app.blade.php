<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16709768048"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'AW-16709768048');
        </script>

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-0E0C9TV45Z"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-0E0C9TV45Z');
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}" />

        <!-- Dynamic Meta Description -->
        <meta name="description" content="@yield('meta_description', ' Informations fiables et vérifiées sur les huiles essentielles, les huiles végétales, les tisanes, et bien plus encore!')">
        
        <!-- Dynamic Page Title -->
        <title>@yield('title', config('app.name', 'AromaMade'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
		@stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
@if(! request()->is('*webrtc*'))
    @if(auth()->check() && auth()->user()->is_therapist)
        @include('layouts.therapistnavigation')
    @else
        @include('layouts.navigation')
    @endif
@endif

@stack('scripts')
@if(! request()->is('*webrtc*'))
    @guest
        <div class="fixed bottom-4 right-4 z-50 text-center">
            <p class="text-gray-800 font-semibold mb-2">Partager cette page :</p>
            <div class="flex space-x-3">
                <!-- Facebook Share -->
                <a href="javascript:void(0);" onclick="shareToFacebook()" class="bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition" title="Partager sur Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>

                <!-- Twitter Share -->
                <a href="javascript:void(0);" onclick="shareToTwitter()" class="bg-blue-400 text-white p-3 rounded-full shadow-lg hover:bg-blue-500 transition" title="Partager sur Twitter">
                    <i class="fab fa-twitter"></i>
                </a>

                <!-- WhatsApp Share -->
                <a href="javascript:void(0);" onclick="shareToWhatsApp()" class="bg-green-500 text-white p-3 rounded-full shadow-lg hover:bg-green-600 transition" title="Partager sur WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    @endguest
@endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
			
			<!-- Usetiful script start -->
            <script>
(function (w, d, s) {
    var a = d.getElementsByTagName('head')[0];
    var r = d.createElement('script');
    r.async = 1;
    r.src = s;
    r.setAttribute('id', 'usetifulScript');
    r.dataset.token = "ebdbb5d70d492668e3cf57ea505ef82f";
                        a.appendChild(r);
  })(window, document, "https://www.usetiful.com/dist/usetiful.js");</script>

<!-- Usetiful script end -->

        </div>

        <!-- Include the Footer -->
		@if(! request()->is('*webrtc*'))
        @include('layouts.footer')
		@endif

		
        <!-- Social Share JavaScript -->
        <script>
            function shareToFacebook() {
                const url = encodeURIComponent(window.location.href);
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
            }

            function shareToTwitter() {
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent(document.title);
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
            }

            function shareToWhatsApp() {
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent(document.title);
                const whatsappURL = `https://api.whatsapp.com/send?text=${text}%20${url}`;
                window.open(whatsappURL, '_blank');
            }
        </script>

        <!-- Add FontAwesome CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </body>
</html>
