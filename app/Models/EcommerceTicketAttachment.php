<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_ticket_id',
        'ecommerce_ticket_reply_id',
        'user_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function ticket()
    {
        return $this->belongsTo(EcommerceTicket::class, 'ecommerce_ticket_id');
    }

    public function reply()
    {
        return $this->belongsTo(EcommerceTicketReply::class, 'ecommerce_ticket_reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
