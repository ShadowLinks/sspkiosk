@extends('layouts.kiosk')

@section('title', 'Write down your password')

@section('content')
    <h1>Write down this password</h1>

    <div class="notice" role="note">{{ $notice }}</div>

    @if ($copyNoticeEnabled)
        <p class="muted">Write it on paper now. You will not be able to see it again on this screen.</p>
    @endif

    <p class="temp-password" id="temp-password" aria-live="polite">{{ $temporaryPassword }}</p>

    <p class="muted">This screen will continue in <span id="countdown">{{ $displaySeconds }}</span> seconds.</p>

    <script>
        (function () {
            let remaining = {{ $displaySeconds }};
            const countdown = document.getElementById('countdown');
            const passwordEl = document.getElementById('temp-password');

            const timer = setInterval(function () {
                remaining -= 1;
                countdown.textContent = String(remaining);

                if (remaining <= 0) {
                    clearInterval(timer);
                    passwordEl.textContent = '********';
                    window.location.href = @json($submittedUrl);
                }
            }, 1000);
        })();
    </script>

    <style>
        .temp-password {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-align: center;
            padding: 1.5rem;
            background: #f3f4f6;
            border-radius: 8px;
            word-break: break-word;
        }
    </style>
@endsection
