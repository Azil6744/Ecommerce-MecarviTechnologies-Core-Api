<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'mailer',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_name',
        'from_email',
        'reply_to_email',
        'reply_to_name',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'smtp_port' => 'integer',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected $appends = [
        'has_smtp_password',
    ];

    public function setSmtpPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['smtp_password'] = Crypt::encryptString($value);
    }

    public function getDecryptedSmtpPassword(): ?string
    {
        if (empty($this->attributes['smtp_password'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['smtp_password']);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getHasSmtpPasswordAttribute(): bool
    {
        return ! empty($this->attributes['smtp_password']);
    }
}
