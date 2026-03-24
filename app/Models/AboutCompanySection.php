<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutCompanySection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_title',
        'company_description',
        'company_image',
        'left_background_color',
        'right_background_color',
    ];

    /**
     * Get the full URL for the company image.
     *
     * @return string|null
     */
    public function getCompanyImageUrlAttribute()
    {
        if ($this->company_image) {
            return asset('storage/' . $this->company_image);
        }
        return null;
    }
}
