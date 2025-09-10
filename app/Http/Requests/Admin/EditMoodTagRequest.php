<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditMoodTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-mood-tags');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload            = $this->input();
        $payload['routeId'] = $this->route('moodTag')->id;
        $rules              = array();

        $rules['tag'] = ['required', 'regex:/(^[A-Za-z ]+$)+/', 'max:15',
            Rule::unique('mood_tags')
                ->where(function ($query) use ($payload) {
                    return $query->where('tag', @$payload['tag'])
                        ->where('id', '!=', $payload['routeId']);
                })];

        return $rules;
    }

    public function attributes()
    {
        return [
            'tag' => 'tag name',
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tag.unique' => 'tag name already exists!',
        ];
    }
}
