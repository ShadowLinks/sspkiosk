@extends('layouts.kiosk')

@section('title', 'Reset photo')

@section('content')
    <h1>Take your photo</h1>

    <div class="notice" role="note">{{ $notice }}</div>

    <div class="camera-wrap">
        <video id="camera" autoplay playsinline muted aria-label="Camera preview"></video>
        <canvas id="snapshot" hidden></canvas>
    </div>

    <form method="post" action="{{ route('kiosk.reset.photo.store') }}" enctype="multipart/form-data" id="photo-form">
        @csrf
        <input type="file" name="photo" id="photo-input" accept="image/jpeg,image/png,image/webp" hidden required>
        <button type="button" class="btn btn-secondary" id="capture-btn">Take photo</button>
        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Continue</button>
    </form>

    <script>
        (function () {
            const video = document.getElementById('camera');
            const canvas = document.getElementById('snapshot');
            const photoInput = document.getElementById('photo-input');
            const captureBtn = document.getElementById('capture-btn');
            const submitBtn = document.getElementById('submit-btn');

            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                .then(stream => { video.srcObject = stream; })
                .catch(() => {
                    captureBtn.disabled = true;
                    alert('Camera access is required.');
                });

            captureBtn.addEventListener('click', function () {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                canvas.toBlob(function (blob) {
                    if (!blob) return;
                    const file = new File([blob], 'reset.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    photoInput.files = dt.files;
                    submitBtn.disabled = false;
                }, 'image/jpeg', 0.92);
            });
        })();
    </script>
@endsection
