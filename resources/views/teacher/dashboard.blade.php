<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="dashboard">

    <div class="header">
        <h2>Teacher Dashboard</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </div>

    <p>Welcome, {{ auth()->user()->name }}</p>

    <div class="card notifications-card">
        <h3>🔔 Notifications</h3>
        @if(auth()->user()->notifications->count())
            <div class="notifications-scroll">
                @foreach(auth()->user()->notifications as $notification)
                    <div class="notification-item">
                        <strong>{{ $notification->data['title'] }}</strong><br>
                        {{ $notification->data['message'] }}
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:#a0aec0;">No notifications yet</p>
        @endif
    </div>

    <div class="grid">
        <div class="card">
            <h3>Add Course</h3>
            <form method="POST" action="{{ route('teacher.courses.store') }}">
                @csrf
                <input type="text" name="title" placeholder="Course title" required>
                <button class="add-btn">Add</button>
            </form>
        </div>

        <div class="card">
            <h3>Your Courses</h3>
            <ul class="course-list">
                @forelse($courses as $course)
                    <li class="course-item">
                        <span class="course-title">{{ $course->title }}</span>
                        <div class="course-actions">
                            <a href="{{ route('teacher.courses.students', $course->id) }}" class="view-btn">
                                View Students
                            </a>
                            <form method="POST" action="{{ route('teacher.courses.delete', $course->id) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button class="icon-btn" onclick="return confirm('Delete this course?')">🗑️</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="empty">No courses added yet</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div id="teacher-ai" class="card">
    <h3>🤖 AI Help</h3>

    <div id="ai-messages">
        <div class="ai-msg">
            Hi 👋 I am your Teacher AI assistant.<br>
            Type <b>help</b> to see what you can ask.
        </div>
    </div>

    <div id="ai-input">
        <input type="text" id="ai-text" placeholder="Ask something...">
        <button onclick="sendTeacherAI()">Send</button>
    </div>
</div>

<script>
function sendTeacherAI(){
    let input=document.getElementById('ai-text');
    let msg=input.value.trim();
    if(!msg)return;

    let box=document.getElementById('ai-messages');

    box.innerHTML+=`<div class="ai-msg user">${msg}</div>`;
    input.value='';

    fetch('/teacher/ai-chat',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body:JSON.stringify({message:msg})
    })
    .then(res=>res.json())
    .then(data=>{
        let reply='';
        if(Array.isArray(data.reply)){
            reply='<ul style="padding-left:18px;">';
            data.reply.forEach(r=>reply+=`<li>${r}</li>`);
            reply+='</ul>';
        }else{
            reply=data.reply.replace(/\n/g,'<br>');
        }

        box.innerHTML+=`<div class="ai-msg bot">${reply}</div>`;
        box.scrollTop=box.scrollHeight;
    });
}

document.getElementById('ai-text').addEventListener('keydown',function(e){
    if(e.key==='Enter'){
        e.preventDefault();
        sendTeacherAI();
    }
});
</script>
</body>
</html>
