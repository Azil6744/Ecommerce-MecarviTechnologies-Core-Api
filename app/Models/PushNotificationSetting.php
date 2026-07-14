<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'push_notification_settings';

    protected $fillable = [
        'is_enabled',
        'firebase_project_id',
        'firebase_api_key',
        'firebase_auth_domain',
        'firebase_storage_bucket',
        'firebase_messaging_sender_id',
        'firebase_app_id',
        'firebase_measurement_id',
        'firebase_private_key_json',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
