<x-app-layout>
    @section('title', 'Modifier mon rendez-vous | Olithea')
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .reschedule-page { background:#f5f6f1; min-height:70vh; padding:32px 16px 64px; }
        .reschedule-card { max-width:760px; margin:0 auto; background:#fff; border:1px solid #e0e5d3; border-radius:8px; box-shadow:0 10px 28px rgba(37,49,25,.07); padding:30px; }
        .reschedule-card h1 { margin:0; color:#26351f; font-size:28px; font-weight:700; }
        .reschedule-card .lead { color:#687064; margin:8px 0 24px; }
        .current-slot { background:#f4f7eb; border-left:4px solid #647a0b; padding:14px 16px; margin-bottom:24px; }
        .reschedule-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .reschedule-field label { display:block; margin-bottom:7px; color:#4d5848; font-size:13px; font-weight:700; }
        .reschedule-field input, .reschedule-field select { width:100%; min-height:44px; border:1px solid #cbd3c0; border-radius:6px; padding:9px 11px; background:#fff; }
        .reschedule-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:24px; }
        .reschedule-button { min-height:44px; border-radius:6px; padding:10px 17px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .reschedule-button.primary { border:0; background:#647a0b; color:#fff; cursor:pointer; }
        .reschedule-button.primary:disabled { opacity:.55; cursor:not-allowed; }
        .reschedule-button.secondary { border:1px solid #647a0b; color:#526508; background:#fff; }
        .reschedule-errors { margin-bottom:18px; border-radius:6px; background:#fbe9e5; color:#843d2e; padding:13px 15px; }
        .reschedule-empty { margin:0 0 18px; border-radius:6px; background:#f4f7eb; color:#4d5848; padding:13px 15px; }
        .reschedule-note { color:#687064; font-size:13px; line-height:1.5; margin-top:20px; }
        .flatpickr-calendar { border:1px solid #647a0b; }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background:#647a0b; border-color:#647a0b; }
        @media(max-width:640px) { .reschedule-page{padding:18px 12px 44px}.reschedule-card{padding:22px 18px}.reschedule-card h1{font-size:23px}.reschedule-form-grid{grid-template-columns:1fr}.reschedule-actions,.reschedule-button{width:100%} }
    </style>

    <main class="reschedule-page">
        <section class="reschedule-card">
            <h1>Choisissez un nouveau créneau</h1>
            <p class="lead">Votre rendez-vous actuel reste réservé jusqu’à la confirmation du nouveau créneau.</p>
            @if($errors->any())<div class="reschedule-errors" role="alert">{{ $errors->all()[0] }}</div>@endif
            <div class="current-slot"><strong>Créneau actuel</strong><br>{{ $appointment->appointment_date?->format('d/m/Y à H:i') }} · {{ $appointment->product?->name ?? 'Rendez-vous' }}</div>

            @if(empty($availableDates))
                <p class="reschedule-empty" role="status">Aucun autre créneau n’est disponible pour le moment. Votre rendez-vous actuel reste bien réservé.</p>
            @endif

            <form method="POST" action="{{ route('appointment.confirmation.reschedule', $appointment->token) }}">
                @csrf
                <div class="reschedule-form-grid">
                    <div class="reschedule-field">
                        <label for="appointment_date">Nouvelle date</label>
                        <input type="text" id="appointment_date" name="appointment_date" value="{{ old('appointment_date', $selectedDate) }}" autocomplete="off" required @disabled(empty($availableDates))>
                    </div>
                    <div class="reschedule-field">
                        <label for="appointment_time">Nouvelle heure</label>
                        <select id="appointment_time" name="appointment_time" required>
                            <option value="">Choisissez une heure</option>
                            @foreach($slots as $slot)
                                <option value="{{ $slot['start'] }}" @selected(old('appointment_time') === $slot['start'])>{{ $slot['start'] }} – {{ $slot['end'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="reschedule-actions">
                    <button type="submit" class="reschedule-button primary" @disabled(empty($availableDates))>Confirmer le nouveau créneau</button>
                    <a class="reschedule-button secondary" href="{{ route('appointments.showPatient', $appointment->token) }}">Conserver le créneau actuel</a>
                </div>
            </form>
            <p class="reschedule-note">Pour changer de prestation, de durée, de mode ou de lieu, contactez directement votre praticien.</p>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');
            const endpoint = @json(route('appointment.confirmation.reschedule.slots', $appointment->token));
            const availableDates = @json($availableDates);

            const loadSlots = async (date) => {
                if (!date || !availableDates.includes(date)) return;
                timeSelect.innerHTML = '<option value="">Chargement…</option>';
                try {
                    const response = await fetch(`${endpoint}?date=${encodeURIComponent(date)}`, { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    const slots = Array.isArray(data.slots) ? data.slots : [];
                    timeSelect.innerHTML = '<option value="">Choisissez une heure</option>' + slots.map(slot => `<option value="${slot.start}">${slot.start} – ${slot.end}</option>`).join('');
                    if (!slots.length) timeSelect.innerHTML = '<option value="">Aucun créneau disponible</option>';
                } catch (error) {
                    timeSelect.innerHTML = '<option value="">Impossible de charger les créneaux</option>';
                }
            };

            flatpickr(dateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                minDate: 'today',
                locale: 'fr',
                disableMobile: true,
                enable: availableDates,
                defaultDate: @json(old('appointment_date', $selectedDate)),
                onChange: (_selectedDates, dateStr) => loadSlots(dateStr),
            });
        });
    </script>
</x-app-layout>
