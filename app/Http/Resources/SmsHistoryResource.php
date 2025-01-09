<?php

namespace App\Http\Resources;

use App\Models\languages;
use App\Services\FormRequestHandleInputs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $init = [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'user'=>UserResource::make($this->whenLoaded('user')),
            'users_no'=>$this->users_no,
            'limit_orders'=>$this->limit_orders,
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
        if(request()->hasHeader('AllLangs')){
            $langs = languages::query()->select('prefix')->get();
            // data
            $name = FormRequestHandleInputs::handle_output_column_for_all_lang('name',$this->name,$langs);
            $message = FormRequestHandleInputs::handle_output_column_for_all_lang('message',$this->message,$langs);
            return array_merge($init,$name,$message);
        }else{
            $data = [
                'name'=>FormRequestHandleInputs::handle_output_column($this->name),
                'message'=>FormRequestHandleInputs::handle_output_column($this->message),
            ];
            return array_merge($init,$data);
        }
    }
}
