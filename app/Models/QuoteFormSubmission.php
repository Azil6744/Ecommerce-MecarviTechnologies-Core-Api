<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteFormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_slug',
        'first_name',
        'last_name',
        'phone',
        'email',
        'company_name',
        'country',
        'project_type',
        'estimate_budget',
        'maximum_time_for_project',
        'required_skills',
        'corporate_intake_payload',
        'uploaded_files',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
