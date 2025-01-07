<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class cities extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id','country_id','name'];


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function country()
    {
        return $this->belongsTo(countries::class)->withTrashed();
    }
}
