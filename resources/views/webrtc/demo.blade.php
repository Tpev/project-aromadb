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
    <!-- Include SimplePeer from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/simple-peer@9/simplepeer.min.js"></script>

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
        let senderId = generateUniqueId();

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

        // Create a peer connection
        function createPeer(initiator) {
            const peer = new SimplePeer({
                initiator: initiator,
                trickle: false,
                stream: localStream
            });

            peer.on('signal', data => {
                console.log('SIGNAL', JSON.stringify(data));
                sendSignalingData('signal', data);
            });

            peer.on('connect', () => {
                console.log('Connected to peer!');
            });

            peer.on('data', data => {
                console.log('Received data from peer:', data);
            });

            peer.on('stream', stream => {
                console.log('Received remote stream');
                remoteVideo.srcObject = stream;
            });

            return peer;
        }

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
            let isInitiator = false;
            let peer;

            // Call your signaling API to check if the user is the initiator
            axios.post('/webrtc/join-room', {
                room: room,
                user_id: senderId
            }).then(response => {
                isInitiator = response.data.isInitiator;
                peer = createPeer(isInitiator);  // Create the peer based on whether the user is the initiator

                if (!isInitiator && response.data.offer) {
                    console.log('Received offer from initiator:', response.data.offer);
                    peer.signal(response.data.offer); // Respond to the cached offer
                }
            }).catch(err => {
                console.error('Error joining room:', err);
            });
        }

        function sendSignalingData(type, data) {
            axios.post('/webrtc/signaling', {
                type: type,
                data: data,
                room: roomName,
                senderId: senderId
            }).then(response => {
                console.log('Signaling data sent:', response.data);
            }).catch(err => {
                console.error('Error sending signaling data:', err);
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
