<?php

namespace App\Http\Resources\V11;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseMasterClassResource extends JsonResource
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
        /** @var User $user */
        $user          = $this->user();
        $xDeviceOs     = strtolower($request->header('X-Device-Os', ""));
        $width         = 1280;
        $height        = 640;
        $totalDuration = $this->courseTotalDurarion();
        $totalDurations = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $width  = 1280;
            $height = 640;
        }
        $userMasterClassData = $this->courseUserLogs()->wherePivot("user_id", $user->id)->first();

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $this->instructions),
            'creator'       => $this->getCreatorData(),
            'image'         => $this->getMediaData('logo', ['w' => $width, 'h' => $height, 'zc' => 3]),
            "isEnrolled"    => (!empty($userMasterClassData) && $userMasterClassData->pivot->joined) ,
            "isCompleted"   => (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ,
            "totalLesson"   => $this->courseLessions()->where('status', true)->count(),
            "totalDuration" => $totalDurations,
        ];
    }
}
