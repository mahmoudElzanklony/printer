<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $groupedPermissions = $this->permissions->groupBy(function ($permission) {
            // Extract the icon prefix (before "|/|")
            return explode('|/|', $permission->name)[0];
        });

        return [
          'id'=>$this->id,
          'name'=>$this->name,
          'permissions'=>$groupedPermissions->map(function ($permissions, $icon) {
              return [
                  'icon' => $icon,
                  'permissions' => PermissionResource::collection($permissions),
              ];
          })->values(),
          'created_at'=>$this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
