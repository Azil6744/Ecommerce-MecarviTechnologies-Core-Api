<?php

namespace App\Models\ServicePage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowcaseSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_title',
        'section_description',
        'section_image',
        'background_image',
        'background_image_mobile',
    ];

    /**
     * Get the showcase items associated with this showcase section.
     *
     * @return HasMany
     */
    public function showcaseItems(): HasMany
    {
        return $this->hasMany(ShowcaseItem::class)->orderBy('order');
    }

    /**
     * Get the full URL for the section image.
     *
     * @return string|null
     */
    public function getSectionImageUrlAttribute()
    {
        if ($this->section_image) {
            return asset('storage/' . $this->section_image);
        }
        return null;
    }

    /**
     * Get the full URL for the background image.
     *
     * @return string|null
     */
    public function getBackgroundImageUrlAttribute()
    {
        if ($this->background_image) {
            return asset('storage/' . $this->background_image);
        }
        return null;
    }

    /**
     * Get the full URL for the mobile background image.
     *
     * @return string|null
     */
    public function getBackgroundImageMobileUrlAttribute()
    {
        if ($this->background_image_mobile) {
            return asset('storage/' . $this->background_image_mobile);
        }
        return null;
    }
}
