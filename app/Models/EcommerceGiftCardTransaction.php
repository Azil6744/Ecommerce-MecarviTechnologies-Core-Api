<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceGiftCardTransaction extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_gift_card_transactions';

    protected $fillable = [
        'giftcard_id',
        'transaction_type',
        'amount',
        'order_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'giftcard_id' => 'integer',
        'order_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function giftCard()
    {
        return $this->belongsTo(EcommerceGiftCard::class, 'giftcard_id');
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
