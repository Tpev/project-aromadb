<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">Test de Visioconférence WebRTC</h1>
        <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-4">
            <div>
                <h2 class="text-xl font-semibold mb-2">Votre Vidéo</h2>
                <video id="localVideo" autoplay muted class="w-64 h-48 bg-gray-200"></video>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-2">Vidéo du Participant</h2>
                <video id="remoteVideo" autoplay class="w-64 h-48 bg-gray-200"></video>
            </div>
        </div>
        <div class="mt-6 text-center">
            <input type="text" id="roomInput" placeholder="Nom de la salle" class="border border-gray-300 p-2 rounded-lg mr-2">
            <button id="joinBtn" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors duration-300">Rejoindre</button>
        </div>
    </div>

    @push('scripts')
    <script type="module">
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Page chargée et DOM prêt');

        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const joinBtn = document.getElementById('joinBtn');
        const roomInput = document.getElementById('roomInput');

        let localStream;
        let peerConnection;
        let roomName;
        let isInitiator = false;
        let senderId = generateUniqueId();
        let iceCandidatesQueue = [];

        const configuration = {
            iceServers: [
                { urls: "stun:stun.relay.metered.ca:80" },
                { urls: "turn:eu.relay.metered.ca:80", username: "973cd534a917cf4aad94e78d", credential: "U0vCqXJ3Zj6GCso9" },
            ]
        };

        function generateUniqueId() {
            return Math.random().toString(36).substr(2, 9) + '-' + Date.now().toString(36);
        }

        // Access media devices (camera and microphone)
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                localStream = stream;
                localVideo.srcObject = stream;
                console.log('Local media stream obtained.');
            })
            .catch(err => {
                alert('Impossible d\'accéder à la caméra et au microphone.');
            });

        // On "join" button click
        joinBtn.addEventListener('click', () => {
            roomName = roomInput.value.trim();
            if (!roomName) {
                alert('Veuillez entrer un nom de salle.');
                return;
            }
            joinRoom(roomName);
        });

        function joinRoom(room) {
            peerConnection = new RTCPeerConnection(configuration);

            // Add local tracks to the peer connection
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });

            // Handle remote track
            peerConnection.addEventListener('track', event => {
                if (remoteVideo.srcObject !== event.streams[0]) {
                    remoteVideo.srcObject = event.streams[0];
                }
            });

            // Handle ICE candidates
            peerConnection.addEventListener('icecandidate', event => {
                if (event.candidate) {
                    sendSignalingData('ice-candidate', event.candidate);
                }
            });

            // Subscribe to the Pusher channel for signaling
            window.Echo.channel('video-room.' + room)
                .listen('.client-signaling', (data) => {
                    handleSignalingData(data);
                })
                .listen('.client-joined', (data) => {
                    console.log('Client joined:', data.user_id);
                    if (!isInitiator) {
                        console.log('Not the initiator, waiting for an offer...');
                    }
                });

            // First user (initiator) creates the offer
            sendJoinNotification(room);
        }

        function sendJoinNotification(room) {
            axios.post('/webrtc/join-room', {
                room: room,
                user_id: senderId
            }).then(response => {
                if (response.data.isInitiator) {
                    isInitiator = true;
                    console.log('This user is the initiator.');
                    createOffer();  // Create an offer if this is the first user
                } else {
                    console.log("This user is NOT the initiator. Waiting for the offer.");
                }
            }).catch(err => {
                console.error('Error joining room:', err);
            });
        }

        function createOffer() {
            peerConnection.createOffer()
                .then(offer => peerConnection.setLocalDescription(offer))
                .then(() => {
                    console.log('Offer created and sent.');
                    sendSignalingData('offer', peerConnection.localDescription);
                })
                .catch(err => {
                    console.error('Erreur lors de la création de l\'offre :', err);
                });
        }

        function sendSignalingData(type, data) {
            axios.post('/webrtc/signaling', {
                type: type,
                data: data,
                room: roomName,
                senderId: senderId
            }).then(response => {
                console.log('Données de signaling envoyées :', response.data);
            }).catch(err => {
                console.error('Erreur lors de l\'envoi des données de signaling :', err);
            });
        }

        function handleSignalingData(data) {
            if (data.senderId === senderId) {
                return;
            }

            if (data.type === 'offer') {
                handleOffer(data.data);
            } else if (data.type === 'answer') {
                handleAnswer(data.data);
            } else if (data.type === 'ice-candidate') {
                handleIceCandidate(data.data);
            }
        }

        function cleanSDP(sdp) {
            const sdpLines = sdp.split('\n');
            const filteredSDP = [];

            const allowedLines = [
                /^v=0/,                               // Version
                /^o=-/,                               // Origin
                /^s=-/,                               // Session name
                /^t=0 0/,                             // Time
                /^a=group:BUNDLE/,                    // Group bundle
                /^a=msid-semantic: WMS/,              // Media Stream Identification
                /^m=audio/,                           // Audio media description
                /^m=video/,                           // Video media description
                /^c=IN IP4 0.0.0.0/,                  // Connection Information
                /^a=rtcp:9 IN IP4 0.0.0.0/,           // RTCP connection
                /^a=ice-ufrag:/,                      // ICE username fragment
                /^a=ice-pwd:/,                        // ICE password
                /^a=fingerprint:/,                    // DTLS fingerprint
                /^a=setup:/,                          // Setup attribute
                /^a=mid:/,                            // Media stream identification
                /^a=sendrecv/,                        // Send/receive directions
                /^a=msid:/,                           // Media stream ID
                /^a=rtcp-mux/,                        // RTCP multiplexing
                /^a=rtpmap:/,                         // Codec mappings
            ];

            sdpLines.forEach(line => {
                for (let pattern of allowedLines) {
                    if (pattern.test(line.trim())) {
                        filteredSDP.push(line.trim());
                        break;
                    }
                }
            });

            return filteredSDP.join('\n');
        }

        function handleOffer(offer) {
            const filteredSDP = cleanSDP(offer.sdp);
            peerConnection.setRemoteDescription(new RTCSessionDescription({
                type: 'offer',
                sdp: filteredSDP
            }))
            .then(() => peerConnection.createAnswer())
            .then(answer => peerConnection.setLocalDescription(answer))
            .then(() => {
                console.log('Answer created and sent.');
                sendSignalingData('answer', peerConnection.localDescription);
                iceCandidatesQueue.forEach(candidate => {
                    peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                        .catch(err => {
                            console.error('Erreur lors de l\'ajout du candidat ICE :', err);
                        });
                });
                iceCandidatesQueue = [];
            })
            .catch(err => {
                console.error('Erreur lors de la gestion de l\'offre :', err);
            });
        }

        function handleIceCandidate(candidate) {
            if (peerConnection.remoteDescription) {
                peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                    .catch(err => {
                        console.error('Erreur lors de l\'ajout du candidat ICE :', err);
                    });
            } else {
                iceCandidatesQueue.push(candidate);
                console.log('Candidat ICE mis en file d\'attente');
            }
        }
    });
    </script>
    @endpush
</x-app-layout>
