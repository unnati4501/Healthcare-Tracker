<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Event;

class EditBookEventRequest extends FormRequest
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
        $bookingLog = $this->route('bookingLog');
        $event = Event::find($bookingLog->event_id);
        $locationTypes = config('zevolifesettings.event-location-type');
        $rules = [
            'notes'           => 'sometimes|nullable',
            'company'         => 'nullable|integer',
            'date'            => 'nullable|date|date_format:d-m-Y',
            'email_notes'     => 'sometimes|nullable',
            'company_type'    => 'nullable',
            'email'           => 'nullable'
        ];
        
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
            'selectedslot.required_with_all' => 'Please select new presenter/time for event',
            'presenterName.required_if'      => 'Please enter presenter name',
        ];
    }

    public function attributes()
    {
        return [
            'notes'         => 'additional notes',
            'date'          => 'date',
            'selectedslot'  => 'slot',
            'presenterName' => 'presenter name',
        ];
    }
}
