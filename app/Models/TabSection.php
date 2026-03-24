<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabSection extends Model
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
        'background_color',
        'tab1_title',
        'tab1_icon',
        'tab1_content',
        'tab1_features',
        'tab1_image',
        'tab2_title',
        'tab2_icon',
        'tab2_content',
        'tab2_features',
        'tab2_image',
        'tab3_title',
        'tab3_icon',
        'tab3_content',
        'tab3_features',
        'tab3_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tab1_features' => 'array',
        'tab2_features' => 'array',
        'tab3_features' => 'array',
    ];

    /**
     * Get the full URL for tab 1 icon.
     *
     * @return string|null
     */
    public function getTab1IconUrlAttribute()
    {
        if ($this->tab1_icon) {
            return asset('storage/' . $this->tab1_icon);
        }
        return null;
    }

    /**
     * Get the full URL for tab 1 image.
     *
     * @return string|null
     */
    public function getTab1ImageUrlAttribute()
    {
        if ($this->tab1_image) {
            return asset('storage/' . $this->tab1_image);
        }
        return null;
    }

    /**
     * Get the full URL for tab 2 icon.
     *
     * @return string|null
     */
    public function getTab2IconUrlAttribute()
    {
        if ($this->tab2_icon) {
            return asset('storage/' . $this->tab2_icon);
        }
        return null;
    }

    /**
     * Get the full URL for tab 2 image.
     *
     * @return string|null
     */
    public function getTab2ImageUrlAttribute()
    {
        if ($this->tab2_image) {
            return asset('storage/' . $this->tab2_image);
        }
        return null;
    }

    /**
     * Get the full URL for tab 3 icon.
     *
     * @return string|null
     */
    public function getTab3IconUrlAttribute()
    {
        if ($this->tab3_icon) {
            return asset('storage/' . $this->tab3_icon);
        }
        return null;
    }

    /**
     * Get the full URL for tab 3 image.
     *
     * @return string|null
     */
    public function getTab3ImageUrlAttribute()
    {
        if ($this->tab3_image) {
            return asset('storage/' . $this->tab3_image);
        }
        return null;
    }
}
