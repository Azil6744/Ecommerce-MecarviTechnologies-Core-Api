<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tagline',
        'description',
        'contact_person',
        'address',
        'phone',
        'email',
        'web',
        'fax',
        'category',
        'status',
        'assistance_tags',
        'logo_svg_type',
    ];

    protected $casts = [
        'assistance_tags' => 'array',
    ];
}
