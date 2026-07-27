<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Services;

use Illuminate\Support\Arr;

class TemplateRenderer
{
    /**
     * Replace {{ dotted.variables }} in a template against a context array.
     * Unknown variables render empty (templates are validated against the
     * event's variable catalog in the admin editor).
     *
     * When $escape is true (HTML email bodies), each substituted value is
     * HTML-escaped so customer-controlled fields (name, address) can't inject
     * markup into the email (NOT-06). The template itself is trusted admin HTML.
     */
    public function render(string $template, array $context, bool $escape = false): string
    {
        return preg_replace_callback(
            '/\{\{\s*([\w.]+)\s*\}\}/',
            function (array $m) use ($context, $escape) {
                $value = (string) (Arr::get($context, $m[1]) ?? '');

                return $escape ? e($value) : $value;
            },
            $template,
        );
    }
}
