<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'name',
        'image_path',
        'description',
        'price',
        'pricing_mode',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function attribute()
    {
        return $this->belongsTo(GlobalAttribute::class, 'attribute_id');
    }
}
