<?php

namespace Themicly\Shopcrafty\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $status
 */
#[Fillable(['name', 'email', 'password', 'role', 'status', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_STAFF = 'staff';

    /**
     * Granular staff permissions (key => human label). Owners implicitly hold all;
     * each key has a matching `manage-{key}` gate (see AuthServiceProvider).
     */
    public const PERMISSIONS = [
        'products' => 'Products & inventory',
        'orders' => 'Orders & fulfillment',
        'refunds' => 'Refunds & returns',
        'customers' => 'Customers',
        'marketing' => 'Marketing',
        'content' => 'Content & storefront',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /** Owners have every permission; staff have only what's been granted. */
    public function hasPermission(string $key): bool
    {
        return $this->isOwner() || in_array($key, (array) ($this->permissions ?? []), true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Whether this user may access the admin panel at all. */
    public function canAccessAdmin(): bool
    {
        return $this->isActive() && $this->hasRole(self::ROLE_OWNER, self::ROLE_STAFF);
    }
}
