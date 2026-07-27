<x-layouts.guest title="Sign in">
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-content">Sign in</h1>
        <p class="mt-1 text-sm text-content-muted">Welcome back. Enter your details to continue.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-success-soft px-3 py-2 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            :value="old('email')"
            :error="$errors->first('email')"
            required
            autofocus
            autocomplete="username"
        />

        <x-ui.input
            name="password"
            type="password"
            label="Password"
            :error="$errors->first('password')"
            required
            autocomplete="current-password"
        />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-content-secondary">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Forgot password?</a>
        </div>

        <x-ui.button type="submit" class="w-full">Sign in</x-ui.button>
    </form>
</x-layouts.guest>
