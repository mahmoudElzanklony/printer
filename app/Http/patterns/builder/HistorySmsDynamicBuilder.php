<?php

namespace App\Http\patterns\builder;

use App\Http\patterns\strategy\Messages\SMSMessages;
use App\Models\orders;
use App\Models\sms_history;
use App\Services\FormRequestHandleInputs;

class HistorySmsDynamicBuilder
{
    private $users;

    public function __construct(private $basic_info)
    {}

    public function get_users_number(){
        $this->users = orders::query()->with('user')
            ->selectRaw('count(user_id) as orders , user_id')
            ->groupBy('user_id')
            ->havingRaw('count(orders) >= '.$this->basic_info['limit_orders'])
            ->get();
        // create at db
        $this->basic_info['users_no'] = sizeof($this->users);


        // send sms message
        $obj = new SMSMessages();
        foreach($this->users as $user){
            // TO DO SMS
            //$obj->send();
        }
        return $this;
    }

    public function save_DB(){
        $data =  FormRequestHandleInputs::handle_inputs_langs($this->basic_info,['name','message']);
        $result = sms_history::query()->create($data);
        $result->load('user');
        return $result;
    }
}
