<div class="details-box">
    <label class="details-label" for="name">Nom de l'entreprise (raison sociale) *</label>
    <input type="text" id="name" name="name" class="form-control"
           value="{{ old('name', $company->name ?? '') }}" required>
    @error('name') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="trade_name">Nom commercial</label>
    <input type="text" id="trade_name" name="trade_name" class="form-control"
           value="{{ old('trade_name', $company->trade_name ?? '') }}">
    @error('trade_name') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="siret">SIRET</label>
    <input type="text" id="siret" name="siret" class="form-control"
           value="{{ old('siret', $company->siret ?? '') }}">
    @error('siret') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="vat_number">Numéro de TVA intracommunautaire</label>
    <input type="text" id="vat_number" name="vat_number" class="form-control"
           value="{{ old('vat_number', $company->vat_number ?? '') }}">
    @error('vat_number') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<hr class="my-4">

<h3 class="text-lg font-semibold mb-2" style="color:#647a0b;">Adresse de facturation</h3>

<div class="details-box">
    <label class="details-label" for="billing_address">Adresse</label>
    <input type="text" id="billing_address" name="billing_address" class="form-control"
           value="{{ old('billing_address', $company->billing_address ?? '') }}">
    @error('billing_address') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="billing_zip">Code postal</label>
    <input type="text" id="billing_zip" name="billing_zip" class="form-control"
           value="{{ old('billing_zip', $company->billing_zip ?? '') }}">
    @error('billing_zip') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="billing_city">Ville</label>
    <input type="text" id="billing_city" name="billing_city" class="form-control"
           value="{{ old('billing_city', $company->billing_city ?? '') }}">
    @error('billing_city') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="billing_country">Pays</label>
    <input type="text" id="billing_country" name="billing_country" class="form-control"
           value="{{ old('billing_country', $company->billing_country ?? '') }}">
    @error('billing_country') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<hr class="my-4">

<h3 class="text-lg font-semibold mb-2" style="color:#647a0b;">Contact facturation</h3>

<div class="details-box">
    <label class="details-label" for="billing_email">Email facturation</label>
    <input type="email" id="billing_email" name="billing_email" class="form-control"
           value="{{ old('billing_email', $company->billing_email ?? '') }}">
    @error('billing_email') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="billing_phone">Téléphone facturation</label>
    <input type="text" id="billing_phone" name="billing_phone" class="form-control"
           value="{{ old('billing_phone', $company->billing_phone ?? '') }}">
    @error('billing_phone') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<hr class="my-4">

<h3 class="text-lg font-semibold mb-2" style="color:#647a0b;">Contact principal</h3>

<div class="details-box">
    <label class="details-label" for="main_contact_first_name">Prénom</label>
    <input type="text" id="main_contact_first_name" name="main_contact_first_name" class="form-control"
           value="{{ old('main_contact_first_name', $company->main_contact_first_name ?? '') }}">
    @error('main_contact_first_name') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="main_contact_last_name">Nom</label>
    <input type="text" id="main_contact_last_name" name="main_contact_last_name" class="form-control"
           value="{{ old('main_contact_last_name', $company->main_contact_last_name ?? '') }}">
    @error('main_contact_last_name') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="main_contact_email">Email</label>
    <input type="email" id="main_contact_email" name="main_contact_email" class="form-control"
           value="{{ old('main_contact_email', $company->main_contact_email ?? '') }}">
    @error('main_contact_email') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="main_contact_phone">Téléphone</label>
    <input type="text" id="main_contact_phone" name="main_contact_phone" class="form-control"
           value="{{ old('main_contact_phone', $company->main_contact_phone ?? '') }}">
    @error('main_contact_phone') <p class="text-red-500">{{ $message }}</p> @enderror
</div>

<div class="details-box">
    <label class="details-label" for="notes">Notes internes</label>
    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $company->notes ?? '') }}</textarea>
    @error('notes') <p class="text-red-500">{{ $message }}</p> @enderror
</div>
