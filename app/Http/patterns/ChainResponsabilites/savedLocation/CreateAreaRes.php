<?php

namespace App\Http\patterns\ChainResponsabilites\savedLocation;

use App\Models\cities;
use App\Models\shipment_prices;

class CreateAreaRes extends createSavedLocationAbstract
{
    private $last_data = null;
    public function set_last_data($last_data)
    {
        $this->last_data = $last_data;
    }

    public function handle($data)
    {

        if(!(is_numeric($data['area_id']))){
            // create city
            $inputs = [
                'user_id'=>auth()->id(),
                'city_id' => $data['city_id'],
                'area'=>json_encode([$this->lang=>$data['area_id'],$this->other=>''],JSON_UNESCAPED_UNICODE),
                'price'=>50,
            ];

            $obj = shipment_prices::query()->create($inputs);
            $data['area_id'] = $obj->id;
        }

        $this->set_last_data($data);

    }
    public function get_last_data(){
        return $this->last_data;
    }
}
