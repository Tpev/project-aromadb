@extends('admin.newsletter-imports.layout')
@section('content')
<section class="panel">
    <h2>Préparer une liste</h2>
    <p>Les contacts seront rattachés uniquement à ce praticien. Ils ne créeront pas de fiches clients.</p>
    <form method="POST" action="{{ route('admin.therapists.newsletter-imports.preview', $therapist) }}" enctype="multipart/form-data">
        @csrf
        <label for="audience_name">Nom de l’audience</label>
        <input id="audience_name" name="audience_name" type="text" maxlength="255" required value="{{ old('audience_name', 'Liste newsletter — '.$therapist->name) }}">
        <label for="csv_file">Fichier CSV</label>
        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" required aria-describedby="csv-help">
        <p id="csv-help" class="muted">4 Mo maximum, 10 000 lignes. EMAIL obligatoire ; NOM, PRENOM, OPT_IN et DOUBLE_OPT-IN facultatifs. Virgules, points-virgules et tabulations acceptés ; UTF-8 et Windows-1252.</p>
        <p class="notice">Les statuts d’inscription absents ou non confirmés seront classés « À vérifier » et exclus des envois. Les désabonnements déjà enregistrés sont conservés.</p>
        <button type="submit">Analyser le fichier</button>
        <p class="muted">Cette étape prépare un aperçu. Aucun contact n’est ajouté et aucun email n’est envoyé.</p>
    </form>
</section>
<section class="panel">
    <h2>Historique des imports</h2>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Fichier / audience</th><th>Administrateur</th><th>État</th></tr></thead><tbody>
        @forelse($imports as $import)
            <tr><td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('admin.therapists.newsletter-imports.show', [$therapist, $import]) }}">{{ $import->original_filename }}</a><br>{{ $import->audience_name }}</td>
                <td>{{ $import->creator?->name ?? 'Compte supprimé' }}</td><td>{{ $import->status === 'completed' ? 'Importé' : 'Aperçu' }}</td></tr>
        @empty<tr><td colspan="4">Aucun import pour ce praticien.</td></tr>@endforelse
    </tbody></table></div>
    {{ $imports->links() }}
</section>
@endsection
