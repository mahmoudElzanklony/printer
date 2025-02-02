<?php

namespace App\Services;

use App\Models\saved_locations;
use App\Models\saved_properties_settings;

class CheckMaxBeforeSaveService
{
    public static function execute_saved_locations(){
        if(auth()->user()->roleName() == 'client'){
            $check_len = saved_locations::query()
                ->where('user_id',auth()->id())
                ->count();
            if($check_len >= 5){
                abort(Messages::error(__('errors.max_value')));
            }
        }
    }

    public static function execute_saved_properties(){
        if(auth()->user()->roleName() == 'client'){
            $check_len = saved_properties_settings::query()
                ->where('user_id',auth()->id())
                ->count();
            if($check_len >= 5){
                abort(Messages::error(__('errors.max_value')));
            }
        }
    }
}
