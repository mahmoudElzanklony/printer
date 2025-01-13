<?php

namespace App\Models;

use App\Http\Enum\ContactTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contacts extends Model
{
    use HasFactory;

    protected $fillable = ['username','email','phone','message','status'];

    protected $casts = [
        'status' => ContactTypeEnum::class
    ];
}
