<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class social_accounts extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'provider_id', 'provider', 'access_token', 'refresh_token', 'expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
