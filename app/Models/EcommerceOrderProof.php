<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrderProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'proof_type',
        'file_path',
        'status',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }
}
