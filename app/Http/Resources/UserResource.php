<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */



    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'number' => $this->phone,
            'photo' => asset('uploads/personal_photo/' . $this->photo),
            'date_of_birth' => $this->date_of_birth,
            'national_id' => $this->national_id,
            'phone' => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles_name' => $this->roles->first()?->name ?? null,      ];
    }
}

