<?php

namespace App\Http\Resources\V11;

use Illuminate\Http\Resources\Json\JsonResource;

class GethelpResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userCompany = $this->company()->first();

        return [
            "support" => (!$userCompany->is_intercom) ? false : true,
            "faq"     => (!$userCompany->is_faqs) ? false : true,
            "eap"     => (!$userCompany->is_eap) ? false : true,
        ];
    }
}
