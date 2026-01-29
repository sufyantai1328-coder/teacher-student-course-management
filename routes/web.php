<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TeacherAIChatController;



Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/course-show', [CourseController::class, 'index'])
    ->name('courses.list');


Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.post');


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {

    Route::get('/student/dashboard', [DashboardController::class, 'studentDashboard'])
        ->name('student.dashboard');

    Route::get('/teacher/dashboard', [DashboardController::class, 'teacherDashboard'])
        ->name('teacher.dashboard');
});


Route::get('/courses/{id}', [StudentCourseController::class, 'show'])
    ->name('courses.show');

Route::post('/courses/{id}/enroll', [StudentCourseController::class, 'enroll'])
    ->name('courses.enroll');

Route::post('/courses/{id}/unenroll', [StudentCourseController::class, 'unenroll'])
    ->name('courses.unenroll');


Route::middleware('auth')->group(function () {

    Route::post('/teacher/courses', [TeacherCourseController::class, 'store'])
        ->name('teacher.courses.store');

    Route::delete('/teacher/courses/{id}', [TeacherCourseController::class, 'destroy'])
        ->name('teacher.courses.delete');

    Route::get('/teacher/courses/{id}/students',
        [TeacherCourseController::class, 'students']
    )->name('teacher.courses.students');

    // ✅ TEACHER AI CHAT (FIXED)
    Route::post('/teacher/ai-chat', [TeacherAIChatController::class, 'reply']);
});


Route::post('/ai-chat', [App\Http\Controllers\AIChatController::class, 'reply'])
    ->name('ai.chat')
    ->middleware('auth');


Route::get('/test-mail', function () {
    Mail::raw('Test Mail From Laravel', function ($message) {
        $message->to('test@gmail.com')->subject('Laravel Test Mail');
    });

    return 'Mail sent';
});
