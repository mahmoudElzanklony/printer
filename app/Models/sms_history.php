<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sms_history extends Model
{
    use HasFactory;

    public static function boot(){
        parent::boot();
        static::creating(function(Model $model){
            if(auth()->check() && !($model->isDirty('user_id'))){
                $model->user_id = auth()->id();
            }
        });
    }

    protected $fillable = ['user_id','name','message','users_no','limit_orders'];

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
}
