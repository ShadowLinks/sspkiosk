@extends('layouts.kiosk')

@section('title', 'Already registered')

@section('content')
    <h1>Already registered</h1>

    @if ($student)
        <p>
            <strong>{{ $student->name }}</strong>, you are already registered for kiosk password reset assistance.
        </p>
    @else
        <p>You are already registered for kiosk password reset assistance.</p>
    @endif

    <p>If you forget your password, visit a district kiosk and follow the on-screen instructions to request help.</p>

    <p class="muted">Technology staff must approve each reset request before your password is changed.</p>
@endsection
