<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_name',
        'order',
    ];

    /**
     * Get the tabs for this category.
     */
    public function tabs()
    {
        return $this->hasMany(WhatWeCreateTab::class);
    }
}
