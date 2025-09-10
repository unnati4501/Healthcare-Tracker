<?php

namespace App\Http\Resources\V6;

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

        return [
            'questionId'   => $this->id,
            'questionText' => $this->title,
            'questionType' => $this->type,
            'questionLogo' => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'options'      => $questionOption,
        ];
    }
}
