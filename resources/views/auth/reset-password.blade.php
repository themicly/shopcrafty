<x-layouts.guest title="Reset password">
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-content">Reset password</h1>
        <p class="mt-1 text-sm text-content-muted">Choose a new password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            :value="old('email', $email)"
            :error="$errors->first('email')"
            required
            autofocus
        />

        <x-ui.input
            name="password"
            type="password"
            label="New password"
            :error="$errors->first('password')"
            required
            autocomplete="new-password"
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            label="Confirm password"
            required
            autocomplete="new-password"
        />

        <x-ui.button type="submit" class="w-full">Reset password</x-ui.button>
    </form>
</x-layouts.guest>
