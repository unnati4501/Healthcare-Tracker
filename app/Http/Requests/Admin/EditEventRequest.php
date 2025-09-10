<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->
allow('edit-event');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $event   = $this->route('event');

        $eventLogoMax = config('zevolifesettings.fileSizeValidations.event.logo', 2048);
        $rules = [
            'description'        => 'required',
            'fees'               => 'sometimes|nullable|numeric|digits_between:1,10',
            'company_visibility' => 'required',
            'company_visibility.*'     => 'integer',
            'capacity'           => 'sometimes|nullable|numeric|digits_between:1,10',
            'logo'               => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$eventLogoMax}",
                Rule::dimensions()->minWidth(1024)->minHeight(1024)->ratio(1 / 1.0),
            ],
        ];

        if ($role->group == 'zevo') {
            $rules += [
                'presenter' => 'required',
            ];
        } else {
            $rules += [
                'presenterName'             => 'required|max:100',
                'specialEventCategoryTitle' => 'required_if:is_special,1'
            ];
        }

        if ($event->status == 1) {
            $rules += [
                'name'        => 'required|max:100',
                'subcategory' => 'required|integer',
                'duration'    => 'required',
                'location'    => 'required',
            ];
        }

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
            'logo.max'                 => 'The :attribute field may not be greater than 2MB.',
            'logo.image'               => 'The :attribute field must be an image.',
            'logo.mimes'               => 'The :attribute feild must be a file of type: jpg, jpeg, png.',
            'description.introduction' => 'The :attribute field may not be greater than 1000 characters.',
            'logo.dimensions'          => 'The uploaded image does not match the given dimension and ratio.',
            'presenterName.required'   => 'Please enter presenter name',
            'specialEventCategoryTitle.required_if' => 'The category field is required'

        ];
    }

    public function attributes()
    {
        return [
            'logo'        => 'event logo',
            'name'        => 'event name',
            'fees'        => 'event fees',
            'description' => 'event description',
            'subcategory' => 'event category',
            'location'    => 'location type',
        ];
    }
}
