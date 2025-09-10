<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditSurveyQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax = config('zevolifesettings.fileSizeValidations.course_survey_question.logo', (2 * 1024));

        return [
            'title'    => 'required|max:150',
            'question' => 'required',
            'type'     => 'required',
            'score'    => 'required',
            'logo'     => "required|image|mimes:jpg,jpeg,png|max:{$logoMax}",
            'option'   => 'required|max:50',
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        $messages               = [];
        $messages['logo.image'] = 'The :attribute field must be an image.';
        $messages['logo.mimes'] = 'The :attribute feild must be a file of type: jpg, jpeg, png.';
        $messages['logo.max']   = 'The :attribute field may not be greater than 2MB.';
        return $messages;
    }

    public function attributes()
    {
        return [
            'title' => 'survey title',
            'type'  => 'question type',
            'logo'  => 'question logo',
        ];
    }
}
