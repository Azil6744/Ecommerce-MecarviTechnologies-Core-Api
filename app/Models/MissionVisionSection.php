<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionVisionSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mission_title',
        'vision_title',
        'mission_description',
        'vision_description',
        'mission_background_color',
        'vision_background_color',
    ];
}
