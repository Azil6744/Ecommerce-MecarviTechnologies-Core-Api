<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'device_type',
        'device_title',
        'device_details',
        'location',
        'network',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
