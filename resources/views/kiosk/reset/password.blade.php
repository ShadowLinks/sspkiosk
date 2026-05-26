@extends('layouts.kiosk')

@section('title', 'Choose your new password')

@section('content')
    <h1>Choose your new password</h1>
    <p class="muted">If technology staff approve your request, this will become your Google password.</p>

    <form method="post" action="{{ route('kiosk.reset.password.store') }}">
        @csrf
        <label for="password">New password (at least {{ $minLength }} characters)</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

        @if ($errors->any())
            <div class="alert-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="btn btn-primary">Submit request</button>
    </form>
@endsection
