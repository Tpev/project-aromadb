<meta property="og:title" content="{{ $socialTitle }}">
<meta property="og:description" content="{{ $socialDescription }}">
<meta property="og:url" content="{{ $socialUrl }}">
<meta property="og:type" content="event">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="fr_FR">
<meta property="og:image" content="{{ $socialImage['url'] }}">
<meta property="og:image:url" content="{{ $socialImage['url'] }}">
<meta property="og:image:secure_url" content="{{ $socialImage['secure_url'] }}">
@if($socialImage['mime_type'])
    <meta property="og:image:type" content="{{ $socialImage['mime_type'] }}">
@endif
@if($socialImage['width'] && $socialImage['height'])
    <meta property="og:image:width" content="{{ $socialImage['width'] }}">
    <meta property="og:image:height" content="{{ $socialImage['height'] }}">
@endif
<meta property="og:image:alt" content="{{ $socialImage['alt'] }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $socialTitle }}">
<meta name="twitter:description" content="{{ $socialDescription }}">
<meta name="twitter:image" content="{{ $socialImage['url'] }}">
<meta name="twitter:image:alt" content="{{ $socialImage['alt'] }}">
