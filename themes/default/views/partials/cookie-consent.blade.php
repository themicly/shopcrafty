{{--
    Shared cookie-consent banner. Theme-neutral (uses --st-* tokens only) so it
    renders consistently across every theme via `@include('theme::partials.cookie-consent')`.
    Only appears when the owner has enabled it in Settings → Privacy, and remembers
    a visitor's dismissal in localStorage so it doesn't reappear on the next page.
--}}
@if (settings('privacy.cookie_consent_enabled'))
    @php
        $cookieMessage = (string) settings('privacy.cookie_message', __('storefront.cookie_message'));
        $policy = (string) settings('privacy.privacy_policy_page', '');
        $policyUrl = $policy !== ''
            ? (str_starts_with($policy, 'http') ? $policy : route('storefront.page', $policy))
            : null;
    @endphp

    <div
        x-data="{
            show: false,
            init() {
                try { this.show = localStorage.getItem('cookie-consent') !== 'accepted'; }
                catch (e) { this.show = true; }
            },
            accept() {
                try { localStorage.setItem('cookie-consent', 'accepted'); } catch (e) {}
                this.show = false;
            },
        }"
        x-show="show"
        x-cloak
        role="region"
        aria-label="{{ __('storefront.cookie_notice') }}"
        {{-- bottom-28 clears the floating pill nav's raised cart circle (tallest theme
             breakpoint is lg, so this stays clear in every theme). --}}
        class="fixed inset-x-0 bottom-28 z-50 p-3 sm:p-4 lg:bottom-0"
    >
        <div
            class="mx-auto flex max-w-4xl flex-col gap-3 p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between"
            style="background: var(--st-surface, var(--st-bg)); color: var(--st-ink); border: 1px solid var(--st-line); border-radius: var(--st-radius-sm)"
        >
            <p class="text-sm" style="color: var(--st-ink-soft)">
                {{ $cookieMessage }}
                @if ($policyUrl)
                    <a href="{{ $policyUrl }}" class="underline" style="color: var(--st-ink)">{{ __('storefront.learn_more') }}</a>
                @endif
            </p>

            <div class="flex shrink-0 items-center gap-2">
                <button
                    type="button"
                    @click="accept()"
                    class="px-4 py-2 text-sm font-semibold"
                    style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)"
                >
                    {{ __('storefront.accept') }}
                </button>
            </div>
        </div>
    </div>
@endif
