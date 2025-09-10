<?php declare (strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Models\Exercise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TrackerLogs;

class GarminController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Sync steps and exercises from garmin endpoint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pingCallback(Request $request)
    {
        if ($request->dailies) {
            return $this->syncSteps($request->dailies);
        }

        if ($request->activities) {
            return $this->syncExercise($request->activities);
        }

        return $this->successResponse(['data' => []]);
    }

    /**
     * Sync steps data of garmin user
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSteps($data)
    {
        try {
            foreach ($data as $value) {
                if (!isset($value['userAccessToken']) ||
                    !isset($value['steps']) ||
                    !isset($value['distanceInMeters']) ||
                    !isset($value['calendarDate'])
                ) {
                    continue;
                }

                $userToken = $value['userAccessToken'];
                $user      = User::with('devices', 'steps')
                    ->select('users.id', 'users.timezone')
                    ->whereHas('devices', function ($query) use ($userToken) {
                        return $query->where('user_token', $userToken);
                    })
                    ->first();

                if (empty($user)) {
                    continue;
                }

                // app timezone and user timezone
                $appTimezone  = config('app.timezone');
                $userTimezone = $user->timezone ?? $appTimezone;

                if (Carbon::parse($value['calendarDate'], $userTimezone)->setTimezone($appTimezone)->toDateString() == Carbon::today()->setTimezone($appTimezone)->toDateString()) {
                    $date = Carbon::now()->setTimezone($appTimezone)->toDateTimeString();
                } else {
                    $date = Carbon::parse($value['calendarDate'], $userTimezone)->setTimezone($appTimezone)->addHours(23)->addMinutes(59)->addSeconds(59)->toDateTimeString();
                }

                $stepDateInUserTimeZone = Carbon::parse($value['calendarDate'], $userTimezone);


                // Garmin tracker log store when webhook call ( Check For Day light saving issue )
                TrackerLogs::create([
                    'user_id'      => $user->getKey(),
                    'os'           => 'web',
                    'tracker_name' => 'garmin',
                    'request_url'  => null,
                    'request_data' => null,
                    'fetched_data' => (json_encode($value) ?? null),
                ]);

                // Delete user steps
                \DB::table('user_step')
                ->where('user_step.user_id', $user->getKey())
                ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[$appTimezone,$user->timezone,$stepDateInUserTimeZone->toDateString()])
                ->delete();

                $stepData = $value['steps'] != 0 ? $value['steps'] : ($value['distanceInMeters'] != 0 ? (((int) $value['distanceInMeters'] / 1000) * 1400) : 0);

                // Add steps into user account
                $user->steps()
                    ->create(
                        [
                            'log_date' => $date,
                            'tracker'  => 'garmin',
                            'steps'    => (int) $stepData,
                            'distance' => (int) $value['distanceInMeters'],
                            'calories' => (int) (isset($value['activeKilocalories']) ? $value['activeKilocalories'] : 0),
                        ]
                    );

                // dispatch job to award general badge to user for steps
                $this->dispatch(new AwardGeneralBadgeToUser($user, 'steps', $date));

                // dispatch job to award general badge to user for distance
                $this->dispatch(new AwardGeneralBadgeToUser($user, 'distance', $date));

                // update last sync date time for steps in current user.
                $user->update(['step_last_sync_date_time' => now()->toDateTimeString()]);
                
            }

            return $this->successResponse(['data' => []], 'Steps synced successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse($e->getMessage());
        }
    }

    /**
     * Sync exercise data of garmin user
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncExercise($data)
    {
        try {
            foreach ($data as $value) {
                if (!isset($value['userAccessToken']) ||
                    !isset($value['summaryId']) ||
                    !isset($value['activityType']) ||
                    !isset($value['startTimeInSeconds']) ||
                    !isset($value['durationInSeconds']) ||
                    !isset($value['distanceInMeters'])
                ) {
                    continue;
                }

                $userToken = $value['userAccessToken'];
                $user      = User::with('devices', 'exercises')
                    ->select('users.id', 'users.timezone')
                    ->whereHas('devices', function ($query) use ($userToken) {
                        return $query->where('user_token', $userToken);
                    })
                    ->first();

                if (empty($user)) {
                    continue;
                }

                // app timezone and user timezone
                $appTimezone  = config('app.timezone');
                $startDate = Carbon::parse(($value['startTimeInSeconds']), $appTimezone);
                $endDate   = $startDate->copy()->addSeconds((int) $value['durationInSeconds']);

                $userExercise = \DB::table('user_exercise')
                    ->where('user_exercise.user_id', $user->getKey())
                    ->where('user_exercise.exercise_key', $value['summaryId'])
                    ->first();

                if (!empty($userExercise)) {
                    if (is_null($userExercise->deleted_at)) {
                        \DB::table('user_exercise')
                            ->where('user_exercise.user_id', $user->getKey())
                            ->where('user_exercise.exercise_key', $value['summaryId'])
                            ->whereNull('user_exercise.deleted_at')
                            ->delete();
                    } else {
                        continue;
                    }
                }

                if (!empty($value['activityType'])) {
                    $masterExercise = Exercise::join('exercise_mapping', 'exercises.id', '=', 'exercise_mapping.exercise_id')
                        ->join('tracker_exercises', 'tracker_exercises.id', '=', 'exercise_mapping.tracker_exercise_id')
                        ->select('exercises.*')
                        ->where('tracker_exercises.tracker', 'garmin')
                        ->where(function ($q) use ($value) {
                            $q->where('tracker_exercises.name', $value['activityType'])
                                ->orWhere('tracker_exercises.key', $value['activityType']);
                        })
                        ->first();

                    if (!empty($masterExercise)) {
                        // ignore future dates activities
                        if ($startDate->toDateTimeString() > now()->toDateTimeString()) {
                            continue;
                        }

                        $user->exercises()
                            ->attach(
                                $masterExercise,
                                [
                                    'exercise_key' => $value['summaryId'],
                                    'calories'     => (int) (isset($value['activeKilocalories']) ? $value['activeKilocalories'] : 0),
                                    'distance'     => ($masterExercise->type == 'minutes') ? 0 : $value['distanceInMeters'],
                                    'duration'     => $value['durationInSeconds'],
                                    'start_date'   => $startDate->toDateTimeString(),
                                    'end_date'     => $endDate->toDateTimeString(),
                                    'tracker'      => 'garmin',
                                ]
                            );

                        // dispatch job to award general badge to user
                        $this->dispatch(new AwardGeneralBadgeToUser($user, 'exercises', $startDate->toDateTimeString()));
                    }

                    // update last synced exercise datetime for user
                    $user->update(['exercise_last_sync_date_time' => now()->toDateTimeString()]);
                }
            }

            return $this->successResponse(['data' => []], 'Exercise synced successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse($e->getMessage());
        }
    }
}
