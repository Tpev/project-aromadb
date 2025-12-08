<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google tag (gtag.js) -->
    <!-- Vos scripts Google Analytics ici -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Dynamic Meta Description -->
    <meta name="description" content="@yield('meta_description', 'Informations fiables et vérifiées sur les huiles essentielles, les huiles végétales, les tisanes, et bien plus encore!')">

    <!-- Dynamic Page Title -->
    <title>@yield('title', config('app.name', 'AromaMade'))</title>
	

    @yield('structured_data')
	@yield('meta_og')
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
	<style>
      /* --- App-only tabbar --- */
      .am-app .app-tabbar { display:flex; }

      .app-tabbar {
        display:none;
        height:58px;
        padding-bottom:env(safe-area-inset-bottom);
      }

      .app-tabbar .tab-item {
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:4px;
        padding:8px 10px;
        flex:1;
        color:#6b7280;
        text-decoration:none;
      }
      .app-tabbar .tab-item.active { color:#111827; font-weight:600; }

      .am-app body {
        padding-bottom:calc(58px + env(safe-area-inset-bottom));
      }

      .no-tabbar .app-tabbar {
        display:none!important;
      }
    </style>
</head>
<body class="font-sans antialiased bg-[#f5f5f5]">
    <div class="min-h-screen">
        @if(! request()->is('*webrtc*'))
            @if(auth()->check() && auth()->user()->is_therapist)
                @include('layouts.therapistnavigation')
            @else
                @include('layouts.navigation')
            @endif
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>


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

    <!-- Notifications Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationButton = document.getElementById('notificationButton');
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const markAllAsReadForm = document.getElementById('markAllAsReadForm');

            // Toggle dropdown visibility
            if (notificationButton) {
                notificationButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    notificationsDropdown.classList.toggle('hidden');
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (notificationButton && !notificationButton.contains(e.target) && !notificationsDropdown.contains(e.target)) {
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
                        notificationsDropdown.querySelector('.max-h-60').innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Aucune nouvelle notification</div>';
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
                    const badge = notificationButton ? notificationButton.querySelector('.badge') : null;
                    if (data.unreadCount > 0) {
                        if (badge) {
                            badge.textContent = data.unreadCount;
                        } else if (notificationButton) {
                            const newBadge = document.createElement('span');
                            newBadge.classList.add('absolute', 'top-0', 'right-0', 'inline-flex', 'items-center', 'justify-center', 'px-2', 'py-1', 'text-xs', 'font-bold', 'leading-none', 'text-white', 'bg-red-600', 'rounded-full', 'transform', 'translate-x-1/2', '-translate-y-1/2');
                            newBadge.textContent = data.unreadCount;
                            notificationButton.appendChild(newBadge);
                        }

                        // Optionally, update the dropdown list with new notifications
                        if (notificationsDropdown && notificationsDropdown.querySelector('.max-h-60').children.length > 0) {
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
                        if (notificationsDropdown) {
                            notificationsDropdown.querySelector('.max-h-60').innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Aucune notification</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
            }, 30000); // 30 seconds
        });
    </script>
<script>
  (function () {
    try {
      const isApp =
        typeof window.Capacitor !== 'undefined' &&
        typeof window.Capacitor.getPlatform === 'function' &&
        window.Capacitor.getPlatform() !== 'web';
      if (isApp) document.documentElement.classList.add('am-app');
    } catch (_) {}
  })();
</script>
@include('partials.app-tabbar')

    @stack('scripts')
</body>
</html>
