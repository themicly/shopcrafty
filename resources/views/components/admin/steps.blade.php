@props([
    'steps' => [],      // [['title' => '…', 'description' => '…'], …]
    'current' => 1,     // 1-based index of the active step
])

@php
    // A lightweight "journey" guide shown above a form so a non-technical admin
    // can see the whole path at a glance and where they are in it. Purely
    // informational — it does not gate the form.
    $steps = array_values($steps);
@endphp

<ol {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-lg border border-line bg-surface-raised p-4 sm:flex-row sm:items-start sm:gap-2']) }}>
    @foreach ($steps as $i => $step)
        @php
            $n = $i + 1;
            $isDone = $n < $current;
            $isCurrent = $n === (int) $current;
        @endphp
        <li class="flex flex-1 items-start gap-3">
            <span @class([
                'grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-semibold',
                'bg-primary text-white' => $isCurrent,
                'bg-success text-white' => $isDone,
                'bg-surface-sunken text-content-muted' => ! $isCurrent && ! $isDone,
            ])>
                @if ($isDone)
                    <x-ui.icon name="check" class="h-4 w-4" />
                @else
                    {{ $n }}
                @endif
            </span>
            <div class="min-w-0 pt-0.5">
                <p @class([
                    'text-sm font-medium',
                    'text-content' => $isCurrent || $isDone,
                    'text-content-muted' => ! $isCurrent && ! $isDone,
                ])>{{ $step['title'] ?? ('Step '.$n) }}</p>
                @if (! empty($step['description']))
                    <p class="mt-0.5 text-xs text-content-muted">{{ $step['description'] }}</p>
                @endif
            </div>
            @if (! $loop->last)
                <div class="mt-3.5 hidden h-px flex-1 bg-line sm:block"></div>
            @endif
        </li>
    @endforeach
</ol>
