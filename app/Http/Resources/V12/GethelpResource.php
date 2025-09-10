<?php

namespace App\Http\Resources\V12;

use App\Models\EAP;
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
        $xDeviceOs   = strtolower(Request()->header('X-Device-Os', ""));
        $isEap       = false;

        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $isEapCount = EAP::join('eap_company', 'eap_company.eap_id', '=', 'eap_list.id')->where('eap_company.company_id', $userCompany->id)->count();
            $isEap      = ($userCompany->is_eap && $isEapCount > 0) ;
        } else {
            $isEap = (!$userCompany->is_eap) ? false : true;
        }

        return [
            "support" => (!$userCompany->is_intercom) ? false : true,
            "faq"     => (!$userCompany->is_faqs) ? false : true,
            "eap"     => $isEap,
        ];
    }
}
