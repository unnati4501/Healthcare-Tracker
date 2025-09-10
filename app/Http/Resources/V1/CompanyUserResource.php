<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyUserResource extends JsonResource
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

        $userTeam = $this->teams()->first();
        $userDept = $this->department()->first();

        $team = $dept = $company = [];
        if (!empty($userTeam)) {
            $team['id']   = $userTeam->getKey();
            $team['name'] = $userTeam->name;

            $dept['id']   = $userDept->getKey();
            $dept['name'] = $userDept->name;
        }

        return [
            'id'         => $this->id,
            'name'       => $this->first_name . " " . $this->last_name,
            'image'      => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'team'       => $team,
            'department' => $dept,
        ];
    }
}
