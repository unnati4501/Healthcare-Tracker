<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V17;

use App\Http\Controllers\API\V12\MoveController as v12MoveController;
use App\Http\Requests\Api\V1\StepRequest;
use App\Http\Requests\Api\V1\SyncExerciseRequest;
use App\Http\Requests\Api\V1\TrackExerciseRequest;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Models\Exercise;
use App\Models\UserExercise;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MoveController extends v12MoveController
{
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackExercise(TrackExerciseRequest $request, Exercise $exercise)
    {
        try {
            $user        = $this->user();
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $data        = $request->all();

            if (!empty($data['tracker'])) {
                \DB::beginTransaction();
                $userExercise = UserExercise::create([
                    'user_id'      => $user->id,
                    'exercise_id'  => $exercise->id,
                    'exercise_key' => $data['exerciseKey'],
                    'calories'     => $data['calories'],
                    'distance'     => ($exercise->type == 'minutes') ? 0 : $data['distance'],
                    'duration'     => $data['duration'],
                    'start_date'   => Carbon::parse($data['startAt'], $timezone)->setTimezone($appTimezone)->toDateTimeString(),
                    'end_date'     => Carbon::parse($data['endAt'], $timezone)->setTimezone($appTimezone)->toDateTimeString(),
                    'tracker'      => $data['tracker'],
                ]);

                if ($userExercise) {
                    if ($request->hasFile('routeImage')) {
                        $name = $userExercise->getKey() . '_' . \time();
                        $userExercise
                            ->clearMediaCollection('logo')
                            ->addMediaFromRequest('routeImage')
                            ->usingName($request->file('routeImage')->getClientOriginalName())
                            ->usingFileName($name . '.' . $request->file('routeImage')->extension())
                            ->toMediaCollection('logo', config('medialibrary.disk_name'));
                    }
                    \DB::commit();

                    // dispatch job to award general badge to user
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'exercises', $userExercise->start_date->toDateTimeString()));
                }
            }

            return $this->successResponse(['data' => []], trans('api_messages.exercise.save'));
        } catch (\Exception $e) {
            \DB::rollback();
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
            // \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = $request->all();

            usort($data, function ($a, $b) {
                return strtotime($a['date']) <=> strtotime($b['date']);
            });

            foreach ($data as $iteration => $item) {
                if (0 > (int) $item['steps'] && 0 > (int) $item['distance'] && 0 > (int) $item['calories']) {
                    continue;
                }

                $date = Carbon::parse($item['date'], $user->timezone)->setTimezone(config('app.timezone'));

                $stepDateInUserTimeZone = Carbon::parse($item['date'], $user->timezone);

                // remove all records for the tracker for steps date pair
                // delete user steps
                $user->steps()
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), $stepDateInUserTimeZone->toDateString())
                //->where('tracker', $item['tracker'])
                    ->get()->each->delete();

                // add steps into user account
                $userStepObject = $user->steps()->create([
                    'log_date' => $date->toDateTimeString(),
                    'tracker'  => $item['tracker'],
                    'steps'    => (int) $item['steps'],
                    'distance' => (int) $item['distance'],
                    'calories' => (int) $item['calories'],
                ]);

                if (!empty($item['steps']) && !empty($item['distance'])) {
                    // dispatch job to award general badge to user for steps
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'steps', $date->toDateTimeString()));

                    // dispatch job to award general badge to user for distance
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'distance', $date->toDateTimeString()));
                }
            }

            // update last sync date time for steps in current user.
            $step_last_sync_date_time = now()->toDateTimeString();

            $user->update(['step_last_sync_date_time' => $step_last_sync_date_time]);

            $returnData['stepLastSyncDateTime'] = (!empty($user->step_last_sync_date_time)) ? Carbon::parse($user->step_last_sync_date_time, $appTimezone)->setTimezone($user->timezone)->toAtomString() : "";

            // \DB::commit();

            // dispatch job to awarg badge to user for running challenge
            // $this->dispatch(new AwardChallengeBadgeToUser($user));

            return $this->successResponse(['data' => $returnData], 'Steps synced successfully.');
        } catch (\Exception $e) {
            // \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
