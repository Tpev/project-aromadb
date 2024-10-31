<!-- resources/views/webrtc.blade.php -->

<x-app-layout>
    @push('styles')
        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <!-- Custom Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
        <!-- Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
        <!-- Custom Styles for Loader, Chat, and Notifications -->
        <style>
       /* Font Family for Roboto */
            .font-roboto {
                font-family: 'Roboto', sans-serif;
            }

            /* Spinner Animation */
            .loader {
                border-top-color: #647a0b; /* Updated color */
                animation: spin 1s ease-in-out infinite;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            /* Chat Panel Scrollbar */
            #chatMessages::-webkit-scrollbar {
                width: 6px;
            }

            #chatMessages::-webkit-scrollbar-thumb {
                background-color: #ccc;
                border-radius: 3px;
            }

            /* Notification Badge */
            .notification-badge {
                position: absolute;
                top: -2px;
                right: -2px;
                background-color: red;
                color: white;
                border-radius: 50%;
                width: 16px;
                height: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                pointer-events: none;
                visibility: hidden; /* Initially hidden */
            }

            .notification-badge.visible {
                visibility: visible; /* Use this class to make it visible */
            }

            .notification-badge-mobile {
                width: 12px;
                height: 12px;
                font-size: 8px;
            }

            /* Message Styling */
            .message-sender {
                background-color: #DCF8C6;
                align-self: flex-end;
                border-radius: 10px;
                padding: 5px 10px;
                margin: 5px 0;
                max-width: 80%;
            }

            .message-receiver {
                background-color: #FFFFFF;
                align-self: flex-start;
                border-radius: 10px;
                padding: 5px 10px;
                margin: 5px 0;
                max-width: 80%;
            }

            /* Highlight Chat Panel on New Message */
            .highlight {
                animation: highlightAnimation 1s ease-in-out;
            }

            @keyframes highlightAnimation {
                0% { background-color: #fffacd; }
                100% { background-color: #ffffff; }
            }

            /* Chat Toggle Button */
            #toggleChat:hover {
                background-color: #854f38; /* Updated color */
            }

            /* Close Button in Chat Panel */
            #closeChat {
                position: absolute;
                top: 10px;
                right: 10px;
                background: transparent;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: #6b7280;
            }

            #closeChat:hover {
                color: #374151;
            }

            /* Ensure Chat Messages are Flex Container */
            #chatMessages {
                display: flex;
                flex-direction: column;
            }

            .invisible {
                visibility: hidden;
            }

            .opacity-0 {
                opacity: 0;
            }

            .transition-opacity {
                transition: opacity 0.3s ease;
            }

            .opacity-100 {
                opacity: 1;
            }

            /* Remote Video Background */
            #remoteVideo {
                object-fit: contain; /* Maintain aspect ratio while scaling down */
                width: 100%;         /* Full width of the container */
                height: 100%;        /* Full height of the container */
                position: absolute;   /* Position it absolutely to cover the background */
                top: 0;              /* Align to the top */
                left: 0;             /* Align to the left */
                z-index: 0;         /* Ensure it's in the background */
            }
        </style>
    @endpush

    @section('meta_description')
        Rejoignez notre salle de vidéoconférence sécurisée et profitez d'une expérience de communication fluide et professionnelle.
    @endsection

    <!-- WebRTC Interface -->
    <div class="min-h-screen flex flex-col bg-gray-100 font-roboto relative">
        <!-- Remote Video as Background -->
        <video id="remoteVideo" autoplay class="absolute top-0 left-0 w-full h-full object-cover z-0"></video>

        <!-- Optional Overlay to Darken Remote Video -->
        <div class="absolute top-0 left-0 w-full h-full bg-black opacity-0 z-10"></div>

        <!-- Loading Spinner Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-50 z-20">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-24 w-24 mb-4"></div>
            <p class="text-white text-lg">En attente de l'autre participant...</p>
        </div>

        <!-- Header -->
        <header class="bg-[#647a0b] text-white p-4 shadow-md flex justify-between items-center relative z-30">
            <h1 class="text-xl font-semibold">Salle : {{ ucfirst($room) }}</h1>
            <span id="connectionStatus" class="text-sm text-yellow-500">Connexion...</span>
        </header>

        <!-- Main Content (Local Video, Control Panel, Chat Panel) -->
        <main class="flex-1 flex flex-col md:flex-row items-end md:items-start justify-end p-4 relative z-30 space-y-4 md:space-y-0 md:space-x-4">
            <!-- Local Video -->
            <div class="relative w-32 h-24 md:w-40 md:h-32">
                <video id="localVideo" autoplay muted class="rounded-lg shadow-lg w-full h-full object-cover"></video>
                <span class="absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-1 py-0.5 rounded">Vous</span>
            </div>

            <!-- Control Panel -->
            <div class="flex space-x-4">
                <!-- Toggle Audio Button -->
                <button id="toggleAudio" aria-label="Couper ou activer le son" class="bg-[#647a0b] hover:bg-[#854f38] text-white p-4 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i id="audioIcon" class="fas fa-microphone"></i>
                </button>

                <!-- Toggle Video Button -->
                <button id="toggleVideo" aria-label="Couper ou activer la vidéo" class="bg-[#647a0b] hover:bg-[#854f38] text-white p-4 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i id="videoIcon" class="fas fa-video"></i>
                </button>

                <!-- Full-Screen Toggle Button -->
                <button id="toggleFullScreen" aria-label="Mode plein écran" class="bg-[#647a0b] hover:bg-[#854f38] text-white p-4 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i class="fas fa-expand"></i>
                </button>

                <button id="toggleChat" aria-label="Basculer le chat" class="bg-[#647a0b] hover:bg-[#854f38] text-white p-3 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 relative">
                    <i class="fas fa-comments"></i>
                    <span id="chatNotificationBadge" class="notification-badge invisible">0</span> <!-- Initially hidden -->
                </button>

                <!-- End Call Button -->
                <button id="endCall" aria-label="Terminer l'appel" class="bg-red-500 hover:bg-red-600 text-white p-4 rounded-full focus:outline-none focus:ring-2 focus:ring-red-400">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>

            <!-- Chat Panel -->
            <div id="chatPanel" class="absolute top-20 right-4 w-64 bg-white bg-opacity-95 p-4 rounded-lg shadow-lg hidden md:flex flex-col z-40">
                <button id="closeChat" aria-label="Fermer le chat">
                    <i class="fas fa-times"></i>
                </button>
                <h2 class="text-lg font-semibold mb-2">Chat</h2>
                <div id="chatMessages" class="overflow-y-scroll h-40 mb-2 border rounded p-2 bg-gray-100">
                    <!-- Messages will appear here -->
                </div>
                <input type="text" id="chatInput" placeholder="Tapez un message..." class="w-full p-2 border rounded" />
            </div>
        </main>

        <!-- Chat Toggle Button for All Devices -->

    </div>

    @push('scripts')
        <!-- SimplePeer & Axios Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/simple-peer@9/simplepeer.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

        <!-- Audio Notification -->

        <!-- JavaScript for WebRTC and Enhancements -->
        <script>
