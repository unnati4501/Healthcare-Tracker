<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditZCSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-survey');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $expectSurveyName = function () {
            $survey = $this->route('zcSurvey');
            return ($survey->id ?? 0);
        };
        $rules                = [];
        $rules['title']       = "required|max:100|unique:zc_survey,title," . $expectSurveyName();
        $rules['description'] = "sometimes|nullable|max:250";
        $rules['questions']   = "required";
        return $rules;
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.unique'       => 'survey title is already exist. ',
            'questions.required' => 'Please add questions in the survey.',
        ];
    }

    public function attributes()
    {
        return [
            'title'       => 'survey title',
            'description' => 'survey description',
        ];
    }
}
