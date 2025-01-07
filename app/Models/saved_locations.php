<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class saved_locations extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id','area_id','address','latitude','longitude','is_default'];

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function area(){
        return $this->belongsTo(shipment_prices::class)->withTrashed();
    }

}
