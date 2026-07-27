@extends('theme::layout')

@section('title', __('auth.choose_new_password'))

@section('content')
    @php
        $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
        $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    @endphp
    <div class="st-container py-16">
        <div class="mx-auto max-w-sm">
            <h1 class="st-display mb-1 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('auth.choose_new_password') }}</h1>
            <p class="mb-6 text-sm" style="color: var(--st-ink-soft)">{{ __('auth.reset_subtitle') }}</p>

            <form method="POST" action="{{ route('storefront.password.update') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <input name="email" type="email" value="{{ old('email', $email) }}" placeholder="{{ __('auth.email_address') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    @error('email')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input name="password" type="password" placeholder="{{ __('storefront.new_password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}" autofocus>
                    @error('password')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <input name="password_confirmation" type="password" placeholder="{{ __('auth.confirm_new_password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                <button type="submit" class="w-full py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.reset_password') }}</button>
            </form>
        </div>
    </div>
@endsection
