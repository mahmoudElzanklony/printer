<?php

namespace App\Http\Resources;

use App\Models\saved_properties_settings;
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
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'verified_at' => $this->phone_verified_at,
            'phone' => $this->phone,
            'otp' => $this->otp_secret,
            'has_password' => strlen($this->password) > 0 ? true : false,
            'wallet' => $this->wallet,
            'role' => $this->roles->pluck('name')[0] ?? 'client',
            'saved_properties_count' => saved_properties_settings::query()->where('user_id', $this->id)->count(),
            'image' => ImageResource::make($this->whenLoaded('image')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
        if (isset($this->default_location)) {
            $data['saved_location'] = SavedLocationResource::make($this->default_location);
        }
        if (isset($this->default_category_id)) {
            $data['default_category_id'] = $this->default_category_id;
        }
        if (isset($this->default_service_id)) {
            $data['default_service_id'] = $this->default_service_id;
        }
        if (isset($this->default_country_id)) {
            $data['default_country_id'] = $this->default_country_id;
        }
        if (isset($this->token)) {
            $data['token'] = $this->token;
        }
        if (isset($this->token)) {
            $data['token'] = $this->token;
        }
        if (! ($data['role'] == 'admin' || $data['role'] == 'client')) {
            $groupedPermissions = $this->getAllPermissions()->groupBy(function ($permission) {
                // Extract the icon prefix (before "|/|")
                return explode('|', $permission->name)[0];
            });
            $data['pages'] = $groupedPermissions->map(function ($permissions, $icon) {
                return [
                    'icon' => $icon,
                    'label' => __('admin_nav.'.$icon)['label'],
                    'parent' => __('admin_nav.'.$icon)['parent'] ?? null,
                    'permissions' => PermissionResource::collection($permissions),
                ];
            })->values();
        }

        return $data;
    }
}
