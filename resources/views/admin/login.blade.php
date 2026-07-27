<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login · {{ config('shopcrafty.store_name', 'Shopcrafty') }}</title>
</head>
<body style="margin:0;min-height:100vh;display:grid;place-items:center;background:#f8fafc;color:#172033;font-family:system-ui,sans-serif">
    <main style="width:min(420px,calc(100% - 40px));padding:32px;background:white;border:1px solid #e2e8f0;border-radius:18px">
        <p style="color:#6d28d9;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:12px">Shopcrafty Admin</p>
        <h1>Sign in to your store</h1>
        <p style="color:#64748b">Use your store administrator account to continue.</p>

        @if ($errors->any())
            <div style="margin:16px 0;padding:12px;border-radius:8px;background:#fef2f2;color:#b91c1c;font-size:14px">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" style="display:grid;gap:16px">
            @csrf
            <label style="display:grid;gap:6px;font-size:14px;font-weight:600">
                Email
                <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" style="padding:11px;border:1px solid #cbd5e1;border-radius:8px;font:inherit">
            </label>
            <label style="display:grid;gap:6px;font-size:14px;font-weight:600">
                Password
                <input name="password" type="password" required autocomplete="current-password" style="padding:11px;border:1px solid #cbd5e1;border-radius:8px;font:inherit">
            </label>
            <label style="display:flex;gap:8px;align-items:center;font-size:14px;color:#64748b">
                <input name="remember" type="checkbox" value="1"> Remember me
            </label>
            <button type="submit" style="padding:12px;border:0;border-radius:8px;background:#6d28d9;color:white;font-weight:700;font-size:15px;cursor:pointer">Sign in</button>
        </form>
    </main>
</body>
</html>
