<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Controllers\API\V40\MoveController as v40MoveController;
use App\Http\Requests\Api\V1\StepRequest;
use App\Jobs\SyncStepsJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ServesApiTrait;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Requests\Api\V1\GetStepRequest;
use Illuminate\Http\Request;
use App\Models\UserExercise;
use App\Models\Exercise;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Http\Requests\Api\V1\SyncExerciseRequest;

class MoveController extends v40MoveController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSteps(GetStepRequest $request)
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
                foreach ($totalWeeks as $week => $dates) {
                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->sum($request->type);

                    $weekData          = [];
                    $weekData['key']   = $week;
                    $weekData['value'] = (int) $count;

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    $count = $user->steps()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->sum($request->type);

                    $monthData          = [];
                    $monthData['key']   = ucfirst($monthName);
                    $monthData['value'] = (int) $count;

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr = getDates($request->year, $timezone);
                foreach ($datesArr as $key => $date) {
                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                        ->sum($request->type);

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (int) $count;

                    array_push($data, $dateData);
                }
            }

            $extraData          = [];
            $extraData['total'] = (int) $user->steps()
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', now($user->timezone)->toDateString())
                ->sum($request->type);

            $distanceGoal = 0;
            if (!empty($user->goal)) {
                $distanceGoal = (($user->goal->steps * 1000) / 1400);
            }

            $extraData['goal'] = ($request->type == 'steps' && !empty($user->goal)) ? $user->goal->steps : round($distanceGoal);

            return $this->successResponse(array_merge(['data' => $data], $extraData), ucfirst($request->type) . ' data retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStepsMeCompany(v1GetStepRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;
            $company     = $user->company()->first();

            $data = [];
            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                foreach ($totalWeeks as $week => $dates) {
                    $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                        ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'))
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->groupBy('user_step.user_id')
                        ->get()->toArray();

                    $companyAverage = (count($companyData) > 0) ? array_sum(array_column($companyData, 'total')) / count($companyData) : 0;

                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->sum($request->type);

                    $userAverage = $count / 7;

                    $weekData            = [];
                    $weekData['key']     = $week;
                    $weekData['me']      = round($userAverage, 1);
                    $weekData['company'] = round($companyAverage, 1);

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                        ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'))
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->groupBy('user_step.user_id')
                        ->get()->toArray();

                    $companyAverage = (count($companyData) > 0) ? array_sum(array_column($companyData, 'total')) / count($companyData) : 0;

                    $count = $user->steps()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->sum($request->type);

                    $userAverage = $count / count($monthArr);

                    $monthData            = [];
                    $monthData['key']     = ucfirst($monthName);
                    $monthData['me']      = round($userAverage, 1);
                    $monthData['company'] = round($companyAverage, 1);

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr = getDates($request->year, $timezone);

                $startDate   = $request->year . '-01-' . '01';
                $endDate     = $request->year . '-12-' . '31';
                $currentYear = Carbon::now($timezone)->format('y');

                if ($currentYear == $request->year) {
                    $date    = Carbon::now($timezone)->format('d');
                    $month   = Carbon::now($timezone)->format('m');
                    $endDate = $request->year . '-' . $month . '-' . $date;
                }

                $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                    ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'), \DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}')) as ct_log_date"), \DB::raw('SUM(user_step.steps)/count(DISTINCT user_team.id) as sync_users'))
                    ->whereBetween(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), [$startDate, $endDate])
                    ->groupBy('ct_log_date')
                    ->get()->pluck('sync_users', 'ct_log_date')->toArray();

                $getUsersCount = $user->steps()
                    ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'), \DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}')) as ct_log_date"))
                    ->whereBetween(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), [$startDate, $endDate])
                    ->groupBy('ct_log_date')
                    ->get()->pluck('total', 'ct_log_date')->toArray();

                foreach ($datesArr as $key => $date) {
                    $companyAverage = isset($companyData[$date]) ? $companyData[$date] : 0;
                    $count          = isset($getUsersCount[$date]) ? $getUsersCount[$date] : 0;

                    $dateData            = [];
                    $dateData['key']     = date("d M'y", strtotime($date));
                    $dateData['me']      = (int) $count;
                    $dateData['company'] = round((int)$companyAverage, 1);

                    array_push($data, $dateData);
                }

                array_multisort($datesArr, SORT_ASC, $data);
            }

            return $this->successResponse(['data' => $data], 'Company v/s me ' . $request->type . ' data retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExerciseHistory(GetExerciseHistoryRequest $request)
    {
        try {
            $user = $this->user();

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $start = $request->start_date;
            $end   = $request->end_date;

            $page = $request->get('page') ?: 0;

            $exerciseRecords = $user->exercises()
                ->where(function ($q) use ($start, $end, $appTimezone, $timezone) {
                    $q->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$timezone}')"), '>=', $start)
                        ->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$timezone}')"), '<=', $end);
                })
            /*->where(function ($q) use ($start, $end, $appTimezone, $timezone) {
            $q->whereDate(\DB::raw("CONVERT_TZ(user_exercise.end_date, '{$appTimezone}', '{$timezone}')"), '>=', $start)
            ->whereDate(\DB::raw("CONVERT_TZ(user_exercise.end_date, '{$appTimezone}', '{$timezone}')"), '<=', $end);
            })*/
                ->whereNull('user_exercise.deleted_at')
                ->orderByDesc('user_exercise.start_date')
                ->orderByDesc('user_exercise.id');

            if ($page == 0) {
                $exerciseRecords = $exerciseRecords->get();
            } else {
                $exerciseRecords = $exerciseRecords->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            return $this->successResponse(
                ($exerciseRecords->count() > 0) ? new UserExerciseCollection($exerciseRecords, $page) : ['data' => []],
                ($exerciseRecords->count() > 0) ? 'Exercise List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncExercise(SyncExerciseRequest $request)
    {
        try {
            // \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

            $startDate = $request->startDate ?: "";

            if (empty($startDate)) {
                return $this->invalidResponse([], "Start date is required to sync exercise.");
            }

            $startDate = Carbon::parse($startDate, $timezone)->setTime(0, 0, 0);
            $endDate   = now($timezone);

            $daysRange = \createDateRange($startDate, $endDate);

            $allDates = [];
            foreach ($daysRange as $key => $day) {
                $allDates[] = $day->toDateString();
            }

            $data = $request->all();

            if (empty($data) && count($data) == 0) {
                $trackerToCheck = (!empty($request->headers->get('X-User-Tracker'))) ? ($request->headers->get('X-User-Tracker')) : "";

                if (!empty($trackerToCheck)) {
                    // delete user exercises for those dates on which we have not received data from tracker
                    \DB::table('user_exercise')
                        ->whereIn(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$user->timezone}'))"), $allDates)
                        ->where('user_exercise.user_id', $user->getKey())
                    //->where('user_exercise.tracker', $trackerToCheck)
                        ->where('user_exercise.exercise_key', 'NOT LIKE', '%ZevoLife_%')
                        ->whereNull('user_exercise.deleted_at')
                        ->delete();
                }
            }

            usort($data, function ($a, $b) {
                return strtotime($a['startAt']) <=> strtotime($b['startAt']);
            });

            $givenDatesData = [];
            // remove all records for the tracker for exercises date pair
            foreach ($data as $iteration => $item) {
                $startAtInUserTimeZone = Carbon::parse($item['startAt'], $timezone);
                $givenDatesData[]      = $startAtInUserTimeZone->toDateString();

                // delete user exercises
                \DB::table('user_exercise')
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$user->timezone}'))"), $startAtInUserTimeZone->toDateString())
                    ->where('user_exercise.user_id', $user->getKey())
                //->where('user_exercise.tracker', $item['tracker'])
                    ->where('user_exercise.exercise_key', 'NOT LIKE', '%ZevoLife_%')
                    ->whereNull('user_exercise.deleted_at')
                    ->delete();
            }

            if (!empty($givenDatesData) && !empty($allDates)) {
                $DatesToDeleteData = array_diff($allDates, $givenDatesData);

                if (!empty($DatesToDeleteData)) {
                    // delete user exercises for those dates on which we have not received data from tracker
                    \DB::table('user_exercise')
                        ->whereIn(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$user->timezone}'))"), $DatesToDeleteData)
                        ->where('user_exercise.user_id', $user->getKey())
                    //->where('user_exercise.tracker', $item['tracker'])
                        ->where('user_exercise.exercise_key', 'NOT LIKE', '%ZevoLife_%')
                        ->whereNull('user_exercise.deleted_at')
                        ->delete();
                }
            }

            // sync exercise if mapping found for requested tracker exercise
            foreach ($data as $iteration => $item) {
                if (0 > (int) $item['duration'] && 0 > (int) $item['distance'] && 0 > (int) $item['calories']) {
                    continue;
                }

                if (!empty($item['exerciseName']) && !empty($item['tracker'])) {
                    $masterExercise = Exercise::join('exercise_mapping', 'exercises.id', '=', 'exercise_mapping.exercise_id')
                        ->join('tracker_exercises', 'tracker_exercises.id', '=', 'exercise_mapping.tracker_exercise_id')
                        ->select('exercises.*')
                        ->where('tracker_exercises.tracker', $item['tracker'])
                        ->where(function ($q) use ($item) {
                            $q->where('tracker_exercises.name', $item['exerciseName'])->orWhere('tracker_exercises.key', $item['exerciseName']);
                        })
                        ->first();

                    if (!empty($masterExercise)) {
                        // create date instances in {$appTimezone} timezone
                        $startAt = Carbon::parse($item['startAt'], $timezone)->setTimezone($appTimezone);
                        $endAt   = $startAt->copy()->addSeconds((int) $item['duration']);

                        // ignore future dates activities
                        if ($startAt->toDateString() > now()->toDateString()) {
                            continue;
                        }

                        // delete user exercises
                        $exerciseKeyCount = \DB::table('user_exercise')
                            ->where('user_exercise.user_id', $user->getKey())
                            ->where('user_exercise.tracker', $item['tracker'])
                            ->where('user_exercise.exercise_key', $item['exerciseKey'])
                            ->get();

                        if ($exerciseKeyCount->count() == 0) {
                            // add steps into user account
                            $user->exercises()
                                ->attach(
                                    $masterExercise,
                                    $exerciseData = [
                                        'exercise_key' => $item['exerciseKey'],
                                        'calories'     => $item['calories'],
                                        'distance'     => ($masterExercise->type == 'minutes') ? 0 : $item['distance'],
                                        'duration'     => $item['duration'],
                                        'start_date'   => $startAt->toDateTimeString(),
                                        'end_date'     => $endAt->toDateTimeString(),
                                        'tracker'      => $item['tracker'],
                                    ]
                                );

                            // dispatch job to award general badge to user
                            $this->dispatch(new AwardGeneralBadgeToUser($user, 'exercises', $startAt->toDateTimeString()));
                        }
                    }
                }
            }

            // update last synced exercise datetime for user
            $exercise_last_sync_date_time = now()->toDateTimeString();

            //        \DB::commit();

            $user->update(['exercise_last_sync_date_time' => $exercise_last_sync_date_time]);

            $returnData['exerciseLastSyncDateTime'] = (!empty($user->exercise_last_sync_date_time)) ? Carbon::parse($user->exercise_last_sync_date_time, $appTimezone)->setTimezone($user->timezone)->toAtomString() : "";

            // dispatch job to awarg badge to user for running challenge
            // $this->dispatch(new AwardChallengeBadgeToUser($user));

            return $this->successResponse(['data' => $returnData], 'Exercises synced successfully.');
        } catch (\Exception $e) {
            //        \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Sync steps data of logged-in user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSteps(StepRequest $request)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();
            
            // app timezone and user timezone
            $appTimezone = config('app.timezone');

            $data = $request->all();

            usort($data, function ($a, $b) {
                return strtotime($a['date']) <=> strtotime($b['date']);
            });

            // dispatch job to sync steps
            $this->dispatch(new SyncStepsJob($user, $data));

            // update last sync date time for steps in current user.
            $step_last_sync_date_time = now()->toDateTimeString();

            $user->update(['step_last_sync_date_time' => $step_last_sync_date_time]);

            $returnData['stepLastSyncDateTime'] = (!empty($user->step_last_sync_date_time)) ? Carbon::parse($user->step_last_sync_date_time, $appTimezone)->setTimezone($user->timezone)->toAtomString() : "";

            \DB::commit();

            return $this->successResponse(['data' => $returnData], 'Steps synced successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}