<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

class HandleDefaultRecordAction
{

    public static function execute(array $data, string $modelClass, ?int $excludeId = null): array
    {
        if (isset($data['is_default']) && ($data['is_default'] == 1 || $data['is_default'] === true)) {
            $query = $modelClass::query();

            // remove current record from the update if excludeId is provided
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            $query->update(['is_default' => 0]);
            $data['is_default'] = 1;
        } else {
            $data['is_default'] = 0;
        }

        return $data;
    }


    public static function getDefault(string $modelClass)
    {
        return $modelClass::query()
            ->where('is_default', 1)
            ->first();
    }
}

