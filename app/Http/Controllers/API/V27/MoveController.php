<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Controllers\API\V26\MoveController as v26MoveController;
use App\Http\Requests\Api\V1\TrackExerciseRequest;
use App\Models\Exercise;
use App\Models\UserExercise;
use Carbon\Carbon;
use App\Jobs\AwardGeneralBadgeToUser;
use Illuminate\Http\JsonResponse;

class MoveController extends v26MoveController
{
    /**
     * Track the exercise
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
                
                //Check if exercise is added manual or by tracker from exercise key
                $isManual = 0;
                if (!empty($data['exerciseKey'])) {
                    if (strpos($data['exerciseKey'], "ZevoLife") !== false) {
                        $isManual = 1;
                    }
                }
                
                $userExercise = UserExercise::create([
                    'user_id'      => $user->id,
                    'exercise_id'  => $exercise->id,
                    'exercise_key' => $data['exerciseKey'],
                    'is_manual'    => $isManual,
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
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'exercises', Carbon::parse($userExercise->start_date)->toDateTimeString()));
                }
            }

            return $this->successResponse(['data' => []], trans('api_messages.exercise.save'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
