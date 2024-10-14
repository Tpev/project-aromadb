// Importing required libraries
import './bootstrap';
import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import frLocale from '@fullcalendar/core/locales/fr'; // French localization


// Expose FullCalendar globally if needed
window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
    listPlugin,
    bootstrapPlugin,
    locales: { fr: frLocale },
};

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();


