@extends('theme::layout')

@section('title', __('storefront.profile'))

@section('content')
    <div class="st-container py-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            @include('theme::partials.account-nav')
            <div class="flex-1 lg:max-w-lg">
                <h1 class="st-display mb-6 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.profile') }}</h1>
                <livewire:customers.profile-form />

                @if (settings('privacy.gdpr_tools_enabled'))
                    <div class="mt-10 border-t pt-8" style="border-color: var(--st-line)">
                        <h2 class="st-display mb-1 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.your_data') }}</h2>
                        <p class="mb-4 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.your_data_intro') }}</p>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a
                                href="{{ route('storefront.account.data-export') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold"
                                style="border: 1px solid var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)"
                            >
                                {{ __('storefront.download_my_data') }}
                            </a>

                            <form
                                method="POST"
                                action="{{ route('storefront.account.delete') }}"
                                onsubmit="return confirm(@js(__('storefront.delete_account_confirm')))"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center px-4 py-2.5 text-sm font-semibold sm:w-auto"
                                    style="border: 1px solid var(--st-accent); color: var(--st-accent); border-radius: var(--st-radius-sm)"
                                >
                                    {{ __('storefront.delete_my_account') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
