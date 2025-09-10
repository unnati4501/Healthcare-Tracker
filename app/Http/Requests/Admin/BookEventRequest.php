<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;

class BookEventRequest extends FormRequest
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
        $event         = $this->route('event');
        $locationTypes = config('zevolifesettings.event-location-type');
        $rules         = [
            'notes'            => 'sometimes|nullable',
            'company'          => 'required|integer|check_credit|integer|exists:' . Company::class . ',id',
            'email_notes'      => 'sometimes|nullable',
            'company_type'     => 'nullable',
            'email'            => 'nullable',
            'registrationdate' => 'required',
            'ws_user'          => 'required',
            'capacity'         => 'nullable|integer|max:1000',
            'scheduling_id'    => 'nullable',
        ];
        if (!empty($event) && $event->company_id == null) {
            $rules += [
                'selectedslot' => 'required_with_all:company,date,timeFrom,timeFrom',
            ];
        }

        if (!empty($event) && strtolower($locationTypes[$event->location_type]) == strtolower('Online')) {
            $rules += [
                'video_link' => 'required|url',
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
            'notes.introduction'             => 'The :attribute field may not be greater than 500 characters.',
            'email_notes.introduction'       => 'The :attribute field may not be greater than 500 characters.',
            'selectedslot.required_with_all' => 'Please select presenter/time for event',
            'presenterName.required_if'      => 'Please enter presenter name',
            'company.check_credit'           => 'The selected :attribute does not have sufficient credits to make this booking. Please reach out the the Customer Support team at support@zevohealth.zendesk.com for further assistance. Thank you!',
            'registrationdate.required'      => 'The registration date field is required.',
            'ws_user.required'               => 'The welllbeing specialist field is required.',
        ];
    }

    public function attributes()
    {
        return [
            'notes'         => 'additional notes',
            'date'          => 'date',
            //'timeFrom'      => 'to time',
            //'timeFrom'      => 'from time',
            'selectedslot'  => 'slot',
            'presenterName' => 'presenter name',
        ];
    }
}
