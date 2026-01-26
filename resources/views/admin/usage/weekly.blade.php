<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Usage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        #bg-video {
            position: fixed;
            top: 0; left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            filter: brightness(45%) blur(2px);
        }

        body{
            background: transparent;
            color:#f0f0f0;
            font-family:'Montserrat', sans-serif;
            overflow-x:hidden;
            margin:0; padding:0;
        }

        .container{
            max-width:1300px;
            margin:0 auto;
            padding:0 15px;
            position:relative;
            z-index:1;
        }

        .mt-5{ margin-top:2rem; }

        .page-title{
            font-size:2.2rem;
            font-weight:800;
            color:#fff;
            margin: 30px 0 20px;
            text-align:center;
            text-transform:uppercase;
            letter-spacing:2px;
            position:relative;
        }
        .page-title::after{
            content:'';
            width:180px;
            height:3px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            display:block;
            margin:18px auto 0;
            border-radius:2px;
        }

        .subhead{
            text-align:center;
            color:#cfcfe6;
            margin-bottom: 18px;
            font-size:0.95rem;
        }

        .toolbar{
            display:flex;
            gap:12px;
            justify-content:center;
            align-items:center;
            flex-wrap:wrap;
            margin: 14px 0 24px;
        }

        .pill{
            background: rgba(42,42,60,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 999px;
            padding: 10px 14px;
            box-shadow: 0 0 20px rgba(0,0,0,0.35);
            backdrop-filter: blur(6px);
            display:flex;
            gap:10px;
            align-items:center;
        }

        .pill label{
            font-size:0.85rem;
            color:#d6d6f2;
            opacity:0.95;
        }

        .pill input{
            width:90px;
            background: rgba(58,58,79,0.8);
            color:#fff;
            border:1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 6px 10px;
            outline:none;
        }

        .action-btn{
            display:inline-block;
            padding: 10px 18px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color:#fff;
            border-radius: 999px;
            text-decoration:none;
            border:0;
            cursor:pointer;
            font-weight:700;
            box-shadow: 0 0 10px rgba(255,81,47,0.5);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .action-btn:hover{ transform: scale(1.03); box-shadow: 0 0 18px rgba(255,81,47,0.7); }

        .kpis{
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap:12px;
            margin: 10px 0 18px;
        }

        .kpi{
            background-color: rgba(42,42,60,0.82);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 0 28px rgba(0,0,0,0.45);
            backdrop-filter: blur(6px);
            border:1px solid rgba(255,255,255,0.06);
        }

        .kpi .label{ color:#cfcfe6; font-size:0.85rem; margin-bottom:6px; }
        .kpi .value{ font-size:1.5rem; font-weight:800; color:#fff; }

        .table-responsive{
            background-color: rgba(42,42,60,0.82);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 40px;
            overflow-x:auto;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        table{
            width:100%;
            color:#f0f0f0;
            border-collapse: collapse;
        }

        thead{
            background: rgba(58,58,79,0.8);
        }

        th, td{
            padding: 14px;
            text-align:center;
            border-bottom: 1px solid rgba(58,58,79,0.85);
            white-space: nowrap;
        }

        th{
            text-transform: uppercase;
            font-size:0.92rem;
            position:relative;
        }

        th.sortable{ cursor:pointer; }

        th::after{
            content:'';
            position:absolute;
            left:50%;
            bottom:-10px;
            transform:translateX(-50%);
            width:56%;
            height:2px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border-radius:2px;
        }

        tbody tr{
            transition: background-color .25s ease, transform .25s ease;
        }

        tbody tr:hover{
            background-color: rgba(58,58,79,0.78);
            transform: scale(1.01);
        }

        .muted{
            color:#cfcfe6;
            font-size:0.85rem;
        }

        @media (max-width: 900px){
            .kpis{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 520px){
            .kpis{ grid-template-columns: 1fr; }
        }
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
                <div class="value">{{ number_format($totals['appointments']) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Invoices (total)</div>
                <div class="value">{{ number_format($totals['invoices']) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Quotes (total)</div>
                <div class="value">{{ number_format($totals['quotes']) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Weeks displayed</div>
                <div class="value">{{ count($weeks) }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="usageTable" aria-label="Weekly Usage">
                <thead>
                    <tr>
                        <th class="sortable" data-type="text">Week</th>
                        <th class="sortable" data-type="number">Active Therapists</th>

                        <th class="sortable" data-type="number">Appointments</th>
                        <th class="sortable" data-type="number">Therapists ➜ Appointments</th>

                        <th class="sortable" data-type="number">Invoices</th>
                        <th class="sortable" data-type="number">Therapists ➜ Invoices</th>

                        <th class="sortable" data-type="number">Quotes</th>
                        <th class="sortable" data-type="number">Therapists ➜ Quotes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $w)
                        <tr>
                            <td data-sort="{{ $w['yw'] }}">
                                {{ $w['week_start']->setTimezone('Europe/Paris')->format('d/m') }}
                                <span class="muted">→</span>
                                {{ $w['week_end']->setTimezone('Europe/Paris')->format('d/m') }}
                                <div class="muted">ISO {{ $w['yw'] }}</div>
                            </td>

                            <td data-sort="{{ $w['active_users'] }}">
                                <strong>{{ $w['active_users'] }}</strong>
                            </td>

                            <td data-sort="{{ $w['appointments_count'] }}">{{ $w['appointments_count'] }}</td>
                            <td data-sort="{{ $w['appointments_users'] }}">{{ $w['appointments_users'] }}</td>

                            <td data-sort="{{ $w['invoices_count'] }}">{{ $w['invoices_count'] }}</td>
                            <td data-sort="{{ $w['invoices_users'] }}">{{ $w['invoices_users'] }}</td>

                            <td data-sort="{{ $w['quotes_count'] }}">{{ $w['quotes_count'] }}</td>
                            <td data-sort="{{ $w['quotes_users'] }}">{{ $w['quotes_users'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById("usageTable");
            const headers = Array.from(table.querySelectorAll("thead th.sortable"));

            let sortState = { index: 0, asc: false };

            headers.forEach((th, index) => {
                th.addEventListener("click", () => {
                    const type = th.getAttribute("data-type") || "text";
                    const asc = (sortState.index === index) ? !sortState.asc : true;
                    sortState = { index, asc };
                    sortTable(table, index, asc, type);
                });
            });

            function sortTable(table, columnIndex, asc, type) {
                const tbody = table.tBodies[0];
                const rows = Array.from(tbody.querySelectorAll("tr"));

                const parse = (val) => {
                    if (type === "number") return parseInt(val || "0", 10);
                    return (val || "").toString();
                };

                rows.sort((a, b) => {
                    const aCell = a.querySelectorAll("td")[columnIndex];
                    const bCell = b.querySelectorAll("td")[columnIndex];

                    const aVal = aCell.getAttribute("data-sort") || aCell.textContent.trim();
                    const bVal = bCell.getAttribute("data-sort") || bCell.textContent.trim();

                    const A = parse(aVal);
                    const B = parse(bVal);

                    if (type === "number") return asc ? (A - B) : (B - A);
                    return asc ? A.localeCompare(B) : B.localeCompare(A);
                });

                rows.forEach(r => tbody.appendChild(r));
            }
        });
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
