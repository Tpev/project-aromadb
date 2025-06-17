<x-client-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 space-y-10">
        <h1 class="text-3xl font-bold text-gray-900">Bienvenue, {{ auth()->user()->first_name ?? auth()->user()->name }}</h1>

        <!-- Upload Document Section -->
        <section class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📄 Envoyer un document</h2>
            <form id="documentUploadForm" enctype="multipart/form-data">
                @csrf
                <input type="file" name="document" id="documentFile" class="block w-full border border-gray-300 rounded px-4 py-2 mb-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded w-full sm:w-auto">Envoyer</button>
            </form>
            <div id="uploadStatus" class="mt-3 text-sm hidden"></div>
        </section>

        <!-- Uploaded Documents -->
        <section class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📁 Mes Documents Envoyés</h2>
            <ul class="divide-y divide-gray-200">
                @forelse($clientProfile->clientFiles as $file)
                    <li class="py-2 text-sm flex justify-between items-center">
                        <span>{{ $file->original_name }}</span>
                        <a href="{{ route('client_files.download', $file) }}" class="text-indigo-600 hover:underline">Télécharger</a>



                    </li>
                @empty
                    <li class="text-gray-500 text-sm">Aucun document envoyé.</li>
                @endforelse
            </ul>
        </section>

        <!-- Ask a Question -->
<section class="bg-white p-6 rounded-2xl shadow">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">💬 Messagerie</h2>

<div id="messageList" class="space-y-2 mb-4 max-h-64 overflow-y-auto">
    @foreach($messages as $msg)
        <div class="text-sm {{ $msg->sender_type === 'client' ? 'text-right' : 'text-left' }}">
            <div class="inline-block px-3 py-2 rounded-lg {{ $msg->sender_type === 'client' ? 'bg-indigo-100' : 'bg-gray-100' }}">
                <div>{{ $msg->content }}</div>
                <div class="text-gray-500 text-xs mt-1">
                    {{ $msg->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    @endforeach
</div>


    <form id="messageForm">
        @csrf
        <textarea name="content" rows="2" class="w-full border rounded px-3 py-2" placeholder="Écrivez un message..."></textarea>
        <button type="submit" class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded">Envoyer</button>
    </form>
</section>

<script>
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    fetch('{{ route('client.messages.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: data
    }).then(res => res.json()).then(res => {
        if (res.success) {
            location.reload(); // or append message via JS
        }
    });
});

// Scroll vers le bas automatiquement au chargement
window.addEventListener('DOMContentLoaded', () => {
    const messageList = document.getElementById('messageList');
    if (messageList) {
        messageList.scrollTop = messageList.scrollHeight;
    }
});

</script>


        <!-- Upcoming Appointments -->
        <section class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📅 Mes Rendez-vous à venir</h2>
            <ul class="divide-y divide-gray-200">
                @forelse($appointments as $appointment)
                    <li class="py-2 text-sm">
                        {{ $appointment->appointment_date->format('d/m/Y H:i') }} avec {{ $appointment->user->name ?? 'votre thérapeute' }}
                    </li>
                @empty
                    <li class="text-gray-500 text-sm">Aucun rendez-vous prévu.</li>
                @endforelse
            </ul>
        </section>

        <!-- Invoices -->
        <section class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🧾 Mes Factures</h2>
            <ul class="divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <span>Facture du {{ $invoice->created_at->format('d/m/Y') }}</span>
                        <a href="{{ route('client.invoices.pdf', $invoice->id) }}" class="text-indigo-600 hover:underline">Télécharger</a>

                    </li>
                @empty
                    <li class="text-gray-500 text-sm">Aucune facture disponible.</li>
                @endforelse
            </ul>
        </section>


    </div>

    <script>
        document.getElementById('documentUploadForm').addEventListener('submit', function (e) {
            e.preventDefault();

            let formData = new FormData();
            formData.append('document', document.getElementById('documentFile').files[0]);

            fetch('{{ route('client.files.upload') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const status = document.getElementById('uploadStatus');
                if (data.success) {
                    status.innerText = '✅ Document envoyé avec succès !';
                    status.classList.remove('hidden', 'text-red-600');
                    status.classList.add('text-green-600');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    status.innerText = '❌ Erreur : ' + (data.message || 'envoi échoué');
                    status.classList.remove('hidden', 'text-green-600');
                    status.classList.add('text-red-600');
                }
            })
            .catch(error => {
                const status = document.getElementById('uploadStatus');
                status.innerText = '❌ Une erreur est survenue.';
                status.classList.remove('hidden', 'text-green-600');
                status.classList.add('text-red-600');
                console.error(error);
            });
        });
		
function renderMessages(messages) {
    const list = document.getElementById('messageList');
    list.innerHTML = ''; // clear

    messages.forEach(msg => {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'text-sm ' + (msg.sender_type === 'client' ? 'text-right' : 'text-left');

        const bubble = document.createElement('div');
        bubble.className = 'inline-block px-3 py-2 rounded-lg ' + (msg.sender_type === 'client' ? 'bg-indigo-100' : 'bg-gray-100');
        bubble.innerText = msg.content;

        const time = document.createElement('div');
        time.className = 'text-xs text-gray-400 mt-1';
        time.innerText = msg.timestamp;

        msgDiv.appendChild(bubble);
        msgDiv.appendChild(time);
        list.appendChild(msgDiv);
    });

    list.scrollTop = list.scrollHeight;
}

function fetchMessages() {
    fetch('{{ route('client.messages.fetch') }}')
        .then(res => res.json())
        .then(renderMessages);
}

setInterval(fetchMessages, 10000); // every 10 seconds
window.addEventListener('DOMContentLoaded', fetchMessages);
		
		
    </script>
</x-client-app-layout>
