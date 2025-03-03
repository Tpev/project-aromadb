<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Chapter;
use App\Models\Lesson;

class TrainingController extends Controller
{
    public function index()
    {
        // Eager-load chapters -> lessons
        $trainings = Training::with('chapters.lessons')->get();

        return view('trainings.index', compact('trainings'));
    }

    public function show(Training $training)
    {
        // Eager-load nested relationships
        $training->load('chapters.lessons');

        return view('trainings.show', compact('training'));
    }
	
    public function showLesson(Training $training, Lesson $lesson)
    {
        // 1) Load all chapters + lessons for this training, sorted by chapter.position, then lesson.position
        // Eager-load: chapters -> lessons
        $training->load([
            'chapters' => function ($query) {
                $query->orderBy('position');
            },
            'chapters.lessons' => function ($query) {
                $query->orderBy('position');
            }
        ]);

        // 2) Flatten all lessons in the entire training in the correct order
        $allLessons = [];
        foreach ($training->chapters as $chapter) {
            foreach ($chapter->lessons as $l) {
                $allLessons[] = $l; 
            }
        }

        // 3) Find the index of the current lesson in this flattened list
        $currentIndex = collect($allLessons)->search(function ($l) use ($lesson) {
            return $l->id === $lesson->id;
        });

        // 4) Determine previous and next lessons
        $previousLesson = ($currentIndex > 0)
            ? $allLessons[$currentIndex - 1]
            : null;

        $nextLesson = ($currentIndex < count($allLessons) - 1)
            ? $allLessons[$currentIndex + 1]
            : null;

        // 5) Return the view
        return view('trainings.show-lesson', compact('training', 'lesson', 'previousLesson', 'nextLesson'));
    }
}
