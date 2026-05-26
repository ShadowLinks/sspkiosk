@extends('layouts.admin')

@section('title', 'Request #'.$resetRequest->id)

@section('content')
    <h1>Reset request #{{ $resetRequest->id }}</h1>

    <div class="card">
        <p><strong>Status:</strong> <span class="badge badge-{{ $resetRequest->status->value }}">{{ str_replace('_', ' ', $resetRequest->status->value) }}</span></p>
        <p><strong>Student:</strong> <a href="{{ route('admin.students.show', $resetRequest->student) }}">{{ $resetRequest->student->name }}</a> ({{ $resetRequest->student->email }})</p>
        <p><strong>Kiosk:</strong> <a href="{{ route('admin.kiosks.show', $resetRequest->kiosk) }}">{{ $resetRequest->kiosk->name }}</a></p>
        <p><strong>Challenge score:</strong> {{ $resetRequest->challenge_score }} / {{ count($resetRequest->challenge_questions_presented ?? []) }}</p>
        <p><strong>Requested:</strong> {{ $resetRequest->requested_at?->toDateTimeString() }}</p>
        <p><strong>Expires:</strong> {{ $resetRequest->expires_at?->toDateTimeString() }}</p>
        <p><strong>Reset mode:</strong> {{ $resetRequest->reset_mode ?? '—' }}</p>
        <p><strong>Pending password type:</strong> {{ $resetRequest->pending_password_type ?? '—' }}</p>
        <p><strong>Encrypted pending password on file:</strong> {{ $resetRequest->hasEncryptedPendingPassword() ? 'Yes' : 'No' }}</p>
        <p><strong>Pending password created:</strong> {{ $resetRequest->pending_password_created_at?->toDateTimeString() ?? '—' }}</p>
        <p><strong>Pending password displayed:</strong> {{ $resetRequest->pending_password_displayed_at?->toDateTimeString() ?? '—' }}</p>
        <p><strong>Pending password expires:</strong> {{ $resetRequest->pending_password_expires_at?->toDateTimeString() ?? '—' }}</p>
        <p><strong>Pending password deleted:</strong> {{ $resetRequest->pending_password_deleted_at?->toDateTimeString() ?? '—' }}</p>
        @if ($resetRequest->approved_at)
            <p><strong>Approved:</strong> {{ $resetRequest->approved_at->toDateTimeString() }} (Slack {{ $resetRequest->approved_by_slack_user_id }})</p>
        @endif
        @if ($resetRequest->denied_at)
            <p><strong>Denied:</strong> {{ $resetRequest->denied_at->toDateTimeString() }}</p>
        @endif
        @if ($resetRequest->google_reset_attempted_at)
            <p><strong>Google reset:</strong> {{ $resetRequest->google_reset_success ? 'Success' : 'Failed' }}
                @if ($resetRequest->google_error_message)
                    — {{ $resetRequest->google_error_message }}
                @endif
            </p>
        @endif
    </div>

    @if ($resetRequest->resetPhoto)
        <div class="card">
            <h2>Reset request photo</h2>
            <img class="photo-thumb" src="{{ route('admin.photos.show', $resetRequest->resetPhoto) }}" alt="Reset request photo">
        </div>
    @endif
@endsection
