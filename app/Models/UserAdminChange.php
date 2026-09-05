<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdminChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'actor_name',
        'actor_role',
        'title',
        'description',
        'changed_fields',
        'before_value',
        'after_value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
