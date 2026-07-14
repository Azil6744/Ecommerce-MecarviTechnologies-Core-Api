<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    protected $table = 'sms_settings';

    protected $fillable = [
        'is_enabled',
        'provider',
        'twilio_sid',
        'twilio_auth_token',
        'twilio_from_number',
        'infobip_api_key',
        'infobip_base_url',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
