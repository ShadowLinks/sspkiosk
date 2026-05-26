<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login — {{ config('app.name') }}</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            width: 100%;
            max-width: 24rem;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        label { display: block; margin: 0.75rem 0 0.25rem; font-weight: 600; }
        input { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        button {
            margin-top: 1rem;
            width: 100%;
            padding: 0.65rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .error { color: #b91c1c; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin login</h1>
        <form method="post" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')<p class="error">{{ $message }}</p>@enderror

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label><input type="checkbox" name="remember" value="1"> Remember me</label>

            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
