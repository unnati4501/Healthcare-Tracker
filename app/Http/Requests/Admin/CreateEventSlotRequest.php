<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventSlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('book-event');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'schedulingId' => 'required|string',
            'wsId'         => 'required|integer|exists:' . User::class . ',id',
        ];
    }
}
