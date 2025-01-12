<?php

namespace App\Http\Resources;

use App\Models\categories;
use App\Models\saved_locations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'=>$this->id,
            'username'=>$this->username,
            'email'=>$this->email,
            'verified_at'=>$this->phone_verified_at,
            'phone'=>$this->phone,
            'otp'=>$this->otp_secret,
            'wallet'=>$this->wallet,
            'role'=>$this->roles->pluck('name'),
            'created_at'=>$this->created_at->format('Y-m-d H:i:s')
        ];
        if(isset($this->default_location)){
            $data['saved_location'] = SavedLocationResource::make($this->default_location);
        }
        if(isset($this->default_category_id)){
            $data['default_category_id'] = $this->default_category_id;
        }
        if(isset($this->default_service_id)){
            $data['default_service_id'] = $this->default_service_id;
        }
        if(isset($this->token)){
            $data['token'] = $this->token;
        }
        return $data;
    }
}
