<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCustomizationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'file_type',
    ];

    public function draft()
    {
        return $this->belongsTo(ProductCustomizationDraft::class, 'draft_id');
    }
}
