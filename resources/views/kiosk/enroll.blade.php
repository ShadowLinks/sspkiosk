@extends('layouts.kiosk')

@section('title', 'Kiosk enrollment')

@section('content')
    <h1>Enroll this kiosk</h1>

    @if (session('error'))
        <div class="alert-error" role="alert">{{ session('error') }}</div>
    @endif

    @if (session('success'))
        <p class="status" role="status">{{ session('success') }}</p>
    @endif

    <p>Enter the one-time enrollment code from technology staff.</p>

    <form method="post" action="{{ route('kiosk.enroll') }}">
        @csrf
        <label>
            Enrollment code
            <input
                type="text"
                name="enrollment_code"
                value="{{ old('enrollment_code') }}"
                required
                autocomplete="off"
                maxlength="64"
            >
        </label>
        <button type="submit" class="btn btn-primary">Enroll kiosk</button>
    </form>

    <p class="muted">API clients may still enroll with <code>POST /kiosk/enroll</code> and receive JSON.</p>
@endsection
