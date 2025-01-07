<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedPropertiesSettingAnswersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'saved_properties_settings_id '=>$this->saved_properties_settings_id,
            'property_id'=>$this->property_id,
            'property'=>PropertyResource::make($this->whenLoaded('property')),
            'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
