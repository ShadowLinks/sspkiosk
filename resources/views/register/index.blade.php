@extends('layouts.kiosk')

@section('title', 'Register')

@section('content')
    <h1>Student password reset registration</h1>

    @if (session('error'))
        <div class="alert-error" role="alert">{{ session('error') }}</div>
    @endif

    @if (! $isConfigured)
        <div class="alert-error" role="alert">
            Registration is not available right now. Please contact your school technology staff.
        </div>
    @else
        <div class="notice" role="note">
            {{ $notice }}
        </div>

        <p>Sign in with your school Google account to begin registration for kiosk password reset assistance.</p>

        <a class="btn btn-primary" href="{{ route('auth.google.redirect') }}">
            Sign in with Google
        </a>
    @endif

    <p class="muted">You must be signed in with your student Google Workspace account.</p>
@endsection
