<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $training->title }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-4">{{ $training->title }}</h1>
            <p class="mb-4">{{ $training->description }}</p>

            @foreach($training->chapters->sortBy('position') as $chapter)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">{{ $chapter->title }}</h2>

                    @foreach($chapter->lessons->sortBy('position') as $lesson)
                        <div class="mb-4">
                            <h3 class="text-lg font-medium mb-1">{{ $lesson->title }}</h3>
                            <div class="prose">
                                {!! $lesson->content !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
