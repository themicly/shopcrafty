<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $subject
 * @property string $body
 * @property array<int, string>|null $audience_tags
 * @property string $status
 * @property int $recipients_count
 * @property Carbon|null $sent_at
 */
class NewsletterCampaign extends Model
{
    protected $table = 'newsletter_campaigns';

    protected $fillable = ['subject', 'body', 'audience_tags', 'status', 'recipients_count', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'recipients_count' => 'integer', 'audience_tags' => 'array'];
    }
}
