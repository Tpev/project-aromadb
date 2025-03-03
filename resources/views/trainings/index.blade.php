<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Trainings') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-4">All Trainings</h1>
            
            <ul class="list-disc list-inside">
			@foreach($trainings as $training)
				<h2>{{ $training->title }}</h2>
				@if($training->chapters->count())
					<!-- Optionally get the first chapter's first lesson to jump in -->
					@php
						$firstChapter = $training->chapters->sortBy('position')->first();
						$firstLesson = optional($firstChapter)->lessons->sortBy('position')->first();
					@endphp

					@if($firstLesson)
						<a href="{{ route('trainings.show-lesson', [$training, $firstLesson]) }}">
							Commencer la formation
						</a>
					@endif
				@endif
			@endforeach

            </ul>
        </div>
    </div>
</x-app-layout>
