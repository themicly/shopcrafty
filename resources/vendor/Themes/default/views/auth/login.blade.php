@extends('theme::layout')

@section('title', __('storefront.sign_in'))

@section('content')
    @php
        $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
        $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    @endphp
    <div class="st-container py-16">
        <div class="mx-auto max-w-sm">
            <h1 class="st-display mb-1 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('auth.welcome_back') }}</h1>
            <p class="mb-6 text-sm" style="color: var(--st-ink-soft)">{{ __('auth.sign_in_subtitle') }}</p>

            <form method="POST" action="{{ route('storefront.login') }}" class="space-y-3">
                @csrf
                <div>
                    <input name="identifier" value="{{ old('identifier') }}" aria-label="{{ __('auth.phone_or_email') }}" placeholder="{{ __('auth.phone_or_email') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}" autofocus>
                    @error('identifier')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <input name="password" type="password" aria-label="{{ __('storefront.password') }}" placeholder="{{ __('storefront.password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2" style="color: var(--st-ink-soft)">
                        <input type="checkbox" name="remember"> {{ __('storefront.remember_me') }}
                    </label>
                    <a href="{{ route('storefront.password.request') }}" class="font-medium" style="color: var(--st-accent)">{{ __('auth.forgot_password_short') }}</a>
                </div>
                <button type="submit" class="w-full py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.sign_in') }}</button>
            </form>

            <p class="mt-5 text-center text-sm" style="color: var(--st-ink-soft)">
                {{ __('auth.new_here') }} <a href="{{ route('storefront.register') }}" class="font-medium" style="color: var(--st-accent)">{{ __('auth.create_an_account') }}</a>
            </p>
        </div>
    </div>
@endsection
