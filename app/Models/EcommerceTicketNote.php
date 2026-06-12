<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicketNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_ticket_id',
        'user_id',
        'note',
        'visibility',
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
