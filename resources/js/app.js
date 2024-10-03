import './bootstrap';

import Alpine from 'alpinejs';
// resources/js/app.js

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import frLocale from '@fullcalendar/core/locales/fr'; // Pour la localisation française

// Rendre FullCalendar accessible globalement (optionnel)
window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
    listPlugin,
    bootstrapPlugin,
    locales: { fr: frLocale },
	
};
window.FullCalendar.locales = { fr: frLocale };
// Vos autres imports...


window.Alpine = Alpine;

Alpine.start();
