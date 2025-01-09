<?php

namespace App\Actions;

use App\Models\categories;
use App\Models\saved_locations;
use App\Models\services;

class DefaultInfoWithUser
{
    public static function execute($user)
    {
        $user['default_category_id'] = categories::query()->first()->id;
        $user['default_service_id'] = services::query()->first()->id;
        $user['default_location'] = saved_locations::query()
            ->where("user_id", $user["id"])
            ->where("is_default", 1)
            ->first();

        return $user;
    }
}
