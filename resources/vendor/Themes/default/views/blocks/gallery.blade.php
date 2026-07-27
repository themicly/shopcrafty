<section class="st-reveal py-10 sm:py-16" style="background: var(--st-bg)">
    <div class="st-container">
        @if (! empty($b['images']))
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                @foreach ($b['images'] as $image)
                    <img src="{{ $image }}" alt=""
                        class="w-full object-cover" style="border-radius: var(--st-radius)" />
                @endforeach
            </div>
        @endif
    </div>
</section>
