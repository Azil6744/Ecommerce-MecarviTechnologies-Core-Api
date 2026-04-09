<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an editor.
     *
     * @return bool
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Check if user is a viewer.
     *
     * @return bool
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Check if user has admin access (super_admin or editor).
     *
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'editor']);
    }

    /**
     * Get available roles.
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        return ['super_admin', 'editor', 'viewer'];
    }

    // Ecommerce Relationships
    public function orders()
    {
        return $this->hasMany(EcommerceOrder::class);
    }

    public function addresses()
    {
        return $this->hasMany(EcommerceAddress::class);
    }

    public function reviews()
    {
        return $this->hasMany(EcommerceReview::class);
    }

    public function cart()
    {
        return $this->hasOne(EcommerceCart::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(EcommerceWalletTransaction::class);
    }

    public function quotations()
    {
        return $this->hasMany(EcommerceQuotation::class);
    }

    public function tickets()
    {
        return $this->hasMany(EcommerceTicket::class);
    }

    public function disputes()
    {
        return $this->hasMany(EcommerceDispute::class);
    }

    public function returns()
    {
        return $this->hasMany(EcommerceReturn::class);
    }
}
