<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'refresh_token',
        'refresh_token_expires_at',
        'last_access_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