document.addEventListener('DOMContentLoaded', function () {
    let localStream;
    let peer = null;
    let audioEnabled = true;
    let videoEnabled = true;
    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const connectionStatus = document.getElementById('connectionStatus');

    // Control Buttons
    const toggleAudioButton = document.getElementById('toggleAudio');
    const toggleVideoButton = document.getElementById('toggleVideo');
    const endCallButton = document.getElementById('endCall');
    const toggleFullScreenButton = document.getElementById('toggleFullScreen');

    // Chat Elements
    const chatPanel = document.getElementById('chatPanel');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatNotificationBadge = document.getElementById('chatNotificationBadge');
    const toggleChatButton = document.getElementById('toggleChat');
    const closeChatButton = document.getElementById('closeChat');

    // Audio Notification Element
    const notificationSound = document.getElementById('notificationSound');

    // Room and Sender ID
    const room = @json($room);
    const senderId = generateUniqueId();

    console.log(`WebRTC initialisé pour la salle : ${room}, senderId : ${senderId}`);

    // ICE Servers Configuration
    const iceServers = [
        { urls: 'stun:turn.aromamade.com:3478' },
        {
            urls: 'turn:turn.aromamade.com:3478?transport=udp',
            username: 'userprod',
            credential: 'blablablatesttest',
        },
        // Add more TURN servers if needed
    ];

    // Determine if this client is the initiator based on URL hash
    const isInitiator = location.hash === '#1';
    console.log(`Le pair est initiateur : ${isInitiator}`);

    // Retry Counters
    let offerRetryCount = 0;
    const MAX_OFFER_RETRIES = 100;

    let answerRetryCount = 0;
    const MAX_ANSWER_RETRIES = 100;

    // Initialize Media
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            localStream = stream;
            localVideo.srcObject = stream;
            console.log('Flux local acquis.');

            initializePeerConnection(isInitiator, stream);

            // Start monitoring signaling data
            monitorSignaling();
        })
        .catch(error => {
            console.error('Erreur d\'accès aux appareils médias.', error);
            alert('Impossible d\'accéder à la caméra et au microphone. Veuillez vérifier les autorisations.');
            // Hide loading spinner on error
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden');
            }
        });

    // Generate Unique ID
    function generateUniqueId() {
        return Math.random().toString(36).substr(2, 9) + '-' + Date.now().toString(36);
    }

    // Initialize Peer Connection
    function initializePeerConnection(initiator, stream) {
        createPeer(initiator, stream);
        
        if (!initiator) {
            console.log('Non-initiateur : Tentative de récupération de l\'offre.');
            setTimeout(getOffer, 1000);
        }
    }

    // Create WebRTC Peer
    function createPeer(initiator, stream) {
        peer = new SimplePeer({
            initiator: initiator,
            trickle: false,
            stream: stream,
            config: { iceServers }
        });

        // Handle signaling data
        peer.on('signal', (data) => {
            const type = data.type === 'offer' ? 'offer' : 'answer';
            console.log(`Envoi ${type} :`, data);

            axios.post('/webrtc/signaling', {
                type: type,
                data: JSON.stringify(data),
                room: room,
                senderId: senderId
            }).then(response => {
                console.log('Données de signalisation envoyées :', response.data);

                if (type === 'offer') {
                    console.log('Initiateur : En attente de réponse.');
                    setTimeout(checkForAnswer, 1000);
                }
            }).catch(err => {
                console.error('Erreur lors de l\'envoi des données de signalisation :', err);
            });
        });

        // Receive remote stream
        peer.on('stream', (stream) => {
            console.log('Flux distant reçu :', stream);
            remoteVideo.srcObject = stream;
            remoteVideo.play().then(() => {
                console.log('Vidéo distante en cours de lecture.');
                // Hide loading spinner
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                    console.log('Overlay de chargement caché (flux).');
                }
            }).catch(err => {
                console.error('Erreur lors de la lecture de la vidéo distante :', err);
            });
        });

        // Connection established
        peer.on('connect', () => {
            console.log('Connexion de pair établie.');
            if (connectionStatus) {
                connectionStatus.innerText = 'Connecté';
                connectionStatus.classList.remove('text-red-500', 'text-yellow-500');
                connectionStatus.classList.add('text-green-500');
            }
            // Hide loading spinner if visible
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden');
                console.log('Overlay de chargement caché (connexion).');
            }
        });

