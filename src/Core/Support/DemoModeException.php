<?php

namespace Themicly\Shopcrafty\Core\Support;

use RuntimeException;

/**
 * Thrown when a write is blocked by demo mode. Caught in bootstrap/app.php's
 * exception handling and turned into a toast instead of an error page.
 */
class DemoModeException extends RuntimeException
{
    public function __construct(string $message = 'This action is disabled in demo mode.')
    {
        parent::__construct($message);
    }
}
