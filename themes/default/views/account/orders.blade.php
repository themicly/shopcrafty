@extends('theme::layout')

@section('title', __('storefront.my_orders'))

@section('content')
    <div class="st-container py-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            @include('theme::partials.account-nav')

            <div class="flex-1">
                <h1 class="st-display mb-6 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.orders') }}</h1>

                {{-- Status filter --}}
                <div class="mb-6 flex flex-wrap gap-2">
                    @php
                        $chips = array_merge(['' => __('account.all')], array_combine($statuses, array_map('ucfirst', $statuses)));
                    @endphp
                    @foreach ($chips as $value => $label)
                        @php $active = $status === $value; @endphp
                        <a href="{{ route('storefront.account.index', $value === '' ? [] : ['status' => $value]) }}"
                            class="whitespace-nowrap px-3 py-1.5 text-xs font-semibold"
                            style="border-radius: var(--st-radius-sm); {{ $active ? 'background: var(--st-ink); color: var(--st-bg)' : 'background: var(--st-surface); color: var(--st-ink-soft)' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                @forelse ($orders as $order)
                    <div class="mb-3 border p-4" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <a href="{{ route('storefront.account.orders.show', $order->number) }}" class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--st-ink)">{{ $order->number }}</p>
                                <p class="text-xs" style="color: var(--st-ink-soft)">{{ $order->placed_at?->format('M j, Y') }} · <span class="capitalize">{{ $order->status }}</span></p>
                            </div>
                            <span class="text-sm font-semibold" style="color: var(--st-ink)">{{ format_money($order->grand_total) }}</span>
                        </a>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <a href="{{ route('storefront.account.orders.show', $order->number) }}" class="text-xs font-semibold" style="color: var(--st-accent)">{{ __('account.view_details') }}</a>
                            <livewire:orders.return-request :order-id="$order->id" :key="'ret-'.$order->id" />
                        </div>
                    </div>
                @empty
                    <div class="border p-10 text-center" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <p class="st-display text-lg" style="color: var(--st-ink)">{{ $status !== '' ? __('account.no_orders_with_status') : __('account.no_orders_yet') }}</p>
                        <a href="{{ url('/shop') }}" class="mt-3 inline-flex text-sm font-medium" style="color: var(--st-accent)">{{ __('account.start_shopping') }}</a>
                    </div>
                @endforelse

                @if ($orders->hasPages())
                    <div class="mt-6">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
