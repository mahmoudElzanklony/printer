<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedPropertiesSettingResource extends JsonResource
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
          'user_id'=>$this->user_id,
          'category_id'=>$this->category_id,
          'category'=>CategoryResource::make($this->whenLoaded('category')),
          'answers'=>SavedPropertiesSettingAnswersResource::collection($this->whenLoaded('answers')),
          'name'=>$this->name,
          'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
