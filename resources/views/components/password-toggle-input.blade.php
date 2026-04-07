@props([
    'id',
    'name',
    'value' => '',
    'required' => false,
    'autocomplete' => 'current-password',
    'placeholder' => null,
])

<div x-data="{ shown: false }" class="relative">
    <input
        {{ $attributes->merge([
            'id' => $id,
            'name' => $name,
            'type' => 'password',
            'value' => $value,
            'autocomplete' => $autocomplete,
            'placeholder' => $placeholder,
            'class' => 'block mt-1 w-full pr-20',
        ]) }}
        x-bind:type="shown ? 'text' : 'password'"
        @if($required) required @endif
    >

    <button
        type="button"
        x-on:click="shown = !shown"
        class="absolute inset-y-0 right-0 flex items-center px-3 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none"
        x-bind:aria-label="shown ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
        x-bind:title="shown ? 'Masquer' : 'Afficher'"
    >
        <span x-show="!shown">Afficher</span>
        <span x-show="shown" x-cloak>Masquer</span>
    </button>
</div>
