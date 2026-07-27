<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Support\Facades\DB;
use Themicly\Shopcrafty\Core\Support\DemoMode;

/**
 * Records what shoppers search on the storefront — both explicit submits
 * (the /search results page) and live/instant header search, but only once
 * the shopper has paused typing (the header's own debounce is the "stopped
 * typing" signal; nothing calls record() per keystroke).
 *
 * Terms are normalized (trim, collapse whitespace, lowercase, cap at 120
 * chars) and deduped per session: a term counts once per visitor session, so
 * pagination, filter reloads, and repeated debounce fires for the same
 * settled term don't inflate the counter. Analytics must never break search —
 * callers wrap record() in rescue().
 */
class SearchTermRecorder
{
    /** Max stored term length (must match the catalog_search_terms column). */
    public const MAX_LENGTH = 120;

    /** Session key holding the terms already counted this session. */
    protected const SESSION_KEY = 'catalog.recorded_search_terms';

    /** Keep the per-session dedupe list from growing unbounded. */
    protected const SESSION_LIMIT = 100;

    public function record(?string $raw): void
    {
        // This writes via DB::table(), bypassing the Eloquent-level demo-mode
        // guard (see Themicly\Shopcrafty\Core\Support\DemoMode) — and search requests are
        // GETs, which that guard intentionally never blocks. Check explicitly
        // so demo visitors don't pollute real search-term analytics.
        if (DemoMode::enabled()) {
            return;
        }

        $term = $this->normalize($raw);

        if ($term === '') {
            return;
        }

        // Once per session per term — repeat page loads don't double-count.
        $seen = (array) session()->get(self::SESSION_KEY, []);

        if (in_array($term, $seen, true)) {
            return;
        }

        $seen[] = $term;
        session()->put(self::SESSION_KEY, array_slice($seen, -self::SESSION_LIMIT));

        $this->increment($term);
    }

    /** Trim, collapse inner whitespace, lowercase, and cap the length. */
    public function normalize(?string $raw): string
    {
        $term = trim((string) $raw);
        $term = (string) preg_replace('/\s+/u', ' ', $term);
        $term = mb_strtolower($term);

        return mb_substr($term, 0, self::MAX_LENGTH);
    }

    /**
     * Insert-or-ignore then atomically increment — no unique-violation race on
     * the first concurrent search for a term (same pattern as product views).
     */
    protected function increment(string $term): void
    {
        $now = now();

        DB::table('catalog_search_terms')->insertOrIgnore([
            'term' => $term,
            'count' => 0,
            'last_searched_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('catalog_search_terms')
            ->where('term', $term)
            ->increment('count', 1, [
                'last_searched_at' => $now,
                'updated_at' => $now,
            ]);
    }
}
