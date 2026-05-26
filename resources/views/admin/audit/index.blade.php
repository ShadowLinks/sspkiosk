@extends('layouts.admin')

@section('title', 'Audit log')

@section('content')
    <h1>Audit log</h1>

    <div class="card">
        <form method="get" action="{{ route('admin.audit.index') }}">
            <label for="action">Action contains</label>
            <input type="text" name="action" id="action" value="{{ $action }}">
            <label for="actor_type">Actor type</label>
            <select name="actor_type" id="actor_type">
                <option value="">All</option>
                @foreach ($actorTypes as $type)
                    <option value="{{ $type->value }}" @selected($actorType === $type->value)>{{ $type->value }}</option>
                @endforeach
            </select>
            <label for="target_id">Target ID</label>
            <input type="text" name="target_id" id="target_id" value="{{ $targetId }}">
            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem">Search</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->actor_type->value }} {{ $log->actor_id }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->target_type }} {{ $log->target_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No audit entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $logs->links() }}
    </div>
@endsection
