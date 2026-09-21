@extends('admin.newsletter-imports.layout')
@section('content')
@php($labels = ['ready' => 'Nouveau, inscrit', 'pending' => 'À vérifier', 'existing' => 'Déjà enregistré', 'unsubscribed' => 'Désabonné — exclu', 'invalid' => 'Ligne invalide — exclue', 'duplicate' => 'Doublon CSV — exclu'])
<section class="panel">
    <h2>{{ $import->audience_name }}</h2>
    <p>{{ $import->original_filename }} · Préparé par {{ $import->creator?->name ?? 'Compte supprimé' }} le {{ $import->created_at->format('d/m/Y à H:i') }}</p>
    <div class="stats">
        <div class="stat"><strong>{{ $report['total'] }}</strong>Lignes</div>
        @foreach($labels as $key => $label)<div class="stat"><strong>{{ $report[$key] }}</strong>{{ $label }}</div>@endforeach
    </div>
    <p class="muted">{{ $report['client_matches'] }} adresse(s) correspondent aussi à une fiche client. Une même adresse ne recevra qu’un email par envoi.</p>
    @if($import->status === 'completed')
        <p class="success"><strong>Import terminé le {{ $import->committed_at->format('d/m/Y à H:i') }}.</strong> {{ $report['created'] ?? 0 }} nouveau(x) contact(s). Aucun email envoyé par l’import.</p>
        @if($import->audience)<p>Le praticien peut sélectionner « {{ $import->audience->name }} » dans sa newsletter.</p>
        @else<p class="notice">L’audience a été supprimée depuis cet import.</p>@endif
    @else
        <p class="notice">Les contacts « À vérifier » seront enregistrés sans être activés. Les contacts existants conserveront leur statut et leurs informations. Les lignes invalides, les doublons du fichier et les désabonnés seront ignorés.</p>
        <form method="POST" action="{{ route('admin.therapists.newsletter-imports.commit', [$therapist, $import]) }}">
            @csrf
            <label class="check"><input type="checkbox" name="confirm" value="1" required><span>Je confirme l’import dans le compte de <strong>{{ $therapist->name }}</strong> et la création de cette audience.</span></label>
            <button type="submit" @disabled($report['ready'] + $report['pending'] + $report['existing'] === 0)>Valider l’import</button>
        </form>
    @endif
    <p><a href="{{ route('admin.therapists.newsletter-imports.index', $therapist) }}">Retour aux imports</a></p>
</section>
<section class="panel">
    <h2>Détail des lignes</h2>
    <div class="table-wrap"><table><thead><tr><th>Ligne</th><th>Email</th><th>Prénom / nom</th><th>Opt-in / double opt-in</th><th>Résultat</th></tr></thead><tbody>
        @foreach($rows as $row)<tr>
            <td>{{ $row['line'] }}</td><td>{{ $row['email'] ?: '—' }}</td>
            <td>{{ trim($row['first_name'].' '.$row['last_name']) ?: '—' }}</td>
            <td>{{ $row['opt_in'] ?: 'Non renseigné' }} / {{ $row['double_opt_in'] ?: 'Non renseigné' }}</td>
            <td>{{ $labels[$row['result']] }}</td>
        </tr>@endforeach
    </tbody></table></div>
    {{ $rows->links() }}
</section>
@endsection
