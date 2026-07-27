@extends('theme::layout')

@section('title', __('storefront.register'))

@section('content')
    @php
        $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
        $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    @endphp
    <div class="st-container py-16">
        <div class="mx-auto max-w-sm">
            <h1 class="st-display mb-1 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.register') }}</h1>
            <p class="mb-6 text-sm" style="color: var(--st-ink-soft)">{{ __('auth.register_subtitle') }}</p>

            <form method="POST" action="{{ route('storefront.register') }}" class="space-y-3">
                @csrf
                <div>
                    <input name="name" value="{{ old('name') }}" aria-label="{{ __('storefront.full_name') }}" placeholder="{{ __('storefront.full_name') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}" autofocus>
                    @error('name')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input name="identifier" value="{{ old('identifier') }}" aria-label="{{ __('auth.phone_or_email') }}" placeholder="{{ __('auth.phone_or_email') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    @error('identifier')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input name="password" type="password" aria-label="{{ __('storefront.password') }}" placeholder="{{ __('storefront.password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    @error('password')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <input name="password_confirmation" type="password" aria-label="{{ __('storefront.confirm_password') }}" placeholder="{{ __('storefront.confirm_password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                <button type="submit" class="w-full py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.register') }}</button>
            </form>

            <p class="mt-5 text-center text-sm" style="color: var(--st-ink-soft)">
                {{ __('storefront.have_account') }} <a href="{{ route('storefront.login') }}" class="font-medium" style="color: var(--st-accent)">{{ __('storefront.sign_in') }}</a>
            </p>
        </div>
    </div>
@endsection
