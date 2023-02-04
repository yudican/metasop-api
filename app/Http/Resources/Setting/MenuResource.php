<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'menu_label' => $this->menu_label,
            'menu_icon' => $this->menu_icon,
            'menu_route' => $this->menu_route,
            'menu_order' => $this->menu_order,
            'show_menu' => $this->show_menu,
            'parent_id' => $this->parent_id,
            'children' => MenuResource::collection($this->children),
            'roles' => RoleResource::collection($this->roles),
        ];
    }
}
