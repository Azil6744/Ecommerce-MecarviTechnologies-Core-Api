<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'icon',
        'title',
        'description',
        'order',
    ];

    /**
     * Get the full URL for the icon.
     *
     * @return string|null
     */
    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon);
        }
        return null;
    }
}

