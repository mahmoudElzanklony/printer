<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class properties_icons extends Model
{
    use HasFactory;

    protected $fillable = ['property_id','label','icon'];
}
