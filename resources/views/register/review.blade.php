@extends('layouts.kiosk')

@section('title', 'Finish registration')

@section('content')
    <h1>Finish registration</h1>

    <p>Signed in as <strong>{{ $student->name }}</strong> ({{ $student->email }}).</p>

    @if (session('status'))
        <p class="status" role="status">{{ session('status') }}</p>
    @endif

    <div class="notice" role="note">{{ $notice }}</div>

    <ul class="checklist">
        <li>Security questions saved: <strong>{{ $questionCount }}</strong></li>
        <li>Registration photo saved: <strong>Yes</strong></li>
    </ul>

    <p>When you finish, you will be registered for kiosk password reset assistance at your school.</p>

    <form method="post" action="{{ route('register.complete') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Complete registration</button>
    </form>
@endsection
