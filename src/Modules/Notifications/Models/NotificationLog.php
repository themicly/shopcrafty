<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notification_logs';

    protected $fillable = [
        'event_key', 'channel', 'gateway', 'recipient',
        'recipient_type', 'status', 'error', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
