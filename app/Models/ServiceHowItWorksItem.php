<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHowItWorksItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'short_description',
        'full_description',
        'image_url',
        'order',
    ];

    public function section()
    {
        return $this->belongsTo(ServiceHowItWorksSection::class, 'section_id');
    }

    /**
     * Resolve stored image path to a full URL when needed.
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    }
}
