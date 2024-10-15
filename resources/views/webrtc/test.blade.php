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

        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                // Ajoutez des serveurs TURN ici si nécessaire
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
                })
                .listen('pusher:subscription_error', (status) => {
                    console.error('Erreur de souscription au canal Pusher :', status);
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
            if (peerConnection.currentRemoteDescription) {
                console.log('Description distante déjà définie, ignorer l\'offre');
                return;
            }

            peerConnection.setRemoteDescription(new RTCSessionDescription(offer))
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
                })
                .catch(err => {
                    console.error('Erreur lors de la gestion de l\'offre :', err);
                });
        }

        function handleAnswer(answer) {
            console.log('Réponse reçue, définition de la description distante');
            peerConnection.setRemoteDescription(new RTCSessionDescription(answer))
                .then(() => {
                    console.log('Description distante définie avec la réponse');
                })
                .catch(err => {
                    console.error('Erreur lors de la gestion de la réponse :', err);
                });
        }

        function handleIceCandidate(candidate) {
            console.log('Candidat ICE reçu, ajout à la PeerConnection');
            peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
                .then(() => {
                    console.log('Candidat ICE ajouté');
                })
                .catch(err => {
                    console.error('Erreur lors de l\'ajout du candidat ICE :', err);
                });
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
