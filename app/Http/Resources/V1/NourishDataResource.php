<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NourishDataResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->user();
        $appTimezone = config('app.timezone');

        // get user weight goal and lates weight data
        $weight = array();
        $userWeightHistory = $this->weights()->orderBy('id', 'DESC')->limit(2)->get();
        if ($userWeightHistory->count() > 0) {
            $weight['current']   = $userWeightHistory[0]->weight;
            $weight['sinceDate'] = Carbon::parse($userWeightHistory[0]->log_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();

            if (!empty($userWeightHistory[1])) {
                $weight['before'] = $userWeightHistory[1]->weight;
            } else {
                $weight['before'] = 0.0;
            }
        } else {
            $weight['current']   = 0.0;
            $weight['before']   = 0.0;
            $weight['sinceDate'] = now($user->timezone)->toDateTimeString();
        }
        $weight['goal'] = (!empty($this->goal) && !empty($this->goal->weight)) ? $this->goal->weight : 0.0;

        // get user calories goal and lates calories data
        $calories = array();

        $userCalorieHistory = $this->steps()->select(\DB::raw("SUM(user_step.calories) as calories"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', now($user->timezone)->toDateString())
                ->first();

        $userCalorieAvg = $this->steps()
                ->orderBy('user_step.log_date', 'DESC')
                ->limit(7)
                ->get()
                ->pluck('calories')
                ->toArray();
                
        if (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) {
            $calories['current'] = (double) $userCalorieHistory['calories'];
        } else {
            $calories['current']   = 0.0;
        }

        if (!empty($userCalorieAvg)) {
            $calories['before'] = (double) round(array_sum($userCalorieAvg) / count($userCalorieAvg), 1);
        } else {
            $calories['before'] = 0.0;
        }

        $calories['goal'] = (!empty($this->goal) && !empty($this->goal->calories)) ? $this->goal->calories : 0.0;


        $bmis = array();
        $userbmisHistory = $this->bmis()->orderBy('id', 'DESC')->limit(2)->get();
        if ($userbmisHistory->count() > 0) {
            $bmis['current'] = round($userbmisHistory[0]->bmi, 1);

            if (!empty($userbmisHistory[1])) {
                $bmis['before'] = round($userbmisHistory[1]->bmi, 1);
            } else {
                $bmis['before'] = 0.0;
            }
        } else {
            $bmis['current'] = 0.0;
            $bmis['before'] = 0.0;
        }

        return [
            "weight" => $weight,
            "calorie" => $calories,
            "bmi" => $bmis,
        ];
    }
}
