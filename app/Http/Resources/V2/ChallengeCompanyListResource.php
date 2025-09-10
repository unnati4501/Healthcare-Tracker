<?php

namespace App\Http\Resources\V2;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeCompanyListResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->getTable() === 'freezed_challenge_participents') {
            $img           = [];
            $img['url']    = "";
            $img['width']  = 0;
            $img['height'] = 0;

            $companyRecord = \App\Models\Company::find($this->company_id);

            if (isset($companyRecord)) {
                return [
                    'id'    => $companyRecord->id,
                    'name'  => $companyRecord->name,
                    'image' => $companyRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                    'team'  => [
                        'id'   => $companyRecord->id,
                        'name' => $companyRecord->name,
                    ],
                ];
            } else {
                return [
                    'id'    => $this->company_id,
                    'name'  => 'Deleted',
                    'image' => (object) $img,
                    'team'  => [
                        'id'   => $this->company_id,
                        'name' => 'Deleted',
                    ],
                ];
            }
        } else {
            return [
                'id'    => $this->id,
                'name'  => $this->name,
                'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'team'  => [
                    'id'   => $this->id,
                    'name' => $this->name,
                ],
            ];
        }
    }
}
