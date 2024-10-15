{{-- resources/views/webrtc/test.blade.php --}}
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
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Page chargée et DOM prêt');

        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const joinBtn = document.getElementById('joinBtn');
        const roomInput = document.getElementById('roomInput');

        let localStream;
        let peerConnection;
        let roomName;
        let iceCandidatesQueue = []; // Queue for ICE candidates before remoteDescription is set

        // Configure ICE servers (STUN/TURN)
        const configuration = {
            iceServers: [
                {
                    urls: "stun:stun.relay.metered.ca:80",
                },
                {
                    urls: "turn:eu.relay.metered.ca:80",
                    username: "973cd534a917cf4aad94e78d",
                    credential: "U0vCqXJ3Zj6GCso9",
                },
                {
                    urls: "turn:eu.relay.metered.ca:80?transport=tcp",
                    username: "973cd534a917cf4aad94e78d",
                    credential: "U0vCqXJ3Zj6GCso9",
                },
                {
                    urls: "turn:eu.relay.metered.ca:443",
                    username: "973cd534a917cf4aad94e78d",
                    credential: "U0vCqXJ3Zj6GCso9",
                },
                {
                    urls: "turns:eu.relay.metered.ca:443?transport=tcp",
                    username: "973cd534a917cf4aad94e78d",
                    credential: "U0vCqXJ3Zj6GCso9",
                },
            ]
        };

        // Initialiser la capture vidéo locale
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                console.log('Médias locaux capturés');
                localStream = stream;
                localVideo.srcObject = stream;
            })
            .catch(err => {
                console.error('Erreur lors de la capture des médias :', err);
                alert('Impossible d\'accéder à la caméra et au microphone.');
            });

        joinBtn.addEventListener('click', () => {
            roomName = roomInput.value.trim();
            if (!roomName) {
                alert('Veuillez entrer un nom de salle.');
                return;
            }
            console.log(`Rejoindre la salle : ${roomName}`);
            joinRoom(roomName);
        });

        function joinRoom(room) {
            console.log(`Initialisation de la PeerConnection pour la salle : ${room}`);
            peerConnection = new RTCPeerConnection(configuration);

            // Ajouter les pistes locales à la PeerConnection
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
                console.log(`Piste ajoutée : ${track.kind}`);
            });

            // Écouter les pistes distantes
            peerConnection.addEventListener('track', event => {
                console.log('Piste distante reçue');
                if (remoteVideo.srcObject !== event.streams[0]) {
                    remoteVideo.srcObject = event.streams[0];
                    console.log('Flux distant défini');
                }
            });

            // Gérer les candidats ICE
            peerConnection.addEventListener('icecandidate', event => {
                if (event.candidate) {
                    console.log('Candidat ICE détecté et envoyé');
                    sendSignalingData('ice-candidate', event.candidate);
                }
            });

            // Écouter les événements de signaling via Echo
            console.log(`Écoute du canal : room.${room}`);
            window.Echo.channel('room.' + room)
                .listen('.SignalingEvent', (e) => {
                    console.log('SignalingEvent reçu :', e);
                    handleSignalingData(e);
                });

            // Créer une offre si c'est le premier à rejoindre la salle
            createOffer();
        }

        function createOffer() {
            console.log('Création d\'une offre');
            peerConnection.createOffer()
                .then(offer => {
                    console.log('Offre créée');
                    return peerConnection.setLocalDescription(offer);
                })
                .then(() => {
                    console.log('Description locale définie');
                    sendSignalingData('offer', peerConnection.localDescription);
                })
                .catch(err => {
                    console.error('Erreur lors de la création de l\'offre :', err);
                });
        }

