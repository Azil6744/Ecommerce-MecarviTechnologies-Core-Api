<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_heading',
        'bg_image',
        'small_text',
        'main_heading',
        'outlined_heading',
        'description',
        'background_text',
        'button_text',
        'button_url',
    ];

    /**
     * Get the full URL for the background image.
     *
     * @return string|null
     */
    public function getBgImageUrlAttribute()
    {
        if ($this->bg_image) {
            return asset('storage/' . $this->bg_image);
        }
        return null;
    }
}
