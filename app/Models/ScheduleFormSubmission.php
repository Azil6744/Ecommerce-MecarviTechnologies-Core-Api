<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleFormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'website',
        'service_needed',
        'preferred_date',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'preferred_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
