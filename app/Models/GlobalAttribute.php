<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'pricing_mode',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function values()
    {
        return $this->hasMany(GlobalAttributeValue::class, 'attribute_id')->orderBy('sort_order');
    }
}
