<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subtitle',
        'main_title',
        'button_text',
        'background_image',
        'background_color',
        'card_background_color',
        'process_subtitle',
        'process_title_1',
        'process_title_2',
        'process_description',
        'process_checklist',
        'process_background_color',
        'process_card_background_color',
        'process_background_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'process_checklist' => 'array',
    ];

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
     * Get the full URL for the process background image.
     *
     * @return string|null
     */
    public function getProcessBackgroundImageUrlAttribute()
    {
        if ($this->process_background_image) {
            return asset('storage/' . $this->process_background_image);
        }
        return null;
    }
}
