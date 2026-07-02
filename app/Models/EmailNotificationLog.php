<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'email_template_id',
        'recipient_email',
        'recipient_type',
        'subject',
        'status',
        'error_message',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
