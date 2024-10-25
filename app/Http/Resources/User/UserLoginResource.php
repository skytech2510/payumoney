<?php

namespace App\Http\Resources\User;

use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $defaultCountry = Country::where('is_default', 'yes')->value('short_name');
        return [
            'user_id'        => $this->id,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'full_name'      => $this->full_name,
            'email'          => $this->email,
            'formattedPhone' => $this->formattedPhone,
            'picture'        => image($this->picture, 'profile'),
            'defaultCountry' => strtolower($defaultCountry),
            'token'          => $this->createToken($this->full_name)->accessToken,
            'userStatus'     => $this->status
          ];
    }
}
