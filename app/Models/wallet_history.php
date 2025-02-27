<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wallet_history extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'type', 'status'];

    public function activeScope(Builder $query)
    {
        $query->where('user_id', auth()->id());
    }
}
