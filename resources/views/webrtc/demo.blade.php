<html>
  <body>
    <style>
      video {
        width: 300px;
        height: 200px;
        background-color: gray;
      }
    </style>

    <!-- Video elements for local and remote streams -->
    <h2>Your Video</h2>
    <video id="localVideo" autoplay muted></video>
    <h2>Remote Video</h2>
    <video id="remoteVideo" autoplay></video>

    <!-- SimplePeer & Axios Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-peer@9/simplepeer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
      let localStream;
      let peer;
      const localVideo = document.getElementById('localVideo');
      const remoteVideo = document.getElementById('remoteVideo');
      const room = 'webrtc-room';  // Fixed room for demo
      const senderId = generateUniqueId();

      // Get user media (video and audio)
      navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
          localStream = stream;
          localVideo.srcObject = stream;  // Show local video

          const isInitiator = location.hash === '#1';  // Determines if peer is the initiator
          createPeer(isInitiator, stream);

          // If not initiator, check for the offer
          if (!isInitiator) {
            setTimeout(getOffer, 1000); // Give the initiator time to create an offer
          }
        })
        .catch(error => {
          console.error('Error accessing media devices.', error);
        });

      function generateUniqueId() {
        return Math.random().toString(36).substr(2, 9) + '-' + Date.now().toString(36);
      }

      function createPeer(initiator, stream) {
        peer = new SimplePeer({
          initiator: initiator,
          trickle: false,  // Disable trickle ICE candidates
          stream: stream,
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
              setTimeout(checkForAnswer, 1000); // Start checking for answer after sending the offer
            }
          }).catch(err => {
            console.error('Error sending signaling data:', err);
          });
        });

        // Receive remote stream
        peer.on('stream', (stream) => {
          console.log('Received remote stream.');
          remoteVideo.srcObject = stream;
        });

        // Peer connected
        peer.on('connect', () => {
          console.log('Connected to peer!');
        });

        // Handle peer connection errors
        peer.on('error', (err) => {
          console.error('Error with peer connection:', err);
        });
      }

      function getOffer() {
        axios.get('/webrtc/get-offer', { params: { room: room } })
          .then(response => {
            const offer = response.data.offer;
            if (offer) {
              console.log('Received offer:', offer);
              peer.signal(offer);  // Signal offer to the peer
            } else {
              console.log('No offer available.');
            }
          })
          .catch(err => {
            console.error('Error retrieving offer:', err);
          });
      }

      function checkForAnswer() {
        axios.get('/webrtc/get-answer', { params: { room: room } })
          .then(response => {
            const answer = response.data.answer;
            if (answer) {
              console.log('Received answer:', answer);
              peer.signal(answer);  // Signal answer to the peer
            } else {
              console.log('No answer available yet. Retrying...');
              setTimeout(checkForAnswer, 1000); // Retry after 1 second if no answer is available
            }
          })
          .catch(err => {
            console.error('Error retrieving answer:', err);
            setTimeout(checkForAnswer, 1000); // Retry after 1 second if error occurs
          });
      }
    </script>
  </body>
</html>
