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
        'title',
        'file_path',
        'preview_file_path',
        'status',
        'expires_at',
        'approved_at',
        'rejected_at',
        'reviewed_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    public function comments()
    {
        return $this->hasMany(EcommerceOrderProofComment::class, 'proof_id')->latest();
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->resolveStoredUrl($this->file_path);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->resolveStoredUrl($this->preview_file_path ?: $this->file_path);
    }

    private function resolveStoredUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
