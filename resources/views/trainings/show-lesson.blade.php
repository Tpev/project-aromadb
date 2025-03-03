<!-- resources/views/trainings/show-lesson.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-[#647a0b] leading-tight">
            {{ $training->title }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Grid Layout: Sidebar on the left, content on the right -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- LEFT SIDEBAR (Chapters & Lessons Menu) -->
            <nav class="col-span-1 bg-white shadow-lg rounded-lg p-4">
                <h3 class="text-xl font-bold mb-4 text-[#854f38]">Chapitres</h3>
                @foreach($training->chapters as $c)
                    <div class="mb-4">
                        <!-- Chapter Title -->
                        <h4 class="text-lg font-semibold text-gray-600 mb-2">
                            {{ $c->title }}
                        </h4>
                        <!-- Lessons within this Chapter -->
                        <ul class="ml-4 list-inside space-y-1">
                            @foreach($c->lessons as $l)
                                <li>
                                    <a 
                                        href="{{ route('trainings.show-lesson', [$training, $l]) }}"
                                        class="block px-2 py-1 rounded
                                            @if($l->id === $lesson->id)
                                                bg-[#854f38] text-white
                                            @else
                                                text-gray-700 hover:bg-[#854f38] hover:text-white
                                            @endif
                                        "
                                    >
                                        {{ $l->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <!-- MAIN CONTENT: Current Lesson -->
            <div class="col-span-1 md:col-span-3">
                <!-- Card container for the lesson content -->
                <article class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-2xl font-bold mb-4 text-[#647a0b]">
                        {{ $lesson->title }}
                    </h3>
                    <div class="prose leading-relaxed text-gray-700">
                        {!! $lesson->content !!}
                    </div>
                </article>

                <!-- Next/Prev Lesson Buttons -->
                <div class="flex justify-between mt-8">
                    @if($previousLesson)
                        <a
                            href="{{ route('trainings.show-lesson', [$training, $previousLesson]) }}"
                            class="btn btn-primary"
                        >
                            &laquo; Leçon précédente
                        </a>
                    @else
                        <span class="btn btn-primary opacity-50 pointer-events-none">
                            &laquo; Leçon précédente
                        </span>
                    @endif

                    @if($nextLesson)
                        <a
                            href="{{ route('trainings.show-lesson', [$training, $nextLesson]) }}"
                            class="btn btn-primary"
                        >
                            Leçon suivante &raquo;
                        </a>
                    @else
                        <span class="btn btn-primary opacity-50 pointer-events-none">
                            Leçon suivante &raquo;
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inline styles for the button classes -->
    <style>
        .btn {
            font-weight: 600;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #647a0b; /* Primary brand color */
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #8ea633;
        }
        .opacity-50.pointer-events-none {
            cursor: not-allowed;
        }
    </style>
</x-app-layout>
