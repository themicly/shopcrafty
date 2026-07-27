<?php

namespace Themicly\Shopcrafty\Modules\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;

class SettingsUpdated
{
    use Dispatchable;

    public function __construct(public ?string $group = null) {}
}
