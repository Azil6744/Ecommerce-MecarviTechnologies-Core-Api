<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicketActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_ticket_id',
        'user_id',
        'type',
        'title',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(EcommerceTicket::class, 'ecommerce_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
