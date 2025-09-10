<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CronofyAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->
            allow('availability');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules                          = array();
        $rules['slots']                 = "required_if:responsbility,1,3";
        $rules['slots_exist']           = 'required_if:responsbility,1,3';
        $rules['presenter_slots']       = "required_if:responsbility,2,3";
        $rules['presenter_slots_exist'] = 'required_if:responsbility,2,3';
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
            'slots.required_if'                 => 'The availability is required for the 1:1 Digital Therapy Sessions.',
            'slots_exist.required_if'           => 'The availability is required for the 1:1 Digital Therapy Sessions.',
            'presenter_slots.required_if'       => 'The availability is required for the Marketplace Events.',
            'presenter_slots_exist.required_if' => 'The availability is required for the Marketplace Events.',
        ];
    }
}
