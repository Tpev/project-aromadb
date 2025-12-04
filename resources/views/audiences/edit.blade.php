{{-- resources/views/audiences/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Modifier la liste / audience') }} - {{ $audience->name }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 space-y-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Modifier la liste</h1>
            <p class="text-sm text-gray-500">
                Mettez à jour le nom, la description ou les contacts de cette liste.
            </p>
        </div>

        <form action="{{ route('audiences.update', $audience) }}" method="POST">
            @csrf
            @method('PUT')
            @include('audiences._form')
        </form>
    </div>
</x-app-layout>
