<!-- resources/views/trainings/show-chapter.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-[#647a0b] leading-tight">
            {{ $training->title }} – {{ $chapter->title }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @foreach($chapter->lessons as $lesson)
            <article class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <h3 class="text-2xl font-bold mb-4 text-[#647a0b]">
                    {{ $lesson->title }}
                </h3>
                <div class="leading-relaxed text-gray-700">
                    {!! $lesson->content !!}
                </div>
            </article>
        @endforeach

        <div class="flex justify-between mt-8">
            <!-- Previous Chapter Link -->
            @if($previousChapter)
                <a
                    href="{{ route('trainings.show-chapter', [$training, $previousChapter]) }}"
                    class="btn btn-primary"
                >
                    &laquo; Chapitre précédent
                </a>
            @else
                <span class="btn btn-primary opacity-50 pointer-events-none">
                    &laquo; Chapitre précédent
                </span>
            @endif

            <!-- Next Chapter Link -->
            @if($nextChapter)
                <a
                    href="{{ route('trainings.show-chapter', [$training, $nextChapter]) }}"
                    class="btn btn-primary"
                >
                    Chapitre suivant &raquo;
                </a>
            @else
                <span class="btn btn-primary opacity-50 pointer-events-none">
                    Chapitre suivant &raquo;
                </span>
            @endif
        </div>
    </div>

    <!-- Inline Styles -->
    <style>
        /* Reusable Button Classes */
        .btn {
            font-weight: 600;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            transition: background-color 0.3s ease;
            text-decoration: none; /* Ensures no underline */
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary {
            background-color: #647a0b; /* Matches your primary brand color */
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #8ea633; /* Slightly lighter shade on hover */
        }

        /* Disabled/Inactive State */
        .opacity-50.pointer-events-none {
            cursor: not-allowed;
        }
    </style>
</x-app-layout>
