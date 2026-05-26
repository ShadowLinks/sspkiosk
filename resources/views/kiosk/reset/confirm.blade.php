@extends('layouts.kiosk')

@section('title', 'Confirm identity')

@section('content')
    <h1>Confirm identity</h1>

    <p>Continue if this is you: <strong>{{ $displayName }}</strong></p>

    <div class="notice" role="note">{{ $notice }}</div>

    <a class="btn btn-primary" href="{{ route('kiosk.reset.photo') }}">Continue to photo</a>
    <a class="btn btn-secondary" href="{{ route('kiosk.reset.index') }}">Start over</a>
@endsection
