<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    use HasFactory;

    const TYPE_COMPANY = 'company';
    const TYPE_SUPPORT = 'support';
    const TYPE_POLICY_CENTER = 'policy_center';
    const TYPE_OUR_BRANDS = 'our_brands';

    protected $fillable = [
        'type',
        'label',
        'path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Scope by link type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
}
