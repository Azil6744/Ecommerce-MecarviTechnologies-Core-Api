<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'role',
        'email',
        'picture',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the full URL for the picture.
     *
     * @return string|null
     */
    public function getPictureUrlAttribute()
    {
        if (!$this->picture) {
            return null;
        }

        return Storage::disk('public')->url($this->picture);
    }
}
