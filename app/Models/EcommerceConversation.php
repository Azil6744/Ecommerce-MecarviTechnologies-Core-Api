<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'status',
        'linked_type',
        'linked_id',
        'linked_label',
        'last_customer_message_at',
        'last_admin_message_at',
        'last_message_at',
        'closed_at',
    ];

    protected $casts = [
        'last_customer_message_at' => 'datetime',
        'last_admin_message_at' => 'datetime',
        'last_message_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(EcommerceConversationMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(EcommerceConversationMessage::class, 'conversation_id')->latestOfMany();
    }
}
