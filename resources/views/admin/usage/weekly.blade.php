<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Usage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        #bg-video{
            position:fixed; top:0; left:0;
            min-width:100%; min-height:100%;
            z-index:-1; object-fit:cover;
            filter:brightness(45%) blur(2px);
        }
        body{
            background:transparent; color:#f0f0f0;
            font-family:'Montserrat',sans-serif;
            overflow-x:hidden; margin:0; padding:0;
        }
        .container{
            max-width:1300px; margin:0 auto;
            padding:0 15px; position:relative; z-index:1;
        }
        .mt-5{ margin-top:2rem; }
        .page-title{
            font-size:2.2rem; font-weight:800; color:#fff;
            margin: 30px 0 10px; text-align:center;
            text-transform:uppercase; letter-spacing:2px; position:relative;
        }
        .page-title::after{
            content:''; width:180px; height:3px;
            background:linear-gradient(90deg,#ff512f,#dd2476);
            display:block; margin:18px auto 0; border-radius:2px;
        }
        .subhead{
            text-align:center; color:#cfcfe6;
            margin-bottom: 18px; font-size:0.95rem;
        }
        .toolbar{
            display:flex; gap:12px; justify-content:center;
            align-items:center; flex-wrap:wrap; margin: 14px 0 24px;
        }
        .pill{
            background:rgba(42,42,60,0.82);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:999px; padding:10px 14px;
            box-shadow:0 0 20px rgba(0,0,0,0.35);
            backdrop-filter:blur(6px);
            display:flex; gap:10px; align-items:center;
        }
        .pill label{ font-size:0.85rem; color:#d6d6f2; }
        .pill input{
            width:90px; background:rgba(58,58,79,0.8);
            color:#fff; border:1px solid rgba(255,255,255,0.12);
            border-radius:10px; padding:6px 10px; outline:none;
        }
        .action-btn{
            display:inline-block; padding:10px 18px;
            background:linear-gradient(90deg,#ff512f,#dd2476);
            color:#fff; border-radius:999px; text-decoration:none;
            border:0; cursor:pointer; font-weight:700;
            box-shadow:0 0 10px rgba(255,81,47,0.5);
            transition:transform .2s ease, box-shadow .2s ease;
        }
        .action-btn:hover{ transform:scale(1.03); box-shadow:0 0 18px rgba(255,81,47,0.7); }

        .kpis{
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap:12px; margin: 10px 0 18px;
        }
        .kpi{
            background:rgba(42,42,60,0.82);
            border-radius:14px; padding:14px 16px;
            box-shadow:0 0 28px rgba(0,0,0,0.45);
            backdrop-filter:blur(6px);
            border:1px solid rgba(255,255,255,0.06);
        }
        .kpi .label{ color:#cfcfe6; font-size:0.85rem; margin-bottom:6px; }
        .kpi .value{ font-size:1.5rem; font-weight:800; color:#fff; }

        details.section{
            background:rgba(42,42,60,0.82);
            border-radius:14px;
            box-shadow:0 0 28px rgba(0,0,0,0.45);
            backdrop-filter:blur(6px);
            border:1px solid rgba(255,255,255,0.06);
            margin: 12px 0;
            overflow:hidden;
        }
        summary.section-head{
            list-style:none;
            cursor:pointer;
            padding: 14px 16px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            background:rgba(58,58,79,0.65);
        }
        summary.section-head::-webkit-details-marker{ display:none; }
        .section-title{
            font-weight:800;
            letter-spacing:0.5px;
            text-transform:uppercase;
        }
        .section-badges{
            display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;
        }
        .badge{
            font-size:0.78rem;
            color:#fff;
            padding:6px 10px;
            border-radius:999px;
            background:rgba(255,255,255,0.08);
            border:1px solid rgba(255,255,255,0.10);
            white-space:nowrap;
        }
        .section-body{ padding: 16px; }

        .table-responsive{
            background:rgba(0,0,0,0.12);
            border-radius:12px;
            padding:12px;
            overflow-x:auto;
            border:1px solid rgba(255,255,255,0.06);
        }
        table{
            width:100%;
            color:#f0f0f0;
            border-collapse:collapse;
        }
        th, td{
            padding:12px;
            text-align:center;
            border-bottom:1px solid rgba(58,58,79,0.85);
            white-space:nowrap;
        }
        thead{ background:rgba(58,58,79,0.75); }
        th{
            text-transform:uppercase;
            font-size:0.85rem;
        }
        .muted{ color:#cfcfe6; font-size:0.85rem; }

        @media (max-width: 900px){ .kpis{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px){ .kpis{ grid-template-columns: 1fr; } }
    </style>
</head>

<body>
<video autoplay muted loop id="bg-video">
    <source src="/images/bg01.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>

<div class="container mt-5">
    <h1 class="page-title">Weekly Feature Usage</h1>
    <p class="subhead">
        Range: {{ $start->setTimezone('Europe/Paris')->format('d/m/Y') }}
        → {{ $end->setTimezone('Europe/Paris')->format('d/m/Y') }}
        <span class="muted">•</span>
        Weeks: {{ $weeksBack }}
    </p>

    <div class="toolbar">
        <form method="GET" action="{{ route('admin.usage.weekly') }}" class="pill">
            <label for="weeks">Weeks</label>
            <input id="weeks" name="weeks" type="number" min="4" max="104" value="{{ $weeksBack }}">
            <button class="action-btn" type="submit">Apply</button>
        </form>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="label">Appointments (total)</div>
            <div class="value">{{ number_format($totals['appointments'] ?? 0) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Invoices (total)</div>
            <div class="value">{{ number_format($totals['invoices'] ?? 0) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Newsletters sent (total)</div>
            <div class="value">{{ number_format($totals['news_sent'] ?? 0) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Referral invites (total)</div>
            <div class="value">{{ number_format($totals['ref_inv'] ?? 0) }}</div>
        </div>
    </div>

    {{-- ========= SECTION: Agenda ========= --}}
    <details class="section" open>
        <summary class="section-head">
            <div class="section-title">Agenda</div>
            <div class="section-badges">
                <span class="badge">Appointments created</span>
                <span class="badge">Meetings created</span>
                <span class="badge">Active users</span>
            </div>
        </summary>
        <div class="section-body">
            <div class="table-responsive">
                <table aria-label="Agenda weekly stats">
                    <thead>
                    <tr>
                        <th>Week</th>
                        <th>Active users</th>
                        <th>Appointments</th>
                        <th>Users ➜ Appointments</th>
                        <th>Meetings</th>
                        <th>Users ➜ Meetings</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td>
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>
                            <td><strong>{{ $w['active_users'] }}</strong></td>
                            <td>{{ $w['appointments_created_count'] }}</td>
                            <td class="muted">{{ $w['appointments_created_users'] }}</td>
                            <td>{{ $w['meetings_created_count'] }}</td>
                            <td class="muted">{{ $w['meetings_created_users'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    {{-- ========= SECTION: Billing ========= --}}
    <details class="section">
        <summary class="section-head">
            <div class="section-title">Billing</div>
            <div class="section-badges">
                <span class="badge">Invoices</span>
                <span class="badge">Quotes</span>
                <span class="badge">Corporate clients</span>
            </div>
        </summary>
        <div class="section-body">
            <div class="table-responsive">
                <table aria-label="Billing weekly stats">
                    <thead>
                    <tr>
                        <th>Week</th>
                        <th>Invoices</th>
                        <th>Users</th>
                        <th>Quotes</th>
                        <th>Users</th>
                        <th>Corporate clients</th>
                        <th>Users</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td>
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>

                            <td>{{ $w['invoices_created_count'] }}</td>
                            <td class="muted">{{ $w['invoices_created_users'] }}</td>

                            <td>{{ $w['quotes_created_count'] }}</td>
                            <td class="muted">{{ $w['quotes_created_users'] }}</td>

                            <td>{{ $w['corporate_clients_created_count'] }}</td>
                            <td class="muted">{{ $w['corporate_clients_created_users'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    {{-- ========= SECTION: Marketing ========= --}}
    <details class="section">
        <summary class="section-head">
            <div class="section-title">Marketing</div>
            <div class="section-badges">
                <span class="badge">Newsletters</span>
                <span class="badge">Packs</span>
                <span class="badge">Questionnaires</span>
            </div>
        </summary>
        <div class="section-body">
            <div class="table-responsive">
                <table aria-label="Marketing weekly stats">
                    <thead>
                    <tr>
                        <th>Week</th>

                        <th>News created</th>
                        <th class="muted">Users</th>

                        <th>Scheduled</th>
                        <th class="muted">Users</th>

                        <th>Sent</th>
                        <th class="muted">Users</th>

                        <th>Packs</th>
                        <th class="muted">Users</th>

                        <th>Questionnaires</th>
                        <th class="muted">Users</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td>
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>

                            <td>{{ $w['newsletters_created_count'] }}</td>
                            <td class="muted">{{ $w['newsletters_created_users'] }}</td>

                            <td>{{ $w['newsletters_scheduled_count'] }}</td>
                            <td class="muted">{{ $w['newsletters_scheduled_users'] }}</td>

                            <td>{{ $w['newsletters_sent_count'] }}</td>
                            <td class="muted">{{ $w['newsletters_sent_users'] }}</td>

                            <td>{{ $w['pack_products_created_count'] }}</td>
                            <td class="muted">{{ $w['pack_products_created_users'] }}</td>

                            <td>{{ $w['questionnaires_created_count'] }}</td>
                            <td class="muted">{{ $w['questionnaires_created_users'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    {{-- ========= SECTION: Referrals ========= --}}
    <details class="section">
        <summary class="section-head">
            <div class="section-title">Referrals</div>
            <div class="section-badges">
                <span class="badge">Invited</span>
                <span class="badge">Opened</span>
                <span class="badge">Signed up</span>
                <span class="badge">Paid</span>
                <span class="badge">Rewarded</span>
            </div>
        </summary>
        <div class="section-body">
            <div class="table-responsive">
                <table aria-label="Referral weekly stats">
                    <thead>
                    <tr>
                        <th>Week</th>

                        <th>Invited</th>
                        <th class="muted">Referrers</th>

                        <th>Opened</th>
                        <th class="muted">Referrers</th>

                        <th>Signed</th>
                        <th class="muted">Referrers</th>

                        <th>Paid</th>
                        <th class="muted">Referrers</th>

                        <th>Rewarded</th>
                        <th class="muted">Referrers</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td>
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>

                            <td>{{ $w['referrals_invited_count'] }}</td>
                            <td class="muted">{{ $w['referrals_invited_users'] }}</td>

                            <td>{{ $w['referrals_opened_count'] }}</td>
                            <td class="muted">{{ $w['referrals_opened_users'] }}</td>

                            <td>{{ $w['referrals_signed_count'] }}</td>
                            <td class="muted">{{ $w['referrals_signed_users'] }}</td>

                            <td>{{ $w['referrals_paid_count'] }}</td>
                            <td class="muted">{{ $w['referrals_paid_users'] }}</td>

                            <td>{{ $w['referrals_rewarded_count'] }}</td>
                            <td class="muted">{{ $w['referrals_rewarded_users'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <p class="muted" style="margin-top:10px;">
                Notes: “Users” here means distinct <code>referrer_user_id</code> (referrers) for each step.
            </p>
        </div>
    </details>

    {{-- ========= SECTION: Documents ========= --}}
    <details class="section">
        <summary class="section-head">
            <div class="section-title">Documents</div>
            <div class="section-badges">
                <span class="badge">Signings created</span>
                <span class="badge">Completed</span>
            </div>
        </summary>
        <div class="section-body">
            <div class="table-responsive">
                <table aria-label="Document signing weekly stats">
                    <thead>
                    <tr>
                        <th>Week</th>
                        <th>Signings created</th>
                        <th>Completed (status=completed)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td>
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>
                            <td>{{ $w['doc_signings_created_count'] }}</td>
                            <td>{{ $w['doc_signings_completed_count'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <p class="muted" style="margin-top:10px;">
                DocumentSigning has no user_id in your model, so this section tracks totals only.
            </p>
        </div>
    </details>

    <div style="height:40px;"></div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
