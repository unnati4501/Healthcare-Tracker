<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseWeekRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('manage-course-modules');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:2|max:50',
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
