<?php

namespace App\Http\Resources\V31;

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
        $user           = $this->user();
        $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));
        $width          = 1280;
        $height         = 640;
        $totalDuration  = $this->courseTotalDurarion();
        $totalDurations = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $width  = 1280;
            $height = 640;
        }
        $userMasterClassData = $this->courseUserLogs()->wherePivot("user_id", $user->id)->first();
        $cousreLog           = $user->courseLogs()->where('course_id', $this->id)->first();
        $joinedOn            = '';
        $completedOn         = '';
        if (!empty($cousreLog)) {
            $joinedOn    = $cousreLog->pivot->joined_on;
            $completedOn = $cousreLog->pivot->completed_on;
        }

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $this->instructions),
            'creator'          => $this->getCreatorData(),
            'image'            => $this->getMediaData('logo', ['w' => $width, 'h' => $height, 'zc' => 3]),
            "isEnrolled"       => (!empty($userMasterClassData) && $userMasterClassData->pivot->joined) ? true : false,
            "isCompleted"      => (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ? true : false,
            "totalLesson"      => $this->courseLessions()->where('status', true)->count(),
            "totalDuration"    => $totalDurations,
            'totalLessons'     => $this->when($xDeviceOs != config('zevolifesettings.PORTAL'), $this->courseLessions()->where('status', true)->count()),
            'completedLessons' => $this->when($xDeviceOs != config('zevolifesettings.PORTAL'), $user->completedLession($this->id)),
            'joinedOn'         => $this->when($xDeviceOs != config('zevolifesettings.PORTAL') && !empty($cousreLog), $joinedOn),
            'completedOn'      => $this->when($xDeviceOs != config('zevolifesettings.PORTAL') && $completedOn && !empty($cousreLog), $completedOn),
        ];
    }
}
