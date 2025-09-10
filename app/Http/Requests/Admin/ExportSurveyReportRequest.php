<?php
declare (strict_types = 1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Class ExportSurveyReportRequest
 *
 * @package App\Http\Requests\Admin
 */
class ExportSurveyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return access()->allow('export-survey-report') || access()->allow('masterclass-survey-report');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $user     = auth()->user();
        $timezone = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $now      = now($timezone);
        return [
            'email'      => 'required|email',
            'start_date' => "required|date_format:Y-m-d|before_or_equal:{$now}",
            'end_date'   => "required|date_format:Y-m-d|before_or_equal:{$now}",
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
            'start_date.before_or_equal' => 'The :attribute must be a date before or equal to today.',
            'end_date.before_or_equal'   => 'The :attribute must be a date before or equal to today.',
        ];
    }
}
