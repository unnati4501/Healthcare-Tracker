<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Models\ChallengeTarget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditLimitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('view-limits');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $type    = $this->input('type');
        $rules   = [];

        if ($type == "challenge") {
            $challenge_targets = ChallengeTarget::where("is_excluded", 0)->pluck('name', 'short_name')->toArray();

            foreach ($challenge_targets as $key => $value) {
                if ($key != "exercises") {
                    $rules[$key] = "required|integer|min:1|max:100000";
                }
            }

            $rules["exercises_distance"]     = "required|integer|min:1|max:100000";
            $rules["exercises_duration"]     = "required|integer|min:1|max:100000";
            $rules["daily_meditation_limit"] = "required|integer|min:1|max:100000";
            $rules["daily_track_limit"]      = "required|integer|min:1|max:100000";
        }

        if ($type == "reward") {
            $portal_limits = config('zevolifesettings.portal_limits');
            foreach ($portal_limits as $limitKey => $target) {
                $rules[$limitKey] = "required|integer|min:1|max:100000";
            }
        }

        if ($type == "reward-daily-limit") {
            $reward_point_daily_limit = config('zevolifesettings.reward_point_daily_limit');
            foreach ($reward_point_daily_limit as $limitKey => $target) {
                if (!empty($target)) {
                    $rules["dailylimit_" . $limitKey] = "required|integer|min:1|max:100000";
                }
            }
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
        return [];
    }

    public function attributes(): array
    {
        $attributes               = [];
        $reward_point_daily_limit = config('zevolifesettings.reward_point_daily_limit');
        foreach ($reward_point_daily_limit as $limitKey => $target) {
            if (!empty($target)) {
                $attributes["dailylimit_" . $limitKey] = $limitKey . ' daily limit';
            }
        }
        return $attributes;
    }
}
