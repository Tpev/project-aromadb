@php($serviceColor = \App\Support\ProductCalendarColor::forAppointment($appointment))
@if($serviceColor)
    <span data-service-color="{{ $serviceColor }}" aria-hidden="true"
          style="display:inline-block;width:10px;height:10px;min-width:10px;border-radius:50%;border:1px solid rgb(0 0 0 / 20%);background-color:{{ $serviceColor }};vertical-align:middle;margin-right:4px;"></span>
@endif
<span>{{ $appointment->product->name ?? '—' }}</span>
