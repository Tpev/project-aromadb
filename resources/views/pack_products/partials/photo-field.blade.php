@php
    $savedPhoto = ($pack ?? null)?->photo_url;
    $removePhoto = filter_var(old('remove_photo', false), FILTER_VALIDATE_BOOLEAN);
@endphp
<section class="pack-photo-field" aria-labelledby="pack-photo-title" x-data="{
    saved: @js($savedPhoto), preview: @js($removePhoto ? null : $savedPhoto), removed: @js($removePhoto), objectUrl: null, error: '',
    release() { if (this.objectUrl) URL.revokeObjectURL(this.objectUrl); this.objectUrl = null; },
    reset() { this.release(); this.$refs.file.value = ''; this.$refs.file.setCustomValidity(''); this.error = ''; this.removed = false; this.preview = this.saved; },
    remove() { this.reset(); this.removed = true; this.preview = null; },
    reject(message) { this.error = message; this.$refs.file.value = ''; this.$refs.file.setCustomValidity(message); },
    async choose(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.error = ''; this.$refs.file.setCustomValidity('');
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { this.reject('Choisissez un fichier JPG, PNG ou WebP.'); return; }
        if (file.size > @js(\App\Services\PackPhotoService::maxBytes())) { this.reject('La photo dépasse la taille maximale autorisée.'); return; }
        const url = URL.createObjectURL(file);
        const img = new Image();
        try {
            await new Promise((resolve, reject) => { img.onload = resolve; img.onerror = reject; img.src = url; });
            if (this.$refs.file.files[0] !== file) { URL.revokeObjectURL(url); return; }
            if (img.naturalWidth > 4096 || img.naturalHeight > 4096) { URL.revokeObjectURL(url); this.reject('Maximum : 4 096 pixels de chaque côté.'); return; }
            this.release(); this.objectUrl = url; this.preview = url; this.removed = false;
        } catch (_) { URL.revokeObjectURL(url); if (this.$refs.file.files[0] === file) this.reject('Impossible de lire cette photo. Choisissez un autre fichier.'); }
    },
    destroy() { this.release(); }
}">
    <h3 id="pack-photo-title">Photo du pack <span>— facultative</span></h3>
    <p>Choisissez une photo illustrant votre accompagnement ou votre activité.</p>
    <p id="pack-photo-help"><strong>JPG, PNG ou WebP · {{ \App\Support\UploadLimit::formatBytes(\App\Services\PackPhotoService::maxBytes()) }} maximum</strong><br>
        Format horizontal conseillé : <strong>1 200 × 675 pixels (16:9)</strong>. Maximum : 4 096 pixels de chaque côté.<br>
        Évitez les textes intégrés à l’image et gardez le sujet au centre. La photo sera recadrée et optimisée automatiquement.
    </p>
    <label for="pack-photo" class="pack-photo-label">{{ $savedPhoto ? 'Remplacer la photo' : 'Ajouter une photo' }}</label>
    <input type="file" id="pack-photo" name="photo" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="choose($event)" aria-describedby="pack-photo-help pack-photo-error">
    <p id="pack-photo-error" role="alert" x-show="error" x-text="error" x-cloak class="pack-photo-error"></p>
    @error('photo')<p role="alert" class="pack-photo-error">{{ $message }}</p>@enderror
    <figure x-show="preview" @if(!$savedPhoto || $removePhoto) x-cloak @endif>
        <img @if($savedPhoto && !$removePhoto) src="{{ $savedPhoto }}" @endif :src="preview || undefined" alt="Aperçu du cadrage de la photo du pack" width="1200" height="675">
        <figcaption>Aperçu du cadrage sur votre portail</figcaption>
    </figure>
    <div class="pack-photo-actions">
        <input type="hidden" name="remove_photo" value="{{ $removePhoto ? '1' : '0' }}" :value="removed ? '1' : '0'">
        <button type="button" @click="remove()" x-show="preview" @if(!$savedPhoto || $removePhoto) x-cloak @endif>Supprimer la photo</button>
        <button type="button" @click="reset()" x-show="objectUrl || removed || error" x-cloak>Annuler le changement de photo</button>
    </div>
    <p x-show="removed" x-cloak>La photo sera supprimée à l’enregistrement.</p>
    @error('remove_photo')<p role="alert" class="pack-photo-error">{{ $message }}</p>@enderror
</section>
@once
<style>
    .pack-photo-field { padding:16px;border:1px solid #e4e8d5;border-radius:12px;min-width:0; }
    .pack-photo-field h3 { margin:0;font-size:14px;font-weight:600;color:#374151; }
    .pack-photo-field h3 span { font-weight:400;color:#6b7280; }
    .pack-photo-field p { margin:8px 0;font-size:12px;line-height:1.6;color:#6b7280; }
    .pack-photo-label { display:block;margin:12px 0 6px;font-size:13px;font-weight:600;color:#374151; }
    .pack-photo-field input[type=file] { display:block;max-width:100%;font-size:12px; }
    .pack-photo-field figure { margin:12px 0 0;max-width:320px; }
    .pack-photo-field figure img { display:block;width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;border-radius:8px; }
    .pack-photo-field figcaption { margin-top:5px;font-size:11px;color:#6b7280; }
    .pack-photo-actions { display:flex;flex-wrap:wrap;gap:12px;margin-top:10px; }
    .pack-photo-actions button { border:0;background:none;padding:4px 0;font-size:12px;text-decoration:underline;color:#647a0b;cursor:pointer; }
    .pack-photo-field p.pack-photo-error { color:#b91c1c; }
</style>
@endonce
