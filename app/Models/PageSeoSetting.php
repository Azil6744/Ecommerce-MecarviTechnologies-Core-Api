<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_slug',
        'tab_name',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];
}
