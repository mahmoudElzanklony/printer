<?php

namespace App\Actions;

use App\Models\saved_locations;

class ChangeDefaultLocationToNonAction
{
    public static function execute()
    {
        saved_locations::query()
            ->where('user_id',auth()->id())
            ->where('is_default',1)
            ->update(['is_default'=>0]);
    }
}
