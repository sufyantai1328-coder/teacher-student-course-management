<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class AIChatController extends Controller
{
    public function reply(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'student') {
            return response()->json([
                'reply' => 'Please login as student.'
            ]);
        }

        $msg = strtolower($request->message);
        $user = auth()->user();

        // HELP
        if (str_contains($msg, 'help')) {
            return response()->json([
                'reply' => [
                    'available courses',
                    'my enrolled courses',
                    'is java available',
                    'is linux available',
                    'how to enroll',
                    'how to unenroll',
                    'course details java',
                ]
            ]);
        }

        // AVAILABLE COURSES
        if (str_contains($msg, 'available courses')) {
            $courses = Course::whereHas('teacher', function ($q) {
                $q->where('status', 'active');
            })->pluck('title');

            return response()->json([
                'reply' => $courses->count()
                    ? 'Available courses: ' . $courses->implode(', ')
                    : 'No courses available right now.'
            ]);
        }

        // CHECK COURSE AVAILABILITY
        if (str_contains($msg, 'is')) {
            preg_match('/is (.+) available/', $msg, $m);
            $courseName = $m[1] ?? null;

            if ($courseName) {
                $course = Course::where('title', 'LIKE', "%$courseName%")->first();

                if (!$course) {
                    return response()->json(['reply' => 'Course not found.']);
                }

                if (!$course->teacher || $course->teacher->status !== 'active') {
                    return response()->json([
                        'reply' => "{$course->title} is not available (teacher inactive)."
                    ]);
                }

                return response()->json([
                    'reply' => "{$course->title} is available."
                ]);
            }
        }

        // ENROLLED COURSES
        if (str_contains($msg, 'my enrolled')) {
            $courses = $user->courses()->pluck('title');

            return response()->json([
                'reply' => $courses->count()
                    ? 'You are enrolled in: ' . $courses->implode(', ')
                    : 'You are not enrolled in any course.'
            ]);
        }

        // COURSE DETAILS
        if (str_contains($msg, 'course details')) {
            preg_match('/course details (.+)/', $msg, $m);
            $courseName = $m[1] ?? null;

            if ($courseName) {
                $course = Course::where('title', 'LIKE', "%$courseName%")->first();

                if (!$course) {
                    return response()->json(['reply' => 'Course not found.']);
                }

                return response()->json([
                    'reply' =>
                        "Course: {$course->title}\n" .
                        "Teacher: " . ($course->teacher->name ?? 'N/A') . "\n" .
                        ($course->description ?? 'No description available.')
                ]);
            }
        }

        // ENROLL HELP
        if (str_contains($msg, 'enroll')) {
            return response()->json([
                'reply' => 'Click the Enroll button on the course card.'
            ]);
        }

        // UNENROLL HELP
        if (str_contains($msg, 'unenroll')) {
            return response()->json([
                'reply' => 'Click the Unenroll button on your enrolled course.'
            ]);
        }

        return response()->json([
            'reply' => 'Hi 👋 Type "help" to see what you can ask.'
        ]);
    }
}
