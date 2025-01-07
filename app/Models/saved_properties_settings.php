<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class saved_properties_settings extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','category_id','name'];

    protected static function boot(){
        parent::boot();
        static::saving(function ($model) {
            if (auth()->check() && !$model->isDirty('user_id')) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function answers()
    {
        return $this->hasMany(saved_properties_settings_answers::class,'saved_properties_settings_id');
    }
}
