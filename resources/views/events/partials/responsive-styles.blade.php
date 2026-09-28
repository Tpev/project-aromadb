<style>
    .therapist-events {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .therapist-events :where(.grid > *, .flex > *) {
        min-width: 0;
    }

    .therapist-events :where(input, select, textarea, img, video, iframe) {
        min-width: 0;
        max-width: 100%;
    }

    .event-mobile-sort { display: none; }
    .event-payment-field { flex: 1; min-width: 200px; }

    @media (max-width: 767px) {
        .therapist-events.event-form-page {
            margin-inline: auto;
            padding-inline: 12px;
        }

        .therapist-events .details-container { padding: 18px 14px; }
        .therapist-events .details-title,
        .therapist-events .page-title { font-size: 1.5rem; }
        .therapist-events .table-title { font-size: 1.15rem; }

        .therapist-events :where(button, .btn-primary, .btn-secondary, summary) {
            min-height: 44px;
        }

        .therapist-events :where(input:not([type="checkbox"]):not([type="radio"]), select, textarea) {
            font-size: 16px;
        }

        .therapist-events .details-container :where(.btn-primary, .btn-secondary) {
            width: 100%;
            text-align: center;
            white-space: normal;
        }

        .therapist-events .details-container .d-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .therapist-events .details-container label.d-flex {
            flex-wrap: nowrap;
            align-items: flex-start;
        }

        .therapist-events :where(input[type="checkbox"], input[type="radio"]) { flex-shrink: 0; }
        .therapist-events .event-payment-field { min-width: 0; flex-basis: 100%; }

        .therapist-events .ql-toolbar.ql-snow .ql-formats { margin-right: 6px; }
        .therapist-events .ql-toolbar button { width: 36px; }
        .therapist-events .ql-tooltip {
            left: 0 !important;
            max-width: 100%;
            white-space: normal;
        }

        .therapist-events .event-list-controls {
            align-items: stretch;
            gap: 10px;
        }

        .therapist-events #search {
            max-width: none !important;
            margin: 0;
        }

        .therapist-events .event-create-action { width: 100%; }
        .therapist-events .event-create-action > a { width: 100%; text-align: center; }

        .therapist-events .event-mobile-sort {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            color: #475569;
            font-size: 0.85rem;
            text-align: left;
        }

        .event-mobile-sort select { flex: 1; border-radius: 8px; min-height: 44px; }

        /* Keep the same rows, links and forms when tables become mobile cards. */
        .therapist-events .event-record-table,
        .therapist-events .event-record-table tbody { display: block; width: 100%; }
        .therapist-events .event-record-table thead {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip-path: inset(50%);
        }

        .therapist-events .event-record-table tbody tr {
            display: block;
            padding: 12px;
            border-bottom: 1px solid #e2ecc3;
        }

        .therapist-events .event-record-table tbody tr:hover { transform: none; }
        .therapist-events .event-record-table td {
            display: block;
            padding: 5px 0;
            text-align: left;
            overflow-wrap: anywhere;
        }

        .therapist-events .event-record-table td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 2px;
            font-size: 0.7rem;
            font-weight: 600;
            opacity: 0.75;
        }

        .therapist-events .event-record-title { font-size: 1rem; font-weight: 600; }
        .therapist-events .event-record-title a { display: block; padding-block: 8px; }
        .therapist-events .event-record-table :where(button, .client-cell a) {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            width: 100%;
        }

        .therapist-events .event-participant-heading,
        .therapist-events .event-participant-details {
            flex-direction: column;
            align-items: flex-start;
        }

        .therapist-events .event-participant-details > * { max-width: 100%; }
    }
</style>