function cleanSDP(sdp) {
    const sdpLines = sdp.split('\n');
    const cleanedSDP = [];

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
        /^a=rtcp-fb:/,                        // RTCP feedback mappings
    ];

    // Filter out unsupported codecs or attributes (e.g., ulpfec, rtx)
    const disallowedLines = [
        /^a=rtpmap:117 rtx/,                  // Exclude rtx codec
        /^a=rtpmap:118 ulpfec/,               // Exclude unsupported ulpfec codec
        /^a=rtpmap:116 red/,               // Exclude unsupported ulpfec codec
        /^a=rtpmap:113 rtx/,               // Exclude unsupported ulpfec codec
        /^a=rtcp-fb:112 nack pli/,               // Exclude unsupported ulpfec codec
        /^a=rtcp-fb:112 nack/,               // Exclude unsupported ulpfec codec
        /^a=rtcp-fb:112 ccm fir/,               // Exclude unsupported ulpfec codec
        /^a=rtcp-fb:112 transport-cc/,               // Exclude unsupported ulpfec codec
        /^a=rt*/,               // Exclude unsupported ulpfec codec
        /^a=ms*/,               // Exclude unsupported ulpfec codec
    ];

    // Loop through each line in the SDP and keep only the allowed lines
    sdpLines.forEach(line => {
        let isAllowed = false;
        for (let pattern of allowedLines) {
            if (pattern.test(line.trim())) {
                isAllowed = true;
                break;
            }
        }
        // Exclude disallowed lines
        for (let disallowed of disallowedLines) {
            if (disallowed.test(line.trim())) {
                isAllowed = false;
                break;
            }
        }
        if (isAllowed) {
            cleanedSDP.push(line.trim());
        }
    });

    // Join the cleaned lines back into a string and return
    return cleanedSDP.join('\n');
}



        function handleSignalingData(data) {
            if (!data || !data.type || !data.payload) {
                console.warn('Données de signaling invalides :', data);
                return;
            }

            console.log(`Gestion du type de signaling : ${data.type}`);
            switch(data.type) {
                case 'offer':
                    handleOffer(data.payload);
                    break;
                case 'answer':
                    handleAnswer(data.payload);
                    break;
                case 'ice-candidate':
                    handleIceCandidate(data.payload);
                    break;
                default:
                    console.warn('Type de signaling inconnu :', data.type);
                    break;
            }
        }

        function handleOffer(offer) {
            console.log('Offre reçue, définition de la description distante');
            const filteredSDP = cleanSDP(offer.sdp);  // Apply the SDP filter
            peerConnection.setRemoteDescription(new RTCSessionDescription({
                type: 'offer',
                sdp: filteredSDP  // Using the filtered SDP
            }))
            .then(() => {
                console.log('Description distante définie, création d\'une réponse');
                return peerConnection.createAnswer();
            })
            .then(answer => {
                console.log('Réponse créée');
                return peerConnection.setLocalDescription(answer);
            })
            .then(() => {
                console.log('Description locale définie avec la réponse');
                sendSignalingData('answer', peerConnection.localDescription);
                iceCandidatesQueue.forEach(candidate => peerConnection.addIceCandidate(new RTCIceCandidate(candidate)));
                iceCandidatesQueue = []; // Clear the queue
            })
            .catch(err => {
                console.error('Erreur lors de la gestion de l\'offre :', err);
            });
        }

        function handleAnswer(answer) {
            console.log('Réponse reçue, définition de la description distante');
            const filteredSDP = cleanSDP(answer.sdp);  // Apply the SDP filter for answers as well
            peerConnection.setRemoteDescription(new RTCSessionDescription({
                type: 'answer',
                sdp: filteredSDP  // Using the filtered SDP
            }))
            .then(() => {
                console.log('Description distante définie avec la réponse');

                // Add queued ICE candidates after setting the remote description
                iceCandidatesQueue.forEach(candidate => peerConnection.addIceCandidate(new RTCIceCandidate(candidate)));
                iceCandidatesQueue = []; // Clear the queue
            })
            .catch(err => {
                console.error('Erreur lors de la gestion de la réponse :', err);
            });
        }

        function handleIceCandidate(candidate) {
            if (peerConnection.remoteDescription) {
                console.log('Candidat ICE reçu, ajout à la PeerConnection');
                peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                    .then(() => {
                        console.log('Candidat ICE ajouté');
                    })
                    .catch(err => {
                        console.error('Erreur lors de l\'ajout du candidat ICE :', err);
                    });
            } else {
                console.log('Remote description non définie, mise en file d\'attente du candidat ICE');
                iceCandidatesQueue.push(candidate);
            }
        }

        function sendSignalingData(type, data) {
            console.log(`Envoi des données de signaling : ${type}`);
            axios.post('/api/signaling', {
                type: type,
                payload: data,
                room: roomName
            }).then(response => {
                console.log('Données de signaling envoyées :', response.data);
            }).catch(err => {
                console.error('Erreur lors de l\'envoi des données de signaling :', err);
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
