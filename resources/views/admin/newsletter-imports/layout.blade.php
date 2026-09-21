<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import newsletter — {{ $therapist->name }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f6f1;color:#20291d;font:16px/1.5 system-ui,sans-serif}
        main{max-width:1120px;margin:32px auto;padding:0 20px;overflow-wrap:anywhere}a{color:#526408}h1{font-size:28px;line-height:1.2}h2{font-size:20px}
        .panel{background:white;border:1px solid #dce2d3;border-radius:12px;padding:24px;margin:20px 0}.muted{color:#5c6758}.notice{background:#fff6dc;border:1px solid #e8ce83;padding:16px;border-radius:8px}
        label{display:block;font-weight:600;margin:16px 0 6px}input[type=text],input[type=file]{width:100%;max-width:620px;padding:10px;border:1px solid #bac4b1;border-radius:6px;font:inherit}
        button,.button{display:inline-block;background:#647a0b;color:white;padding:12px 18px;border:0;border-radius:7px;font:inherit;text-decoration:none;cursor:pointer}button:disabled{opacity:.5;cursor:not-allowed}
        .error{color:#9d2923}.success{color:#38631c}.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px}.stat{padding:14px;background:#f3f6ec;border-radius:8px}.stat strong{display:block;font-size:26px}
        .table-wrap{overflow:auto}table{width:100%;min-width:700px;border-collapse:collapse;text-align:left;font-size:14px}th,td{padding:12px;border-bottom:1px solid #e3e8db;vertical-align:top}td{overflow-wrap:anywhere}th{background:#f3f6ec}.check{font-weight:400;display:flex;gap:10px;align-items:flex-start;margin:20px 0}.check input{margin-top:6px}
        nav[role=navigation]{margin-top:20px}nav[role=navigation] svg{width:20px;height:20px}nav[role=navigation] a,nav[role=navigation] span{margin-right:8px}
        @media(max-width:600px){main{margin:20px auto;padding:0 12px}.panel{padding:16px}h1{font-size:24px}}
    </style>
</head>
<body><main>
    <a href="{{ route('admin.therapists.show', $therapist) }}">← Fiche admin du praticien</a>
    <h1>Import de contacts newsletter</h1>
    <p><strong>{{ $therapist->name }}</strong> · {{ $therapist->email }} · Compte #{{ $therapist->id }}</p>
    @if(session('success'))<p role="status" class="panel success">{{ session('success') }}</p>@endif
    @if($errors->any())<div role="alert" class="panel error">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
    @yield('content')
</main></body></html>
