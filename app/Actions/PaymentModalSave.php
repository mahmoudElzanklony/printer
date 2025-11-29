<?php

namespace App\Actions;

use App\Models\payments;
use App\Models\taxes;

class PaymentModalSave
{
    public static function make($id,$model_name,$money,$type = 'visa',$updated_id = null,$shipment_price = null){
        payments::query()->updateOrCreate([
            'id'=>$updated_id,
        ],[
            'paymentable_id'=>$id,
            'paymentable_type'=>'App\Models\\'.$model_name,
            'money'=>$money,
            'shipment_price'=>$shipment_price,
            'tax'=>taxes::query()->first()->percentage ?? 0,
            'type'=>$type
        ]);
        return true;
    }
}
