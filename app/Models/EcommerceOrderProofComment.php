<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrderProofComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'proof_id',
        'user_id',
        'author_type',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function proof()
    {
        return $this->belongsTo(EcommerceOrderProof::class, 'proof_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
