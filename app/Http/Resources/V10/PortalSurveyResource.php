<?php

namespace App\Http\Resources\V10;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalSurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $response = [
            'questionId'   => $this->question_id,
            'questionText' => $this->title,
            'questionLogo' => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'questionType' => $this->questiontype->name,
        ];

        if ($this->questiontype->name == 'choice') {
            $options = $this->getQuestionOptions()['score'];

            $response['options'] = [];
            foreach ($options as $value) {
                $response['options'][] = [
                    'optionId'       => $value['optionId'],
                    'optionText'     => $value['choice'],
                    'optionScore'    => $value['score'],
                    'optionImageUrl' => $value['imageUrl'],
                ];
            }
        }

        return $response;
    }
}
