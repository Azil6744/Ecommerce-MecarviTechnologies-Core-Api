<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceGiftCardTransfer extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_gift_card_transfers';

    protected $fillable = [
        'giftcard_id',
        'old_owner_id',
        'new_owner_id',
        'old_owner_email',
        'new_owner_email',
        'transfer_reason',
        'transferred_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'giftcard_id' => 'integer',
        'old_owner_id' => 'integer',
        'new_owner_id' => 'integer',
    ];

    public function giftCard()
    {
        return $this->belongsTo(EcommerceGiftCard::class, 'giftcard_id');
    }

    public function oldOwner()
    {
        return $this->belongsTo(User::class, 'old_owner_id');
    }

    public function newOwner()
    {
        return $this->belongsTo(User::class, 'new_owner_id');
    }
}
