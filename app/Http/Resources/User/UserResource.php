<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
{
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'last_name'  => $this->last_name,
            'password'   => $this->password,
            'phrase'     => $this->phrase,
            'role_id'    => $this->role_id,
        ];
    }

    public function with($request)
    {
        return [
            'version' => '1.0',
        ];
    }
}
