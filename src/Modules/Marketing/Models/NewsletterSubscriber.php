<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $email
 * @property string|null $name
 * @property string $status
 * @property string $token
 * @property string|null $source
 */
class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    protected $fillable = ['email', 'name', 'status', 'token', 'source'];

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber) {
            $subscriber->token ??= Str::random(40);
        });
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('status', 'subscribed');
    }
}
