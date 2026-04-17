<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'customer_name',
        'subject',
        'priority',
        'status',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
