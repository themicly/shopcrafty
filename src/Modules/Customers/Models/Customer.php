<?php

namespace Themicly\Shopcrafty\Modules\Customers\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $password
 * @property string $status
 * @property Carbon|null $created_at
 */
class Customer extends Authenticatable
{
    use Notifiable;

    protected $table = 'customers';

    protected $fillable = [
        'name', 'mobile', 'email', 'password',
        'mobile_verified_at', 'email_verified_at', 'status', 'last_order_at', 'tags',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'mobile_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'last_order_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /** Send the reset link through the store's own notification pipeline. */
    public function sendPasswordResetNotification($token): void
    {
        app(SendNotification::class)->handle('customer.password-reset', [
            'customer' => ['name' => $this->name, 'email' => $this->email, 'phone' => $this->mobile],
            'reset' => ['url' => route('storefront.password.reset', ['token' => $token, 'email' => $this->email])],
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Normalize a raw phone to E.164 using the store's default country code. */
    public static function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        $digits = preg_replace('/[^0-9]/', '', $mobile);

        if (str_starts_with($mobile, '+')) {
            return '+'.$digits;
        }

        $cc = (string) settings('localization.phone_country_code', '');

        if ($cc && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $cc ? $cc.$digits : $digits;
    }
}
