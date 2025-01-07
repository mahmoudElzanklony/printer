<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class shipment_prices extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id','city_id','area','price'];

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function city(){
        return $this->belongsTo(cities::class)->withTrashed();
    }

}
