<?php

namespace App\Actions;

use App\Models\categories;
use App\Models\countries;
use App\Models\saved_locations;
use App\Models\services;

class DefaultInfoWithUser
{
    public static function execute($user)
    {
        $defaultCategory = HandleDefaultRecordAction::getDefault(categories::class);
        $user['default_category_id'] = $defaultCategory?->id;

        $defaultService = HandleDefaultRecordAction::getDefault(services::class);
        $user['default_service_id'] = $defaultService?->id;

        $defaultCountry = HandleDefaultRecordAction::getDefault(countries::class);
        $user['default_country_id'] = $defaultCountry?->id;

        $user['default_location'] = saved_locations::query()
            ->where("user_id", $user["id"])
            ->where("is_default", 1)
            ->first();

        return $user;
    }
}
