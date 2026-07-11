{{ $body }}

---
Ce message vous est adressé par {{ $therapistName }} via Olithea.
@if($category === 'marketing')
Se désinscrire de ces suivis : {{ $unsubscribeUrl }}
@endif
