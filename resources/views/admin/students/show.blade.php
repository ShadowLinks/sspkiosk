@extends('layouts.admin')

@section('title', $student->name)

@section('content')
    <h1>{{ $student->name }}</h1>

    <div class="card">
        <p><strong>Email:</strong> {{ $student->email }}</p>
        <p><strong>Google sub:</strong> {{ $student->google_sub }}</p>
        <p><strong>School / grade:</strong> {{ $student->school ?? '—' }} / {{ $student->grade ?? '—' }}</p>
        <p><strong>Org unit:</strong> {{ $student->org_unit_path ?? '—' }}</p>
        <p><strong>Registered:</strong> {{ $student->registered_at?->toDateTimeString() ?? 'Not registered' }}</p>
        <p><strong>Reset enabled:</strong> {{ $student->reset_enabled ? 'Yes' : 'No' }}</p>
        <p><strong>Failed attempts today:</strong> {{ $failedAttemptsToday }} @if ($isLockedOut)(locked out)@endif</p>
        <p><strong>Challenge questions:</strong> {{ $student->challengeQuestions->count() }}</p>

        <div class="actions">
            @if ($student->reset_enabled)
                <form method="post" action="{{ route('admin.students.disable-reset', $student) }}" class="inline" onsubmit="return confirm('Disable kiosk reset for this student?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Disable reset eligibility</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.students.enable-reset', $student) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Enable reset eligibility</button>
                </form>
            @endif
        </div>
    </div>

    @if ($registrationPhoto)
        <div class="card">
            <h2>Registration photo</h2>
            <img class="photo-thumb" src="{{ route('admin.photos.show', $registrationPhoto) }}" alt="Registration photo">
        </div>
    @endif

    <div class="card">
        <h2>Recent reset requests</h2>
        @if ($student->passwordResetRequests->isEmpty())
            <p class="muted">No reset requests.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kiosk</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Requested</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($student->passwordResetRequests as $request)
                        <tr>
                            <td><a href="{{ route('admin.requests.show', $request) }}">{{ $request->id }}</a></td>
                            <td>{{ $request->kiosk->name }}</td>
                            <td>{{ $request->status->value }}</td>
                            <td>{{ $request->challenge_score }}</td>
                            <td>{{ $request->requested_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
