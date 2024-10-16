<!-- resources/views/webrtc.blade.php -->

<x-app-layout>
    @push('styles')
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
        <!-- Custom Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
    @endpush

    @section('meta_description')
        Rejoignez notre salle de conférence vidéo sécurisée et profitez d'une expérience de communication fluide et professionnelle.
    @endsection

    <!-- WebRTC Interface -->
    <div class="min-h-screen flex flex-col bg-gray-100 font-roboto">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center">
            <h1 class="text-xl font-semibold">Salle: {{ ucfirst($room) }}</h1>
            <span id="connectionStatus" class="text-sm">Connecté</span>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col md:flex-row items-center justify-center p-4">
            <!-- Video Streams -->
            <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-4">
                <!-- Local Video -->
                <div class="relative">
                    <video id="localVideo" autoplay muted class="rounded-lg shadow-lg w-64 h-48 object-cover"></video>
                    <span class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">Vous</span>
                </div>

                <!-- Remote Video -->
                <div class="relative">
                    <video id="remoteVideo" autoplay class="rounded-lg shadow-lg w-64 h-48 object-cover"></video>
                    <span class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">Participant</span>
                </div>
            </div>
        </main>

        <!-- Control Panel -->
        <footer class="bg-white p-4 shadow-inner">
            <div class="flex justify-center space-x-6">
                <!-- Mute/Unmute Audio Button -->
                <button id="toggleAudio" class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i id="audioIcon" class="fas fa-microphone"></i>
                </button>

                <!-- Mute/Unmute Video Button -->
                <button id="toggleVideo" class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <i id="videoIcon" class="fas fa-video"></i>
                </button>

                <!-- End Call Button -->
                <button id="endCall" class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-full focus:outline-none focus:ring-2 focus:ring-red-400">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </footer>
    </div>

@push('scripts')
    <!-- SimplePeer & Axios Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-peer@9/simplepeer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Your Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let localStream;
            let peer;
            let audioEnabled = true;
            let videoEnabled = true;
            const localVideo = document.getElementById('localVideo');
            const remoteVideo = document.getElementById('remoteVideo');

            const room = @json($room);
            const senderId = generateUniqueId();

            console.log(`WebRTC initialized for room: ${room}, senderId: ${senderId}`);

            // Define ICE servers
            const iceServers = [
                { urls: 'stun:stun.l.google.com:19302' },
                {
                    urls: 'turn:eu.relay.metered.ca:80',
                    username: '973cd534a917cf4aad94e78d',
                    credential: 'U0vCqXJ3Zj6GCso9',
                },
                // Add more TURN servers as needed
            ];

            // Determine if this client is the initiator based on URL hash
            const isInitiator = location.hash === '#1';
            console.log(`Peer is initiator: ${isInitiator}`);

            // Get user media
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    localStream = stream;
                    localVideo.srcObject = stream;
                    console.log('Local stream acquired.');

                    createPeer(isInitiator, stream);

                    // If not initiator, retrieve the offer
                    if (!isInitiator) {
                        console.log('Non-initiator: Attempting to retrieve offer.');
                        setTimeout(getOffer, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error accessing media devices.', error);
                    alert('Could not access camera and microphone. Please check permissions.');
                });

            function generateUniqueId() {
                return Math.random().toString(36).substr(2, 9) + '-' + Date.now().toString(36);
            }

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
                    console.log(`Sending ${type}:`, data);

                    axios.post('/webrtc/signaling', {
                        type: type,
                        data: JSON.stringify(data),
                        room: room,
                        senderId: senderId
                    }).then(response => {
                        console.log('Signaling data sent:', response.data);

                        if (type === 'offer') {
                            console.log('Initiator: Waiting for answer.');
                            setTimeout(checkForAnswer, 1000);
                        }
                    }).catch(err => {
                        console.error('Error sending signaling data:', err);
                    });
                });

                // Receive remote stream
                peer.on('stream', (stream) => {
                    console.log('Received remote stream:', stream);
                    remoteVideo.srcObject = stream;
                    remoteVideo.play().then(() => {
                        console.log('Remote video is playing.');
                    }).catch(err => {
                        console.error('Error playing remote video:', err);
                    });
                });

                // Connection established
                peer.on('connect', () => {
                    console.log('Peer connection established.');
                    document.getElementById('connectionStatus').innerText = 'Connecté';
                });

                // Handle errors
                peer.on('error', (err) => {
                    console.error('Peer connection error:', err);
                    alert('Une erreur est survenue avec la connexion. Veuillez réessayer.');
                });

                // Handle connection close
                peer.on('close', () => {
                    console.log('Peer connection closed.');
                    alert('L\'appel a été terminé.');
                    // Optionally, redirect or reset the UI
                    window.location.href = '/'; // Redirect to home or a specific page
                });
            }

            function getOffer() {
                console.log('Fetching offer from signaling server.');
                axios.get('/webrtc/get-offer', { params: { room: room } })
                    .then(response => {
                        const offer = response.data.offer;
                        if (offer) {
                            console.log('Received offer:', offer);
                            peer.signal(offer);
                        } else {
                            console.log('No offer found. Retrying in 2 seconds...');
                            setTimeout(getOffer, 2000);
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching offer:', err);
                        setTimeout(getOffer, 2000);
                    });
            }

            function checkForAnswer() {
                console.log('Checking for answer from signaling server.');
                axios.get('/webrtc/get-answer', { params: { room: room } })
                    .then(response => {
                        const answer = response.data.answer;
                        if (answer) {
                            console.log('Received answer:', answer);
                            peer.signal(answer);
                        } else {
                            console.log('No answer found yet. Retrying in 2 seconds...');
                            setTimeout(checkForAnswer, 2000);
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching answer:', err);
                        setTimeout(checkForAnswer, 2000);
                    });
            }

            // Control Panel Buttons
            const toggleAudioButton = document.getElementById('toggleAudio');
            const toggleVideoButton = document.getElementById('toggleVideo');
            const endCallButton = document.getElementById('endCall');

            // Audio Control
            toggleAudioButton.addEventListener('click', () => {
                audioEnabled = !audioEnabled;
                localStream.getAudioTracks()[0].enabled = audioEnabled;
                updateAudioIcon();
            });

            function updateAudioIcon() {
                const audioIcon = document.getElementById('audioIcon');
                if (audioEnabled) {
                    audioIcon.classList.remove('fa-microphone-slash');
                    audioIcon.classList.add('fa-microphone');
                } else {
                    audioIcon.classList.remove('fa-microphone');
                    audioIcon.classList.add('fa-microphone-slash');
                }
            }

            // Video Control
            toggleVideoButton.addEventListener('click', () => {
                videoEnabled = !videoEnabled;
                localStream.getVideoTracks()[0].enabled = videoEnabled;
                updateVideoIcon();
            });

            function updateVideoIcon() {
                const videoIcon = document.getElementById('videoIcon');
                if (videoEnabled) {
                    videoIcon.classList.remove('fa-video-slash');
                    videoIcon.classList.add('fa-video');
                } else {
                    videoIcon.classList.remove('fa-video');
                    videoIcon.classList.add('fa-video-slash');
                }
            }

            // End Call Control
            endCallButton.addEventListener('click', () => {
                if (peer) {
                    peer.destroy();
                }
                // Optionally, redirect or reset the UI
                window.location.href = '/'; // Redirect to home or a specific page
            });
        });
    </script>
@endpush

</x-app-layout>
