@extends('layouts.kiosk')

@section('title', 'Password not available')

@section('content')
    <h1>Password not available</h1>
    <p class="muted">This password was already shown on this kiosk or is no longer available. If you need help, please see technology staff.</p>
    <a class="btn btn-primary" href="{{ route('kiosk.reset.index') }}">Return to start</a>
@endsection
