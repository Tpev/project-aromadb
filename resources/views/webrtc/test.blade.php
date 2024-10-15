{{-- resources/views/webrtc/test.blade.php --}}
<x-app-layout>
{{-- resources/views/webrtc/test.blade.php --}}

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
        joinRoom(roomName);
    });

    function joinRoom(room) {
        peerConnection = new RTCPeerConnection(configuration);

        // Ajouter les pistes locales à la PeerConnection
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });

        // Écouter les pistes distantes
        peerConnection.addEventListener('track', event => {
            if (remoteVideo.srcObject !== event.streams[0]) {
                remoteVideo.srcObject = event.streams[0];
            }
        });

        // Gérer les candidats ICE
        peerConnection.addEventListener('icecandidate', event => {
            if (event.candidate) {
                sendSignalingData('ice-candidate', event.candidate);
            }
        });

        // Écouter les événements de signaling via Echo
        window.Echo.channel('room.' + room)
            .listen('.SignalingEvent', (e) => {
                handleSignalingData(e);
            });

        // Créer une offre si c'est le premier à rejoindre la salle
        createOffer();
    }

    function createOffer() {
        peerConnection.createOffer()
            .then(offer => {
                return peerConnection.setLocalDescription(offer);
            })
            .then(() => {
                sendSignalingData('offer', peerConnection.localDescription);
            })
            .catch(err => {
                console.error('Erreur lors de la création de l\'offre :', err);
            });
    }

    function handleSignalingData(data) {
        if (!data || !data.type || !data.payload) return;

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
                break;
        }
    }

    function handleOffer(offer) {
        if (peerConnection.currentRemoteDescription) return;

        peerConnection.setRemoteDescription(new RTCSessionDescription(offer))
            .then(() => {
                return peerConnection.createAnswer();
            })
            .then(answer => {
                return peerConnection.setLocalDescription(answer);
            })
            .then(() => {
                sendSignalingData('answer', peerConnection.localDescription);
            })
            .catch(err => {
                console.error('Erreur lors de la gestion de l\'offre :', err);
            });
    }

    function handleAnswer(answer) {
        peerConnection.setRemoteDescription(new RTCSessionDescription(answer))
            .catch(err => {
                console.error('Erreur lors de la gestion de la réponse :', err);
            });
    }

    function handleIceCandidate(candidate) {
        peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
            .catch(err => {
                console.error('Erreur lors de l\'ajout du candidat ICE :', err);
            });
    }

    function sendSignalingData(type, data) {
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