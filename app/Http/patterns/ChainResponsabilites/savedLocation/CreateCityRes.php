<?php

namespace App\Http\patterns\ChainResponsabilites\savedLocation;

use App\Models\cities;

class CreateCityRes extends createSavedLocationAbstract
{
    public function handle($data)
    {

        if(!(is_numeric($data['city_id']))){
            // create city
            $inputs = [
              'user_id'=>auth()->id(),
              'country_id' => $data['country_id'],
              'name'=>json_encode([$this->lang=>$data['city_id'],$this->other=>''],JSON_UNESCAPED_UNICODE),
            ];

            $obj = cities::query()->create($inputs);
            $data['city_id'] = $obj->id;
        }
        parent::handle($data);
    }
}
