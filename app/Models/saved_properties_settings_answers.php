<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class saved_properties_settings_answers extends Model
{
    use HasFactory;

    protected $fillable = ['saved_properties_settings_id','property_id'];

    public function property()
    {
        return $this->belongsTo(properties::class)->withTrashed();
    }
}
