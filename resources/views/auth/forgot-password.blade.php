<x-layouts.guest title="Forgot password">
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-content">Forgot password</h1>
        <p class="mt-1 text-sm text-content-muted">We'll email you a link to reset it.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-success-soft px-3 py-2 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            :value="old('email')"
            :error="$errors->first('email')"
            required
            autofocus
        />

        <x-ui.button type="submit" class="w-full">Email reset link</x-ui.button>

        <p class="text-center text-sm text-content-muted">
            <a href="{{ route('login') }}" class="text-primary hover:underline">Back to sign in</a>
        </p>
    </form>
</x-layouts.guest>
