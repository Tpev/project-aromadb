@component('mail::message')
# Milestone Reached!

Congratulations!

Your website has reached a new milestone of **{{ $milestone }}** total sessions.

- **Current Total Sessions:** {{ $sessionsTotal }}

Keep up the great work!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
