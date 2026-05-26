@extends('layouts.kiosk')

@section('title', 'Request submitted')

@section('content')
    <h1>Request submitted</h1>
    <div class="notice" role="status">{{ $message }}</div>
    <p class="muted">You may leave the kiosk. Technology staff will review your request.</p>
    <a class="btn btn-primary" href="{{ route('kiosk.reset.index') }}">Done</a>
@endsection
