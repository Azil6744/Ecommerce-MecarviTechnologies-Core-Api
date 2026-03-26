<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormPageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_slug',
        'contact_email',
        'form_title',
        'form_description',
        'submit_button_text',
        'section_order',
    ];

    protected $casts = [
        'section_order' => 'array',
    ];
}
