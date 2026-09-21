@if(is_array($answer))
    <ul>
        @foreach($answer as $choice)
            @if(is_scalar($choice))
                <li>{{ $choice }}</li>
            @endif
        @endforeach
    </ul>
@else
    {{ is_scalar($answer) ? $answer : '—' }}
@endif
