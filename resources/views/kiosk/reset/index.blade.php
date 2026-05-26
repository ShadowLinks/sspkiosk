@extends('layouts.kiosk')

@section('title', 'Password reset')

@section('content')
    <h1>Password reset help</h1>

    @if (session('error'))
        <div class="alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <p>Enter your school email or student ID to request password assistance.</p>

    <form method="post" action="{{ route('kiosk.reset.lookup') }}" id="reset-lookup-form">
        @csrf
        <label>
            Email or student ID
            <input type="text" name="identifier" value="{{ old('identifier') }}" required autocomplete="off" maxlength="255">
        </label>
        <button type="submit" class="btn btn-primary">Continue</button>
    </form>

    <p class="muted">If you are not registered for kiosk assistance, you will not be able to continue.</p>
@endsection
