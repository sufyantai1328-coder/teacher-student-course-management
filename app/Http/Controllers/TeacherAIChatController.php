<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;

class TeacherAIChatController extends Controller
{
    public function reply(Request $request)
    {
        // ✅ Teacher only
        if (!auth()->check() || auth()->user()->role !== 'teacher') {
            return response()->json([
                'reply' => 'Only teachers can use this AI.'
            ]);
        }

        if (!$request->message) {
            return response()->json([
                'reply' => 'Please type something.'
            ]);
        }

        $teacher = auth()->user();
        $msg = strtolower($request->message);

        // ================= HELP =================
        if (str_contains($msg, 'help')) {
            return response()->json([
                'reply' =>
                    "You can ask:\n" .
                    "- my courses\n" .
                    "- students in react\n" .
                    "- students email in react\n" .
                    "- how many students\n" .
                    "- latest enrollment\n" .
                    "- add course"
            ]);
        }

        // ================= MY COURSES =================
        if (str_contains($msg, 'my courses')) {

            $courses = Course::where('teacher_id', $teacher->id)
                ->pluck('title');

            if ($courses->isEmpty()) {
                return response()->json([
                    'reply' => 'You have not added any courses yet.'
                ]);
            }

            return response()->json([
                'reply' => 'Your courses: ' . $courses->implode(', ')
            ]);
        }

        // ================= TOTAL STUDENTS =================
        if (str_contains($msg, 'how many students') || str_contains($msg, 'total students')) {

            $count = User::whereHas('enrollments.course', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->count();

            return response()->json([
                'reply' => "Total enrolled students: $count"
            ]);
        }

        // ================= STUDENTS IN COURSE =================
        if (str_contains($msg, 'students in')) {

            preg_match('/students in (.+)/', $msg, $matches);
            $courseName = trim($matches[1] ?? '');

            $course = Course::where('teacher_id', $teacher->id)
                ->where('title', 'LIKE', "%$courseName%")
                ->first();

            if (!$course) {
                return response()->json([
                    'reply' => 'Course not found.'
                ]);
            }

            $students = $course->students()->pluck('name');

            if ($students->isEmpty()) {
                return response()->json([
                    'reply' => 'No students enrolled in this course.'
                ]);
            }

            return response()->json([
                'reply' =>
                    'Students in ' . $course->title . ': ' .
                    $students->implode(', ')
            ]);
        }

        // ================= STUDENTS EMAIL =================
        if (str_contains($msg, 'students email in')) {

            preg_match('/students email in (.+)/', $msg, $matches);
            $courseName = trim($matches[1] ?? '');

            $course = Course::where('teacher_id', $teacher->id)
                ->where('title', 'LIKE', "%$courseName%")
                ->first();

            if (!$course) {
                return response()->json([
                    'reply' => 'Course not found.'
                ]);
            }

            $students = $course->students()->get(['name', 'email']);

            if ($students->isEmpty()) {
                return response()->json([
                    'reply' => 'No students enrolled.'
                ]);
            }

            $list = $students
                ->map(fn ($s) => "{$s->name} ({$s->email})")
                ->implode(', ');

            return response()->json([
                'reply' => $list
            ]);
        }

        // ================= LATEST ENROLLMENT =================
        if (str_contains($msg, 'latest enrollment')) {

            $notification = $teacher->notifications()->latest()->first();

            if (!$notification) {
                return response()->json([
                    'reply' => 'No enrollments yet.'
                ]);
            }

            return response()->json([
                'reply' => $notification->data['message']
            ]);
        }

        // ================= ADD COURSE =================
        if (str_contains($msg, 'add course') || str_contains($msg, 'create course')) {
            return response()->json([
                'reply' => 'Use the Add Course box on your dashboard.'
            ]);
        }

        // ================= DEFAULT =================
        return response()->json([
            'reply' => 'Hello 👨‍🏫 How can I help you? Type "help" to see options.'
        ]);
    }
}
