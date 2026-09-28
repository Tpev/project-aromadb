<label class="event-mobile-sort" for="{{ $tableId }}-sort">
    <span>Trier par</span>
    <select id="{{ $tableId }}-sort" onchange="sortTable(Number(this.value.split(':')[0]), '{{ $tableId }}', this.value.split(':')[1] === 'asc')">
        <option value="1:asc">Date : plus ancien</option>
        <option value="1:desc">Date : plus récent</option>
        <option value="0:asc">Nom : A → Z</option>
        <option value="0:desc">Nom : Z → A</option>
        <option value="2:asc">Lieu : A → Z</option>
        <option value="2:desc">Lieu : Z → A</option>
        <option value="3:asc">Réservations : croissant</option>
        <option value="3:desc">Réservations : décroissant</option>
    </select>
</label>
