@extends('theme::layout')

@section('title', __('storefront.reset_password'))

@section('content')
    @php
        $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
        $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    @endphp
    <div class="st-container py-16">
        <div class="mx-auto max-w-sm">
            <h1 class="st-display mb-1 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.forgot_password') }}</h1>
            <p class="mb-6 text-sm" style="color: var(--st-ink-soft)">{{ __('auth.forgot_subtitle') }}</p>

            @if (session('status'))
                <p class="mb-4 rounded px-4 py-3 text-sm" style="background: var(--st-primary-soft, #eef); color: var(--st-ink)">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('storefront.password.email') }}" class="space-y-3">
                @csrf
                <div>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('auth.email_address') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}" autofocus>
                    @error('email')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.send_reset_link') }}</button>
            </form>

            <p class="mt-5 text-center text-sm" style="color: var(--st-ink-soft)">
                <a href="{{ route('storefront.login') }}" class="font-medium" style="color: var(--st-accent)">{{ __('auth.back_to_sign_in') }}</a>
            </p>
        </div>
    </div>
@endsection
