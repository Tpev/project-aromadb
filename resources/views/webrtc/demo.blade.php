<!-- resources/views/webrtc.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WebRTC Room: {{ $room }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <h1 class="text-xl font-semibold">Room: {{ ucfirst($room) }}</h1>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row items-center justify-center p-4">
        <!-- Video Grid -->
        <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-4">
            <!-- Local Video -->
            <div class="relative">
                <video id="localVideo" autoplay muted class="rounded-lg shadow-lg w-64 h-48 object-cover"></video>
                <span class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">You</span>
            </div>

            <!-- Remote Video -->
            <div class="relative">
                <video id="remoteVideo" autoplay class="rounded-lg shadow-lg w-64 h-48 object-cover"></video>
                <span class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">Peer</span>
            </div>
        </div>
    </main>

    <!-- Control Panel -->
    <footer class="bg-white p-4 shadow-inner">
        <div class="flex justify-center space-x-4">
            <!-- Mute/Unmute Audio Button -->
            <button id="toggleAudio" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                <svg id="audioIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- Audio On Icon -->
                    <path id="audioOnPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v14m6-14v14M6 9h.01M6 15h.01M18 9h.01M18 15h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
                </svg>
            </button>

            <!-- Mute/Unmute Video Button -->
            <button id="toggleVideo" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                <svg id="videoIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- Video On Icon -->
                    <path id="videoOnPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8v8a1 1 0 01-1.447.894L15 14m-6 0v6m0-6L4.553 11.724A1 1 0 013 12v0a1 1 0 011.447-.894L9 12m6 0h3m-3 0a1 1 0 011 1v4a1 1 0 01-1 1H6a1 1 0 01-1-1v-4a1 1 0 011-1h9z" />
                </svg>
            </button>

            <!-- End Call Button -->
            <button id="endCall" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:ring-2 focus:ring-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- End Call Icon -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728m12.728-12.728L5.636 18.364" />
                </svg>
            </button>
        </div>
    </footer>

    <!-- SimplePeer & Axios Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-peer@9/simplepeer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Enhanced JavaScript -->
    <script>
        let localStream;
        let peer;
        let audioEnabled = true;
        let videoEnabled = true;
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');

        const room = @json($room);
        const senderId = generateUniqueId();

        console.log(`WebRTC initialized for room: ${room}, senderId: ${senderId}`);

        // TURN and STUN servers
        const iceServers = [
            { urls: 'stun:stun.l.google.com:19302' },
            {
                urls: 'turn:eu.relay.metered.ca:80',
                username: '973cd534a917cf4aad94e78d',
                credential: 'U0vCqXJ3Zj6GCso9',
            },
            // Add more TURN servers as needed
        ];

        // Determine initiator based on URL hash
        const isInitiator = location.hash === '#1';
        console.log(`Peer is initiator: ${isInitiator}`);

        // Get user media (video and audio)
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                localStream = stream;
                localVideo.srcObject = stream;  // Show local video
                console.log('Local stream acquired.');

                createPeer(isInitiator, stream);

                // If not initiator, check for the offer
                if (!isInitiator) {
                    console.log('Non-initiator: Attempting to retrieve offer.');
                    setTimeout(getOffer, 1000); // Give the initiator time to create an offer
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
                trickle: false,  // Disable trickle ICE candidates
                stream: stream,
                config: { iceServers }
            });

            // Send signaling data (offer or answer)
            peer.on('signal', (data) => {
                const type = data.type === 'offer' ? 'offer' : 'answer';
                console.log(`Sending ${type}:`, data);

                axios.post('/webrtc/signaling', {
                    type: type,
                    data: JSON.stringify(data),  // Convert the SDP data into a string
                    room: room,
                    senderId: senderId
                }).then(response => {
                    console.log('Signaling data sent:', response.data);

                    // If the type is offer, initiator will wait for an answer
                    if (type === 'offer') {
                        console.log('Initiator: Waiting for answer.');
                        setTimeout(checkForAnswer, 1000); // Start checking for answer after sending the offer
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

            // Peer connected
            peer.on('connect', () => {
                console.log('Peer connection established.');
            });

            // Handle peer connection errors
            peer.on('error', (err) => {
                console.error('Peer connection error:', err);
                alert('An error occurred with the peer connection. Please try again.');
            });

            peer.on('close', () => {
                console.log('Peer connection closed.');
                alert('The call has ended.');
                // Optionally, redirect or reset the UI
            });
        }

        function getOffer() {
            console.log('Fetching offer from signaling server.');
            axios.get('/webrtc/get-offer', { params: { room: room } })
                .then(response => {
                    const offer = response.data.offer;
                    if (offer) {
                        console.log('Received offer:', offer);
                        peer.signal(offer);  // Signal offer to the peer
                    } else {
                        console.log('No offer found. Retrying in 2 seconds...');
                        setTimeout(getOffer, 2000); // Retry after 2 seconds
                    }
                })
                .catch(err => {
                    console.error('Error fetching offer:', err);
                    setTimeout(getOffer, 2000); // Retry after 2 seconds
                });
        }

        function checkForAnswer() {
            console.log('Checking for answer from signaling server.');
            axios.get('/webrtc/get-answer', { params: { room: room } })
                .then(response => {
                    const answer = response.data.answer;
                    if (answer) {
                        console.log('Received answer:', answer);
                        peer.signal(answer);  // Signal answer to the peer
                    } else {
                        console.log('No answer found yet. Retrying in 2 seconds...');
                        setTimeout(checkForAnswer, 2000); // Retry after 2 seconds
                    }
                })
                .catch(err => {
                    console.error('Error fetching answer:', err);
                    setTimeout(checkForAnswer, 2000); // Retry after 2 seconds
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
            const audioOnPath = document.getElementById('audioOnPath');
            const audioOffPath = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M19 12h.01M12 5v14m7-7H5" />
            `;
            if (audioEnabled) {
                audioIcon.innerHTML = `
                    <path id="audioOnPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v14m6-14v14M6 9h.01M6 15h.01M18 9h.01M18 15h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
                `;
            } else {
                audioIcon.innerHTML = audioOffPath;
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
            const videoOnPath = document.getElementById('videoOnPath');
            const videoOffPath = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v14m6-14v14M6 9h.01M6 15h.01M18 9h.01M18 15h.01" />
            `;
            if (videoEnabled) {
                videoIcon.innerHTML = `
                    <path id="videoOnPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8v8a1 1 0 01-1.447.894L15 14m-6 0v6m0-6L4.553 11.724A1 1 0 013 12v0a1 1 0 011.447-.894L9 12m6 0h3m-3 0a1 1 0 011 1v4a1 1 0 01-1 1H6a1 1 0 01-1-1v-4a1 1 0 011-1h9z" />
                `;
            } else {
                videoIcon.innerHTML = videoOffPath;
            }
        }

        // End Call Control
        endCallButton.addEventListener('click', () => {
            if (peer) {
                peer.destroy();
            }
            // Redirect or reset the UI as needed
            window.location.href = '/'; // Redirect to home or a specific page
        });
    </script>
</body>
</html>
