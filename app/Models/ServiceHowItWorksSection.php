<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceHowItWorksItem;

class ServiceHowItWorksSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_description',
        'full_description',
        'background_image_url',
    ];

    public function items()
    {
        return $this->hasMany(ServiceHowItWorksItem::class, 'section_id');
    }
}
