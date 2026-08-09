<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicySection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'hero_title',
        'hero_subtitle',
        'hero_background_image',
        'sections',
        'styling',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sections' => 'array',
        'styling' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * Get the full URL for the hero background image.
     *
     * @return string|null
     */
    public function getHeroBackgroundImageUrlAttribute()
    {
        if ($this->hero_background_image) {
            return asset('storage/' . $this->hero_background_image);
        }
        return null;
    }
}
