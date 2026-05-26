@extends('layouts.kiosk')

@section('title', 'Kiosk required')

@section('content')
    <h1>District kiosk required</h1>

    <p>Registration must be completed on a district-managed kiosk.</p>

    <p class="muted">Please visit your school kiosk to continue, or contact technology staff for help.</p>

    <a class="btn btn-secondary" href="{{ route('register.index') }}">Back</a>
@endsection
