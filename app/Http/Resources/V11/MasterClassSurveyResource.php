<?php

namespace App\Http\Resources\V11;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterClassSurveyResource extends JsonResource
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
        $user = $this->user();

        $questionOption = array();
        if ($this->courseSurveyOptions()->count() > 0) {
            foreach ($this->courseSurveyOptions as $key => $value) {
                $questionOption[] = array('optionId' => $value->id, 'optionText' => $value->choice);
            }
        }

        $w         = 1280;
        $h         = 640;
        $xDeviceOs = strtolower(request()->header('X-Device-Os', ""));
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 400;
        }

        return [
            'questionId'   => $this->id,
            'questionText' => $this->title,
            'questionType' => $this->type,
            'questionLogo' => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'options'      => $questionOption,
        ];
    }
}
