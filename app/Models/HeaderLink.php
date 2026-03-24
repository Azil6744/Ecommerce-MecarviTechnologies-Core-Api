<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'url',
        'show_in_header',
        'sort_order',
    ];

    protected $casts = [
        'show_in_header' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeVisible($query)
    {
        return $query->where('show_in_header', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
}
