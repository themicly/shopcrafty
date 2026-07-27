<?php

namespace Themicly\Shopcrafty\Modules\Settings\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Generates bundled, self-contained demo imagery — no hot-linked picsum, works
 * offline and on any domain. Each "concept" (fashion / electronics / grocery /
 * furniture) has its own palette, so a pack's storefront looks intentional and
 * premium the moment it's imported. Art is deterministic per slug (stable across
 * re-imports) and written as lightweight SVG to the public disk.
 *
 * Swapping in real photography later is a drop-in: replace the media `path`.
 */
class DemoImageFactory
{
    /** @var array<string, array{bg1:string, bg2:string, ink:string, accent:string, serif:string}> */
    private const PALETTES = [
        'fashion' => ['bg1' => '#efe7dd', 'bg2' => '#d8c3ad', 'ink' => '#2b2320', 'accent' => '#a9744f', 'serif' => 'Georgia, "Times New Roman", serif'],
        'electronics' => ['bg1' => '#0f172a', 'bg2' => '#1e293b', 'ink' => '#e2e8f0', 'accent' => '#38bdf8', 'serif' => '"Helvetica Neue", Arial, sans-serif'],
        'grocery' => ['bg1' => '#e9f7ec', 'bg2' => '#bfe5c4', 'ink' => '#14532d', 'accent' => '#22c55e', 'serif' => 'Georgia, serif'],
        'furniture' => ['bg1' => '#f3ede4', 'bg2' => '#d9c9b3', 'ink' => '#3a332b', 'accent' => '#9a6b45', 'serif' => 'Georgia, serif'],
        'generic' => ['bg1' => '#eef2f7', 'bg2' => '#cfd8e3', 'ink' => '#1f2937', 'accent' => '#6366f1', 'serif' => 'Georgia, serif'],
    ];

    /**
     * Concept art for a product (portrait). Returns a public URL.
     */
    public function product(string $pack, string $slug, string $label, int $variant = 0): string
    {
        return $this->make($pack, $slug, $label, $variant, 800, 1000);
    }

    /** Concept art for a category/banner tile (landscape or square). */
    public function tile(string $pack, string $slug, string $label, int $w = 800, int $h = 800): string
    {
        return $this->make($pack, $slug, $label, 0, $w, $h);
    }

    private function make(string $pack, string $slug, string $label, int $variant, int $w, int $h): string
    {
        $path = "demo/{$pack}/{$slug}".($variant ? "-{$variant}" : '').'.svg';

        // Deterministic: same slug+variant → same art, so re-imports don't churn files.
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->svg($pack, $slug, $label, $variant, $w, $h));
        }

        return Storage::disk('public')->url($path);
    }

    private function svg(string $pack, string $slug, string $label, int $variant, int $w, int $h): string
    {
        $p = self::PALETTES[$pack] ?? self::PALETTES['generic'];
        $seed = crc32($slug.$variant);

        // Blob positions/sizes derived from the seed for gentle variety.
        $bx = 120 + ($seed % 400);
        $by = 200 + (($seed >> 3) % 400);
        $br = 180 + (($seed >> 6) % 160);
        $cx = ($seed >> 9) % $w;
        $cy = 500 + (($seed >> 12) % 300);
        $cr = 120 + (($seed >> 15) % 140);

        $mono = $this->monogram($label);
        $text = htmlspecialchars(mb_strtoupper($label), ENT_QUOTES);
        // The palette's CSS-style font stack (e.g. Georgia, "Times New Roman", serif)
        // carries its own double quotes, which broke the SVG's XML when dropped
        // straight into a double-quoted attribute — escape it like any other value.
        $serif = htmlspecialchars($p['serif'], ENT_QUOTES);
        $monoSize = (int) ($w * 0.28);
        $labelY = $h - (int) ($h * 0.09);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="{$p['bg1']}"/>
              <stop offset="1" stop-color="{$p['bg2']}"/>
            </linearGradient>
          </defs>
          <rect width="{$w}" height="{$h}" fill="url(#bg)"/>
          <circle cx="{$bx}" cy="{$by}" r="{$br}" fill="{$p['accent']}" opacity="0.18"/>
          <circle cx="{$cx}" cy="{$cy}" r="{$cr}" fill="{$p['ink']}" opacity="0.07"/>
          <text x="50%" y="47%" text-anchor="middle" dominant-baseline="middle"
                font-family="{$serif}" font-size="{$monoSize}" fill="{$p['ink']}" opacity="0.9">{$mono}</text>
          <text x="50%" y="{$labelY}" text-anchor="middle"
                font-family="'Helvetica Neue', Arial, sans-serif" font-size="30" letter-spacing="3"
                fill="{$p['ink']}" opacity="0.85">{$text}</text>
        </svg>
        SVG;
    }

    private function monogram(string $label): string
    {
        $words = preg_split('/\s+/', trim($label)) ?: [];
        $initials = '';

        // A single-word label (most category names: "Produce", "Pantry") only ever
        // contributes one initial, so sibling categories starting with the same
        // letter render identically — take the word's first two letters instead.
        if (count($words) === 1) {
            $initials = mb_strtoupper(mb_substr($words[0], 0, 2));

            return $initials !== '' ? htmlspecialchars($initials, ENT_QUOTES) : '·';
        }

        foreach (array_slice($words, 0, 2) as $word) {
            $first = mb_substr($word, 0, 1);
            if ($first !== '' && ctype_alnum($first)) {
                $initials .= mb_strtoupper($first);
            }
        }

        return $initials !== '' ? htmlspecialchars($initials, ENT_QUOTES) : '·';
    }
}
