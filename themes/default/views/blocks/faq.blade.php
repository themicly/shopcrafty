<section class="st-reveal py-10 sm:py-16" style="background: var(--st-bg)">
    <div class="st-container">
        @if (! empty($b['items']))
            <div class="mx-auto max-w-2xl" x-data="{ open: null }">
                @foreach ($b['items'] as $index => $item)
                    <div class="border-b" style="border-color: var(--st-line)">
                        <button type="button"
                            class="flex w-full items-center justify-between gap-4 py-5 text-left text-base font-medium"
                            style="color: var(--st-ink)"
                            x-on:click="open = open === {{ $index }} ? null : {{ $index }}">
                            <span>{{ $item['question'] ?? '' }}</span>
                            <svg class="h-5 w-5 shrink-0 transition-transform duration-200"
                                x-bind:class="open === {{ $index }} ? 'rotate-180' : ''"
                                viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M5 7.5l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse class="pb-5 text-sm leading-relaxed"
                            style="color: var(--st-ink-soft)">
                            {{ $item['answer'] ?? '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
