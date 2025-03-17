<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use softDeletes;

    protected $with = ['city'];

    protected $guard_name = 'sanctum';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $attributes = [
        'username' => '',
        'email' => '',
        'password' => '',
        'phone' => '',
        'otp_secret' => null,
    ];

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'otp_secret',
        'wallet',
        'city_id',
        'birth_date',
    ];

    public function roleName()
    {
        return count($this->getRoleNames()) > 0 ? $this->getRoleNames()[0] : 'client';
    }

    public function password(): Attribute
    {
        return Attribute::make(set: fn ($val) => bcrypt($val));
    }

    public function otp_secret(): Attribute
    {
        return Attribute::make(set: fn () => rand(1000, 9999));
    }

    public function image()
    {
        return $this->morphOne(images::class, 'imageable');
    }

    public function orders()
    {
        return $this->hasMany(orders::class, 'user_id');
    }

    public function orders_items()
    {
        return $this->hasManyThrough(orders_items::class, orders::class, 'user_id', 'order_id');
    }

    public function socialAccounts()
    {
        return $this->hasMany(social_accounts::class, 'user_id');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'city_id')->withTrashed();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /*protected $casts = [
        'phone_verified_at' => 'datetime',
    ];*/
}
