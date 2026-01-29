<!DOCTYPE html>
<html>
<head>
    <title>Register Page.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <h2>Create Account</h2>

        <p class="sub-text">
            @if(isset($role))
                Registering as {{ ucfirst($role) }}
            @else
                Register as Student or Teacher
            @endif
        </p>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

            
            @if(isset($role))
                <input type="hidden" name="role" value="{{ $role }}">
            @endif

            
            <select {{ isset($role) ? 'disabled' : '' }} required>
                <option value="">Select Role</option>

                <option value="student"
                    {{ (old('role', $role ?? '') === 'student') ? 'selected' : '' }}>
                    Student
                </option>

                <option value="teacher"
                    {{ (old('role', $role ?? '') === 'teacher') ? 'selected' : '' }}>
                    Teacher
                </option>
            </select>

            <button type="submit">Register</button>
        </form>

        <p class="switch-link">
            Already have an account ?
            <a href="{{ route('login') }}">Login</a>
        </p>

    </div>
</div>

</body>
</html>