peer.on('error', (err) => {
    console.error('Erreur de connexion de pair :', err);
    console.log('Peer error event triggered');
    
    if (isInitiator) {
        alert('Une erreur est survenue avec la connexion. Rechargement de la page.');
        console.log('Initiator detected. Reloading the page...');
        // Adding a slight delay to ensure the alert is displayed
        setTimeout(() => {
            window.location.reload();
        }, 500);
    } else {
        alert('Une erreur est survenue avec la connexion. Veuillez réessayer.');
        console.log('Non-initiator detected. Cleaning up...');
        cleanupPeer();
        
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
            console.log('Loading overlay hidden due to error.');
        }
    }
});



peer.on('close', () => {
    console.log('Peer connection closed.');
    
    if (isInitiator) {
        alert('L\'appel a été terminé. Rechargement de la page pour une nouvelle connexion.');
        window.location.reload(); // Automatically reload the page
    } else {
        // Non-initiator behavior: clean up UI, etc.
        if (connectionStatus) {
            connectionStatus.innerText = 'Déconnecté';
            connectionStatus.classList.remove('text-green-500');
            connectionStatus.classList.add('text-red-500');
        }
        alert('L\'appel a été terminé.');
        cleanupPeer();
        if (loadingOverlay) {
            loadingOverlay.classList.remove('hidden');
            console.log('Loading overlay displayed for a new connection.');
        }
        // Clear signaling data on server
        axios.post('/webrtc/clear-signaling', {
            room: room,
            senderId: senderId
        }).then(response => {
            console.log('Signaling data cleared.');
        }).catch(err => {
            console.error('Error clearing signaling data:', err);
        });
    }
});

        // Handle incoming data (Chat messages)
        peer.on('data', (data) => {
            const message = data.toString();
            console.log('Message reçu :', message);
            appendMessage(`Participant : ${message}`, false);
            showNotification();
        });
    }

    // Cleanup Peer Connection
    function cleanupPeer() {
        if (peer) {
            peer.destroy();
            peer = null;
            console.log('Peer connection destroyed and reset.');
        }

        // Reset connection status
        if (connectionStatus) {
            connectionStatus.innerText = 'Déconnecté';
            connectionStatus.classList.remove('text-green-500');
            connectionStatus.classList.add('text-red-500');
        }

        // Reset retry counters
        resetRetryCounts();
        
        // Optionally reset other UI elements like chat, notifications, etc.
    }

    // Reset Retry Counters
    function resetRetryCounts() {
        offerRetryCount = 0;
        answerRetryCount = 0;
    }

    // Retrieve Offer from Signaling Server
    function getOffer() {
        if (offerRetryCount >= MAX_OFFER_RETRIES) {
            console.error('Nombre maximum de tentatives d\'offre atteint.');
            alert('Impossible d\'établir une connexion. Veuillez réessayer plus tard.');
            loadingOverlay.classList.add('hidden');
            return;
        }

        offerRetryCount++;
        console.log(`Récupération de l'offre depuis le serveur de signalisation. Tentative ${offerRetryCount}`);
        axios.get('/webrtc/get-offer', { params: { room: room } })
            .then(response => {
                const offer = response.data.offer;
                if (offer) {
                    console.log('Offre reçue :', offer);
                    peer.signal(offer);
                } else {
                    console.log('Aucune offre trouvée. Nouvelle tentative dans 2 secondes...');
                    setTimeout(getOffer, 2000);
                }
            })
            .catch(err => {
                console.error('Erreur lors de la récupération de l\'offre :', err);
                setTimeout(getOffer, 2000);
            });
    }

    // Check for Answer from Signaling Server
    function checkForAnswer() {
        if (answerRetryCount >= MAX_ANSWER_RETRIES) {
            console.error('Nombre maximum de tentatives de réponse atteint.');
            alert('Aucune réponse reçue. Veuillez vous assurer que l\'autre participant est en ligne.');
            loadingOverlay.classList.add('hidden');
            return;
        }

        answerRetryCount++;
        console.log(`Vérification de la réponse depuis le serveur de signalisation. Tentative ${answerRetryCount}`);
        axios.get('/webrtc/get-answer', { params: { room: room } })
            .then(response => {
                const answer = response.data.answer;
                if (answer) {
                    console.log('Réponse reçue :', answer);
                    peer.signal(answer);
                } else {
                    console.log('Aucune réponse trouvée pour le moment. Nouvelle tentative dans 2 secondes...');
                    setTimeout(checkForAnswer, 2000);
                }
            })
            .catch(err => {
                console.error('Erreur lors de la récupération de la réponse :', err);
                setTimeout(checkForAnswer, 2000);
            });
    }

    // Append Message to Chat with Timestamp and Styling
    function appendMessage(message, isSender = false) {
        const messageElement = document.createElement('p');
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        messageElement.innerText = `[${timestamp}] ${message}`;
        messageElement.classList.add(isSender ? 'message-sender' : 'message-receiver');
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Highlight chat panel for visual notification
        if (!isSender) {
            if (chatPanel.classList.contains('hidden')) {
                showNotification();
            }
            chatPanel.classList.add('highlight');
            setTimeout(() => {
                chatPanel.classList.remove('highlight');
            }, 1000);
        }
    }

    // Handle Chat Input
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const message = chatInput.value;
            if (message.trim() !== '' && peer && peer.connected) {
                peer.send(message);
                appendMessage(`Vous : ${message}`, true);
                chatInput.value = '';
            }
        }
    });

    // Control Panel Button Handlers
    if (toggleAudioButton && toggleVideoButton && endCallButton && toggleFullScreenButton) {
        // Toggle Audio
        toggleAudioButton.addEventListener('click', () => {
            audioEnabled = !audioEnabled;
            if (localStream && localStream.getAudioTracks().length > 0) {
                localStream.getAudioTracks()[0].enabled = audioEnabled;
                updateAudioIcon();
                console.log(`Audio ${audioEnabled ? 'activé' : 'désactivé'}.`);
            } else {
                console.warn('Aucun flux audio disponible.');
            }
        });

        function updateAudioIcon() {
            const audioIcon = document.getElementById('audioIcon');
            if (audioIcon) {
                if (audioEnabled) {
                    audioIcon.classList.remove('fa-microphone-slash');
                    audioIcon.classList.add('fa-microphone');
                } else {
                    audioIcon.classList.remove('fa-microphone');
                    audioIcon.classList.add('fa-microphone-slash');
                }
            } else {
                console.warn('Élément de l\'icône audio non trouvé.');
            }
        }

        // Toggle Video
        toggleVideoButton.addEventListener('click', () => {
            videoEnabled = !videoEnabled;
            if (localStream && localStream.getVideoTracks().length > 0) {
                localStream.getVideoTracks()[0].enabled = videoEnabled;
                updateVideoIcon();
                console.log(`Vidéo ${videoEnabled ? 'activée' : 'désactivée'}.`);
            } else {
                console.warn('Aucun flux vidéo disponible.');
            }
        });

        function updateVideoIcon() {
            const videoIcon = document.getElementById('videoIcon');
            if (videoIcon) {
                if (videoEnabled) {
                    videoIcon.classList.remove('fa-video-slash');
                    videoIcon.classList.add('fa-video');
                } else {
                    videoIcon.classList.remove('fa-video');
                    videoIcon.classList.add('fa-video-slash');
                }
            } else {
                console.warn('Élément de l\'icône vidéo non trouvé.');
            }
        }

        // Toggle Full-Screen Mode
        toggleFullScreenButton.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Erreur lors de la tentative d'activation du mode plein écran : ${err.message} (${err.name})`);
                });
            } else {
                document.exitFullscreen();
            }
        });

        // End Call
        endCallButton.addEventListener('click', () => {
            if (peer) {
                peer.destroy();
                console.log('Peer connection destroyed.');
            }
            // Clear signaling data on the server
            axios.post('/webrtc/clear-signaling', {
                room: room,
                senderId: senderId
            }).then(response => {
                console.log('Signaling data cleared.');
            }).catch(err => {
                console.error('Error clearing signaling data:', err);
            });
            // Redirect to home or another page
            window.location.href = '/';
        });
    } else {
        console.error('Un ou plusieurs boutons de contrôle manquent dans le DOM.');
    }

    // Chat Toggle Functionality
    toggleChatButton.addEventListener('click', () => {
        const isVisible = !chatPanel.classList.contains('invisible');
        if (isVisible) {
            chatPanel.classList.add('invisible', 'opacity-0');
            chatPanel.classList.remove('transition-opacity', 'duration-300'); // Remove transition for instant effect
            toggleChatButton.classList.remove('bg-[#854f38]');
            toggleChatButton.classList.add('bg-[#647a0b]');
            console.log('Panneau de chat fermé.');
        } else {
            chatPanel.classList.remove('invisible', 'opacity-0');
            chatPanel.classList.add('transition-opacity', 'duration-300'); // Add transition for smooth effect
            resetNotificationBadge();
            console.log('Panneau de chat ouvert.');
        }
    });

    // Close Chat from Chat Panel
    closeChatButton.addEventListener('click', () => {
        chatPanel.classList.add('invisible', 'opacity-0');
        toggleChatButton.classList.remove('bg-[#854f38]');
        toggleChatButton.classList.add('bg-[#647a0b]');
        console.log('Panneau de chat fermé via le bouton de fermeture.');
    });

    // Chat Notification System
    let notificationCount = 0;

    function showNotification() {
        // Increment notification count
        notificationCount += 1;
        updateNotificationBadge();

        // Play notification sound
        if (notificationSound) {
            notificationSound.play().catch(err => {
                console.error('Erreur lors de la lecture du son de notification :', err);
            });
        }

        console.log(`Nombre de notifications : ${notificationCount}`);
    }

    function updateNotificationBadge() {
        if (notificationCount > 0) {
            chatNotificationBadge.style.display = 'flex';
            chatNotificationBadge.innerText = notificationCount;
        } else {
            chatNotificationBadge.style.display = 'none';
        }
    }

    function resetNotificationBadge() {
        notificationCount = 0;
        updateNotificationBadge();
        console.log('Badge de notification réinitialisé.');
    }

    // Efficient Resource Management
    function cleanup() {
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        if (remoteVideo.srcObject) {
            remoteVideo.srcObject.getTracks().forEach(track => track.stop());
        }
        if (peer) {
            peer.destroy();
            peer = null;
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        cleanup();
        // Additionally, clear signaling data
        axios.post('/webrtc/clear-signaling', {
            room: room,
            senderId: senderId
        }).catch(err => {
            console.error('Error clearing signaling data on unload:', err);
        });
    });

    // Monitor Signaling for New Offers
    function monitorSignaling() {
        setInterval(() => {
            axios.get('/webrtc/get-offer', { params: { room: room } })
                .then(response => {
                    const offer = response.data.offer;
                    if (offer && !peer) { // Ensure no existing peer connection
                        console.log('Nouvelle offre reçue. Initialisation du pair.');
                        initializePeerConnection(false, localStream);
                        peer.signal(offer);
                    }
                })
                .catch(err => {
                    console.error('Erreur lors de la surveillance de l\'offre :', err);
                });
        }, 3000); // Check every 3 seconds
    }
});
        </script>
    @endpush

</x-app-layout>
