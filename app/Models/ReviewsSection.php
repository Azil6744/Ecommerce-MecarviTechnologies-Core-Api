<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewsSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'main_heading',
        'average_rating',
        'call_to_action_text',
        'client_label',
        'review_count',
        'button_text',
        'button_url',
        'avatar_1',
        'avatar_2',
        'avatar_3',
        'avatar_4',
        'background_color',
        'card_background_color',
    ];

    /**
     * Get the full URL for avatar images.
     *
     * @return array<string|null>
     */
    public function getAvatarUrlsAttribute()
    {
        $avatars = [];
        for ($i = 1; $i <= 4; $i++) {
            $field = "avatar_{$i}";
            $avatars["avatar_{$i}"] = $this->$field ? asset('storage/' . $this->$field) : null;
        }
        return $avatars;
    }

    /**
     * Get individual avatar URL accessors.
     */
    public function getAvatar1UrlAttribute()
    {
        return $this->avatar_1 ? asset('storage/' . $this->avatar_1) : null;
    }

    public function getAvatar2UrlAttribute()
    {
        return $this->avatar_2 ? asset('storage/' . $this->avatar_2) : null;
    }

    public function getAvatar3UrlAttribute()
    {
        return $this->avatar_3 ? asset('storage/' . $this->avatar_3) : null;
    }

    public function getAvatar4UrlAttribute()
    {
        return $this->avatar_4 ? asset('storage/' . $this->avatar_4) : null;
    }
}
