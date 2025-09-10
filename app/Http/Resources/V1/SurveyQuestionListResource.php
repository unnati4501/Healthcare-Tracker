<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyQuestionListResource extends JsonResource
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
        $user = $this->user();

        $questionOption = array();
        if ($this->hsQuestionsOptions()->count() > 0) {
            foreach ($this->hsQuestionsOptions as $key => $value) {
                $questionOption[] = array('optionId' => $value->id, 'optionText' => $value->choice, 'optionScore' => $value->score);
            }
        }

        return [
            'questionId'       => $this->id,
            'questionText'     => $this->title,
            'options'          => $questionOption,
            'questionMaxScore' => $this->max_score,
            'questionType'     => $this->questionType,
            'questionImage'    => $this->getMediaData(),
            'questionCategory' => array('categoryId' => $this->category_id, 'categoryName' => $this->subCatName),
        ];
    }
}
