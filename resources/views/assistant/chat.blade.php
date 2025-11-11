<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            Assistant AromaMade
        </h2>
    </x-slot>

    <div class="p-4">
        <p class="text-gray-700">Dites ce que vous voulez faire. Ex : <em>Créer client Claire Dupont</em>.</p>
    </div>

    <div id="am-assistant"
         style="position:fixed; right:20px; bottom:20px; width:360px; max-width:92vw; height:520px; background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.1); display:flex; flex-direction:column; overflow:hidden; z-index:9999;">

        <div style="padding:12px 14px; border-bottom:1px solid #eee; display:flex; align-items:center; gap:8px;">
            <div style="width:10px; height:10px; background:#8bc34a; border-radius:999px;"></div>
            <div style="font-weight:700;">Assistant AromaMade</div>
        </div>

        <div id="am-messages" style="flex:1; padding:12px; overflow:auto; background:#fafafa;">
            <div class="msg assistant" style="margin-bottom:10px;">
                <div style="background:#eef7e6; padding:10px 12px; border-radius:10px; display:inline-block;">
                    Bonjour 👋 Dites : <em>Créer client Claire Dupont</em>.
                </div>
            </div>
        </div>

        <form id="am-form" style="padding:10px; border-top:1px solid #eee; display:flex; gap:8px;">
            @csrf
            <input id="am-input" type="text" name="text" placeholder="Écrivez ici…"
                   style="flex:1; border:1px solid #ddd; border-radius:8px; padding:10px;">
            <button id="am-send" type="submit"
                    style="background:#647a0b; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600;">
                Envoyer
            </button>
        </form>
    </div>

    <script>
    const messagesEl = document.getElementById('am-messages');
    const formEl     = document.getElementById('am-form');
    const inputEl    = document.getElementById('am-input');

    function addMessage(role, text, link=null, linkLabel='Ouvrir') {
        const wrap = document.createElement('div');
        wrap.className = 'msg ' + role;
        wrap.style.marginBottom = '10px';

        const bubble = document.createElement('div');
        bubble.style.padding = '10px 12px';
        bubble.style.borderRadius = '10px';
        bubble.style.display = 'inline-block';
        bubble.style.maxWidth = 'calc(100% - 20px)';
        bubble.style.wordBreak = 'break-word';
        bubble.style.background = role === 'user' ? '#e8f0fe' : '#eef7e6';
        bubble.innerText = text;

        if (link) {
            const a = document.createElement('a');
            a.href = link;
            a.textContent = ' ' + linkLabel + ' →';
            a.style.marginLeft = '8px';
            a.style.fontWeight = '700';
            a.style.textDecoration = 'underline';
            a.target = '_blank';
            bubble.appendChild(a);
        }

        wrap.appendChild(bubble);
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    formEl.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = inputEl.value.trim();
        if (!text) return;

        addMessage('user', text);
        inputEl.value = '';

        // optimistic "typing"
        addMessage('assistant', 'Je réfléchis…');

        try {
            const res = await fetch("{{ route('assistant.message') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ text })
            });

            // remove typing bubble
            messagesEl.lastElementChild?.remove();

            if (!res.ok) {
                addMessage('assistant', "Oups, une erreur est survenue. Réessayez.");
                return;
            }
            const data = await res.json();
            (data.messages || []).forEach(m => addMessage(m.role, m.text, m.link || null, m.link_label || 'Ouvrir'));
        } catch (err) {
            messagesEl.lastElementChild?.remove();
            addMessage('assistant', "Erreur réseau. Réessayez.");
        }
    });
    </script>
</x-app-layout>
