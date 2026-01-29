<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
</head>
<body>

<div class="dashboard">

    <div class="dashboard-grid">

        <div class="dashboard-main">

            <div class="header">
                <h2>🎓 Student Dashboard</h2>

                <div style="display:flex; gap:20px; align-items:center;">
                    <div style="position:relative;">
                        🔔
                        @if(auth()->user()->unreadNotifications->count())
                            <span style="
                                position:absolute;
                                top:-8px;
                                right:-10px;
                                background:#ef4444;
                                color:white;
                                font-size:11px;
                                padding:2px 6px;
                                border-radius:50%;
                                font-weight:600;
                            ">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>

            <p class="welcome">
                Welcome, {{ auth()->user()->name }}
            </p>

            @if(session('error'))
                <div class="error-alert">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="success-alert">{{ session('success') }}</div>
            @endif

            @if(auth()->user()->notifications->count())
                <div class="card notifications-card">
                    <h3>🔔 Notifications</h3>
                    <div class="notifications-scroll">
                        @foreach(auth()->user()->notifications as $notification)
                            <div class="notification-item">
                                <strong>{{ $notification->data['title'] }}</strong><br>
                                {{ $notification->data['message'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card">
                <h3>Available Courses</h3>

                <div class="course-list">
                    @forelse($courses as $course)

                        @php
                            $isEnrolled = auth()->user()
                                ->courses()
                                ->where('course_id', $course->id)
                                ->exists();

                            $teacherActive = $course->teacher && $course->teacher->status === 'active';
                        @endphp

                        <div class="course-item">
                            <div class="course-title">{{ $course->title }}</div>
                            <div class="course-teacher">
                                Teacher: {{ $course->teacher->name ?? 'N/A' }}
                            </div>

                            <div class="course-actions">
                                @if($isEnrolled)
                                    <span class="enrolled-badge">Enrolled</span>
                                    <form method="POST" action="{{ route('courses.unenroll', $course->id) }}">
                                        @csrf
                                        <button class="unenroll-btn">Unenroll</button>
                                    </form>
                                @else
                                    @if($teacherActive)
                                        <form method="POST" action="{{ route('courses.enroll', $course->id) }}">
                                            @csrf
                                            <button class="enroll-btn-sm">Enroll</button>
                                        </form>
                                    @else
                                        <button class="enroll-btn-sm" disabled
                                                style="background:#9ca3af; cursor:not-allowed;">
                                            Teacher not available
                                        </button>
                                    @endif

                                    <a href="{{ route('courses.show', $course->id) }}" class="view-btn">
                                        View Course
                                    </a>
                                @endif
                            </div>
                        </div>

                    @empty
                        <p class="empty">No courses available</p>
                    @endforelse
                </div>
            </div>

        </div>

        <div id="ai-chat">
    <div id="ai-header" onclick="toggleAI()">🤖 AI Help</div>

    <div id="ai-body">
        <div id="ai-messages">
            <div class="ai-msg bot">
                Hi {{ auth()->user()->name }} 👋 <br>
                Ask me anything about courses.
            </div>
        </div>

        <div id="ai-input">
            <input type="text" id="ai-text" placeholder="Ask..." />
            <button onclick="sendAI()">Send</button>
        </div>
    </div>
</div>


<script>
function sendAI() {
    let input = document.getElementById('ai-text');
    let msg = input.value.trim();
    if (!msg) return;

    let messages = document.getElementById('ai-messages');

    // USER MESSAGE
    messages.innerHTML += `<div class="ai-msg user">${msg}</div>`;
    input.value = '';

    fetch("{{ route('ai.chat') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ message: msg })
    })
    .then(res => res.json())
    .then(data => {
        let reply = '';

        // ARRAY RESPONSE 
        if (Array.isArray(data.reply)) {
            reply = '<ul style="padding-left:18px;">';
            data.reply.forEach(i => reply += `<li>${i}</li>`);
            reply += '</ul>';
        }
        // NORMAL TEXT
        else {
            reply = data.reply.replace(/\n/g, '<br>');
        }

        messages.innerHTML += `<div class="ai-msg bot">${reply}</div>`;
        messages.scrollTop = messages.scrollHeight;
    });
}
document.getElementById('ai-text').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendAI();
    }
});
function toggleAI(){
    document.getElementById('ai-body').classList.toggle('hide');
}
</script>
</body>
</html>
