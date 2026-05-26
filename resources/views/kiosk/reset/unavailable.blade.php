@extends('layouts.kiosk')

@section('title', 'Kiosk unavailable')

@section('content')
    <h1>Kiosk unavailable</h1>

    @if (session('error'))
        <div class="alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <p>This kiosk cannot accept reset requests right now. Please contact your school technology staff.</p>
@endsection
