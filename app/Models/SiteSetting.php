<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';

    protected $fillable = [
        'site_name',
        'seo_site_title',
        'seo_description',
        'seo_keywords',
        'logo',
        'login_logo',
        'favicon',
        'button_name',
        'button_url',
        'theme_primary_color',
        'theme_secondary_color',
    ];

    /**
     * Logo URL: if stored path (no leading / or http), return asset('storage/...'); otherwise return as-is.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }
        $logo = $this->logo;
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '/')) {
            return $logo;
        }

        return asset('storage/' . $logo);
    }

    /**
     * Favicon URL: if stored path (no leading / or http), return asset('storage/...'); otherwise return as-is.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        if (! $this->favicon) {
            return null;
        }
        $favicon = $this->favicon;
        if (str_starts_with($favicon, 'http://') || str_starts_with($favicon, 'https://') || str_starts_with($favicon, '/')) {
            return $favicon;
        }

        return asset('storage/' . $favicon);
    }

    /**
     * Login logo URL: if stored path (no leading / or http), return asset('storage/...'); otherwise return as-is.
     */
    public function getLoginLogoUrlAttribute(): ?string
    {
        if (! $this->login_logo) {
            return null;
        }
        $loginLogo = $this->login_logo;
        if (str_starts_with($loginLogo, 'http://') || str_starts_with($loginLogo, 'https://') || str_starts_with($loginLogo, '/')) {
            return $loginLogo;
        }

        return asset('storage/' . $loginLogo);
    }
}
