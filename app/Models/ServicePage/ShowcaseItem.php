<?php

namespace App\Models\ServicePage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowcaseItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'showcase_section_id',
        'title',
        'description',
        'image',
        'video',
        'background_color',
        'order',
    ];

    /**
     * Get the showcase section that owns the showcase item.
     *
     * @return BelongsTo
     */
    public function showcaseSection(): BelongsTo
    {
        return $this->belongsTo(ShowcaseSection::class);
    }

    /**
     * Get the full URL for the showcase item image.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    /**
     * Get the full URL for the showcase item video.
     *
     * @return string|null
     */
    public function getVideoUrlAttribute()
    {
        if (!$this->video) {
            return null;
        }

        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }

        return asset('storage/' . $this->video);
    }
}
