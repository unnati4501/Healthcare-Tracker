<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GetLogWeightRequest;
use App\Http\Requests\Api\V1\GetWeightRequest;
use App\Http\Resources\V1\NourishDataResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NourishController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function plans(Request $request)
    {
        $jsonString = '{"code":200,"message":"Subscription plans retrieved successfully.","result":{"data":{"description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur varius orci tortor, vel dignissim sem bibendum ac. Aenean pulvinar sem id aliquet ornare. Aliquam erat volutpat. Nunc fermentum turpis sit amet ipsum scelerisque, at auctor magna fermentum. Quisque nec dignissim nisi. Curabitur vel auctor est. Proin eu libero eu lacus tempus commodo et laoreet mauris. Aliquam tempus ac lacus in cursus.","plans":[{"id":1,"label":"Monthly","days":30,"amount":40,"info":["Basic info about plan","More info about plans"]},{"id":2,"label":"Yearly","days":365,"amount":100,"info":["Basic info about plan","More info about plans"]}]}}}';

        return json_decode($jsonString, true);
    }

    public function getNourishData(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get group details data with json response
            $data = array("data" => new NourishDataResource($user));

            return $this->successResponse($data, 'Nourish data retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function logWeight(GetLogWeightRequest $request)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user                      = $this->user();
            $existingProfile           = $user->profile;
            $appTimezone               = config('app.timezone');
            $timezone                  = $user->timezone ?? $appTimezone;
            $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);

            $userGoalData             = array();
            $userGoalData['weight']   = $request->input('weightGoal');
            $userGoalData['calories'] = $request->input('calorieGoal');
            // create or update user goal
            $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $userGoalData);

            //Delete all weight entry for current day before insert
            $user->weights()
                ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                ->get()->each->delete();

            // create weight entry for user
            $user->weights()->create([
                'weight'   => $request->input('weight'),
                'log_date' => now()->toDateTimeString(),
            ]);

            if (!empty($existingProfile) && !empty($existingProfile->height)) {
                //Delete all user bmi entry for current day before insert
                $user->bmis()
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                    ->get()->each->delete();

                // calculate bmi and store
                $bmi = $request->input('weight') / pow(($existingProfile->height / 100), 2);

                $user->bmis()->create([
                    'bmi'      => $bmi,
                    'weight'   => $request->input('weight'), // kg
                    'height'   => $existingProfile->height, // cm
                    'age'      => $existingProfile->age,
                    'log_date' => now()->toDateTimeString(),
                ]);
            }
            \DB::commit();

            return $this->successResponse([], "Record logged successfully.");
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getWeightGraph(GetWeightRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = [];

            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                $lastWeight = 0;
                foreach ($totalWeeks as $week => $dates) {
                    $count = $user->weights()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->select('weight', \DB::raw("DATE(log_date) as log_dt"))
                        ->get()
                        ->pluck('weight', 'log_dt')
                        ->toArray();

                    $stDate    = $dates['week_start'];
                    $weekCount = 0;
                    $weekSum   = 0;
                    while (strtotime($stDate) <= strtotime($dates['week_end'])) {
                        if ($stDate > now($timezone)->toDateString()) {
                            break;
                        }

                        if (!empty($count) && !empty($count[$stDate])) {
                            $lastWeight = $count[$stDate];
                        }

                        if (!empty($lastWeight)) {
                            $weekCount++;
                        }
                        $weekSum += $lastWeight;

                        $stDate = date("Y-m-d", strtotime("+1 day", strtotime($stDate)));
                    }

                    $weekData        = [];
                    $weekData['key'] = $week;
                    if (!empty($weekSum) && !empty($weekCount)) {
                        $weekData['value'] = (double) round(($weekSum / $weekCount), 1);
                    } else {
                        $weekData['value'] = (double) 0;
                    }

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr   = getMonths($request->year);
                $lastWeight = 0;
                foreach ($monthArr as $month => $monthName) {
                    $count = $user->weights()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->select('weight', \DB::raw("DATE(log_date) as log_dt"))
                        ->get()
                        ->pluck('weight', 'log_dt')
                        ->toArray();

                    $monthCount = 0;
                    $monthSum   = 0;
                    $dt         = $request->year . '-' . $month . '-01';
                    $edt        = $request->year . '-' . $month . '-' . date('t', strtotime($dt));

                    while (strtotime($dt) <= strtotime($edt)) {
                        if ($dt > now($timezone)->toDateString()) {
                            break;
                        }

                        if (!empty($count) && !empty($count[$dt])) {
                            $lastWeight = $count[$dt];
                        }

                        if (!empty($lastWeight)) {
                            $monthCount++;
                        }

                        $monthSum += $lastWeight;

                        $dt = date("Y-m-d", strtotime("+1 day", strtotime($dt)));
                    }

                    $monthData        = [];
                    $monthData['key'] = ucfirst($monthName);

                    if (!empty($monthSum) && !empty($monthCount)) {
                        $monthData['value'] = (double) round(($monthSum / $monthCount), 1);
                    } else {
                        $monthData['value'] = (double) 0;
                    }

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr   = getDates($request->year, $timezone);
                $lastWeight = 0;
                foreach ($datesArr as $key => $date) {
                    $count = $user->weights()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                        ->orderBy('user_weight.log_date', 'DESC')
                        ->select('user_weight.weight', 'user_weight.log_date')
                        ->first();

                    if (!empty($count)) {
                        $lastWeight = $count->weight;
                    }

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (double) $lastWeight;

                    array_push($data, $dateData);
                }
            }

            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'data retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getWeightHistory(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $userWeightHistory = $user->weights()
                ->orderBy('user_weight.log_date', 'DESC')
                ->limit(7)
                ->get()
                ->toArray();

            $data = array();

            if (!empty($userWeightHistory)) {
                $userWeightHistory = array_reverse($userWeightHistory);

                $userWeightPreviousData = $user->weights()
                    ->where("user_weight.id", "<", $userWeightHistory[0]['id'])
                    ->orderBy('user_weight.log_date', 'DESC')
                    ->first();

                $lastWeight = 0.0;
                if (!empty($userWeightPreviousData)) {
                    $lastWeight = $userWeightPreviousData->weight;
                }

                foreach ($userWeightHistory as $key => $value) {
                    $data[] = [
                        "id"        => $value['id'],
                        "date"      => Carbon::parse($value['log_date'], config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
                        "deviation" => ($lastWeight == 0) ? 0.0 : round(($value['weight'] - $lastWeight), 1),
                        "weight"    => $value['weight'],
                    ];

                    $lastWeight = (double) $value['weight'];
                }
                $data = array_reverse($data);
            }

            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'Weight history retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getBmiGraph(GetWeightRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = [];

            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                $lastWeight = 0;
                foreach ($totalWeeks as $week => $dates) {
                    $count = $user->bmis()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->select('bmi', \DB::raw("DATE(log_date) as log_dt"))
                        ->get()
                        ->pluck('bmi', 'log_dt')
                        ->toArray();

                    $stDate    = $dates['week_start'];
                    $weekCount = 0;
                    $weekSum   = 0;
                    while (strtotime($stDate) <= strtotime($dates['week_end'])) {
                        if ($stDate > now($timezone)->toDateString()) {
                            break;
                        }

                        if (!empty($count) && !empty($count[$stDate])) {
                            $lastWeight = $count[$stDate];
                        }

                        if (!empty($lastWeight)) {
                            $weekCount++;
                        }

                        $weekSum += $lastWeight;

                        $stDate = date("Y-m-d", strtotime("+1 day", strtotime($stDate)));
                    }

                    $weekData        = [];
                    $weekData['key'] = $week;
                    if (!empty($weekSum) && !empty($weekCount)) {
                        $weekData['value'] = (double) round(($weekSum / $weekCount), 1);
                    } else {
                        $weekData['value'] = (double) 0;
                    }

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr   = getMonths($request->year);
                $lastWeight = 0;
                foreach ($monthArr as $month => $monthName) {
                    $count = $user->bmis()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->select('bmi', \DB::raw("DATE(log_date) as log_dt"))
                        ->get()
                        ->pluck('bmi', 'log_dt')
                        ->toArray();

                    $monthCount = 0;
                    $monthSum   = 0;
                    $dt         = $request->year . '-' . $month . '-01';
                    $edt        = $request->year . '-' . $month . '-' . date('t', strtotime($dt));

                    while (strtotime($dt) <= strtotime($edt)) {
                        if ($dt > now($timezone)->toDateString()) {
                            break;
                        }

                        if (!empty($count) && !empty($count[$dt])) {
                            $lastWeight = $count[$dt];
                        }

                        if (!empty($lastWeight)) {
                            $monthCount++;
                        }

                        $monthSum += $lastWeight;

                        $dt = date("Y-m-d", strtotime("+1 day", strtotime($dt)));
                    }

                    $monthData        = [];
                    $monthData['key'] = ucfirst($monthName);

                    if (!empty($monthSum) && !empty($monthCount)) {
                        $monthData['value'] = (double) round(($monthSum / $monthCount), 1);
                    } else {
                        $monthData['value'] = (double) 0;
                    }

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr   = getDates($request->year, $timezone);
                $lastWeight = 0;
                foreach ($datesArr as $key => $date) {
                    $count = $user->bmis()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                        ->orderBy('user_bmi.log_date', 'DESC')
                        ->select('user_bmi.bmi', 'user_bmi.log_date')
                        ->first();

                    if (!empty($count)) {
                        $lastWeight = $count->bmi;
                    }

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (double) round($lastWeight, 1);

                    array_push($data, $dateData);
                }
            }

            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'data retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getBmiHistory(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $userBmiHistory = $user->bmis()
                ->orderBy('user_bmi.log_date', 'DESC')
                ->limit(7)
                ->get()
                ->toArray();

            $data = array();

            if (!empty($userBmiHistory)) {
                $userBmiHistory = array_reverse($userBmiHistory);

                $userBmiPreviousData = $user->bmis()
                    ->where("user_bmi.id", "<", $userBmiHistory[0]['id'])
                    ->orderBy('user_bmi.log_date', 'DESC')
                    ->first();

                $lastWeight = 0.0;
                if (!empty($userBmiPreviousData)) {
                    $lastWeight = $userBmiPreviousData->bmi;
                }

                foreach ($userBmiHistory as $key => $value) {
                    $data[] = [
                        "id"        => $value['id'],
                        "date"      => Carbon::parse($value['log_date'], config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
                        "deviation" => ($lastWeight == 0) ? 0.0 : round(($value['bmi'] - $lastWeight), 1),
                        "bmi"       => round($value['bmi'], 1),
                    ];

                    $lastWeight = $value['bmi'];
                }
                $data = array_reverse($data);
            }

            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'bmi history retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getCalorieGraph(GetWeightRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = [];

            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                $lastWeight = 0;
                foreach ($totalWeeks as $week => $dates) {
                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->sum('user_step.calories');

                    $stDate    = $dates['week_start'];
                    $weekCount = 0;
                    while (strtotime($stDate) <= strtotime($dates['week_end'])) {
                        if ($stDate > now($timezone)->toDateString()) {
                            break;
                        }

                        $weekCount++;
                        $stDate = date("Y-m-d", strtotime("+1 day", strtotime($stDate)));
                    }

                    $weekData        = [];
                    $weekData['key'] = $week;

                    if (!empty($weekCount)) {
                        $weekData['value'] = (double) round(($count / $weekCount), 1);
                    } else {
                        $weekData['value'] = 0;
                    }

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr   = getMonths($request->year);
                $lastWeight = 0;
                foreach ($monthArr as $month => $monthName) {
                    $count = $user->steps()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->sum('user_step.calories');

                    $monthCount = 0;
                    $dt         = $request->year . '-' . $month . '-01';
                    $edt        = $request->year . '-' . $month . '-' . date('t', strtotime($dt));

                    while (strtotime($dt) <= strtotime($edt)) {
                        if ($dt > now($timezone)->toDateString()) {
                            break;
                        }
                        $monthCount++;
                        $dt = date("Y-m-d", strtotime("+1 day", strtotime($dt)));
                    }

                    $monthData        = [];
                    $monthData['key'] = ucfirst($monthName);

                    if (!empty($monthCount)) {
                        $monthData['value'] = (double) round(($count / $monthCount), 1);
                    } else {
                        $monthData['value'] = 0;
                    }

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr   = getDates($request->year, $timezone);
                $lastWeight = 0;
                foreach ($datesArr as $key => $date) {
                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                        ->sum('user_step.calories');

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (double) $count;

                    array_push($data, $dateData);
                }
            }

            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'data retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getCalorieHistory(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $userCalorieHistory = $user->steps()
                ->orderBy('user_step.log_date', 'DESC')
                ->limit(7)
                ->get()
                ->toArray();

            $data = array();

            if (!empty($userCalorieHistory)) {
                $userCalorieHistory = array_reverse($userCalorieHistory);

                $userCaloriePreviousData = $user->steps()
                    ->where("user_step.id", "<", $userCalorieHistory[0]['id'])
                    ->orderBy('user_step.log_date', 'DESC')
                    ->first();

                $lastCalorie = 0.0;
                if (!empty($userCaloriePreviousData)) {
                    $lastCalorie = $userCaloriePreviousData->calories;
                } elseif (!empty($this->goal) && !empty($this->goal->calories)) {
                    $lastCalorie = $this->goal->calories;
                }

                foreach ($userCalorieHistory as $key => $value) {
                    $data[] = [
                        "id"        => $value['id'],
                        "date"      => Carbon::parse($value['log_date'], config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
                        "deviation" => ($lastCalorie == 0) ? 0.0 : ($value['calories'] - $lastCalorie),
                        "calorie"   => $value['calories'],
                    ];

                    $lastCalorie = $value['calories'];
                }
                $data = array_reverse($data);
            }
            if (!empty($data)) {
                return $this->successResponse(['data' => $data], 'calories history retrived successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
