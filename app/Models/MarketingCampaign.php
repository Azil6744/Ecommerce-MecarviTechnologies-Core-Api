<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PUSH = 'push';

    protected $fillable = [
        'channel',
        'name',
        'audience_type',
        'segment',
        'recipients_count',
        'custom_recipients',
        'from_name',
        'from_email',
        'reply_to',
        'subject',
        'preview_text',
        'content_type',
        'body',
        'sender_name',
        'sender_phone',
        'notification_title',
        'notification_message',
        'deep_link',
        'platforms',
        'image_path',
        'schedule_type',
        'scheduled_at',
        'timezone',
        'status',
        'settings',
        'metrics',
        'last_test',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'custom_recipients' => 'array',
        'platforms' => 'array',
        'settings' => 'array',
        'metrics' => 'array',
        'last_test' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipients_count' => 'integer',
    ];

    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'channel' => $this->channel,
            'name' => $this->name,
            'audience_type' => $this->audience_type,
            'segment' => $this->segment,
            'recipients_count' => (int) $this->recipients_count,
            'custom_recipients' => $this->custom_recipients ?? [],
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'reply_to' => $this->reply_to,
            'subject' => $this->subject,
            'preview_text' => $this->preview_text,
            'content_type' => $this->content_type,
            'body' => $this->body,
            'sender_name' => $this->sender_name,
            'sender_phone' => $this->sender_phone,
            'notification_title' => $this->notification_title,
            'notification_message' => $this->notification_message,
            'deep_link' => $this->deep_link,
            'platforms' => $this->platforms ?? [],
            'image_path' => $this->image_path,
            'schedule_type' => $this->schedule_type,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'status' => $this->status,
            'settings' => $this->settings ?? [],
            'metrics' => $this->metrics ?? [],
            'last_test' => $this->last_test,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
