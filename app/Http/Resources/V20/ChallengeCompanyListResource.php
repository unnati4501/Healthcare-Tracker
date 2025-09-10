<?php

namespace App\Http\Resources\V20;

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
            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
            $img['width']  = 0;
            $img['height'] = 0;

            $companyRecord = \App\Models\Company::find($this->company_id);

            if (isset($companyRecord)) {
                return [
                    'id'      => $companyRecord->id,
                    'name'    => $companyRecord->name,
                    'image'   => $companyRecord->getMediaData('logo', ['w' => 320, 'h' => 320]),
                    'deleted' => false,
                    'team'    => [
                        'id'   => $companyRecord->id,
                        'name' => $companyRecord->name,
                    ],
                ];
            } else {
                return [
                    'id'      => $this->company_id,
                    'name'    => 'Deleted Company',
                    'image'   => (object) $img,
                    'deleted' => true,
                    'team'    => [
                        'id'   => $this->company_id,
                        'name' => 'Deleted Company',
                    ],
                ];
            }
        } else {
            return [
                'id'      => $this->id,
                'name'    => $this->name,
                'image'   => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
                'deleted' => false,
                'team'    => [
                    'id'   => $this->id,
                    'name' => $this->name,
                ],
            ];
        }
    }
}
