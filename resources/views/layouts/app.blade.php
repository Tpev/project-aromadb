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
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

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
    <!-- Add this script at the bottom of your navbar Blade template -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationButton = document.getElementById('notificationButton');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const markAllAsReadForm = document.getElementById('markAllAsReadForm');

        // Toggle dropdown visibility
        notificationButton.addEventListener('click', function (e) {
            e.preventDefault();
            notificationsDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!notificationButton.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });

        // Handle "Mark all as read" form submission via AJAX
        if (markAllAsReadForm) {
            markAllAsReadForm.addEventListener('submit', function (e) {
                e.preventDefault();
                fetch(markAllAsReadForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    // Remove unread badge
                    const badge = notificationButton.querySelector('.badge');
                    if (badge) {
                        badge.remove();
                    }
                    // Update notifications dropdown
                    notificationsDropdown.querySelector('.max-h-60').innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">No new notifications</div>';
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
            });
        }

        // Polling for new notifications every 30 seconds
        setInterval(function () {
            fetch('{{ route("notifications.fetch") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                const badge = notificationButton.querySelector('.badge');
                if (data.unreadCount > 0) {
                    if (badge) {
                        badge.textContent = data.unreadCount;
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.classList.add('absolute', 'top-0', 'right-0', 'inline-flex', 'items-center', 'justify-center', 'px-2', 'py-1', 'text-xs', 'font-bold', 'leading-none', 'text-white', 'bg-red-600', 'rounded-full', 'transform', 'translate-x-1/2', '-translate-y-1/2');
                        newBadge.textContent = data.unreadCount;
                        notificationButton.appendChild(newBadge);
                    }

                    // Optionally, update the dropdown list with new notifications
                    if (notificationsDropdown.querySelector('.max-h-60').children.length > 0) {
                        let notificationsHtml = '';
                        data.notifications.forEach(function (notification) {
                            notificationsHtml += `
                                <a href="${notification.data.url}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    ${notification.data.message}
                                    <br>
                                    <small class="text-gray-500">${notification.data.appointment_date}</small>
                                </a>
                            `;
                        });
                        notificationsDropdown.querySelector('.max-h-60').innerHTML = notificationsHtml;
                    }
                } else {
                    if (badge) {
                        badge.remove();
                    }
                    notificationsDropdown.querySelector('.max-h-60').innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Aucune notification</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
        }, 30000); // 30 seconds
    });
</script>

	
	</body>
</html>
