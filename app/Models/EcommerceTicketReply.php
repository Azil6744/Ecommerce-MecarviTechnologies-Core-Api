<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_ticket_id',
        'user_id',
        'admin_reply',
        'message',
        'attachments',
    ];

    protected $casts = [
        'admin_reply' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(EcommerceTicket::class, 'ecommerce_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replyAttachments()
    {
        return $this->hasMany(EcommerceTicketAttachment::class, 'ecommerce_ticket_reply_id');
    }
}
