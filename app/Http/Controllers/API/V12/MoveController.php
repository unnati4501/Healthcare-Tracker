<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V12;

use App\Http\Controllers\API\V11\MoveController as v11MoveController;
use App\Http\Requests\Api\V12\UpdateTrackedExerciseRequest;
use App\Http\Resources\V12\UpdatedUserExerciseResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\UserExercise;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MoveController extends v11MoveController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTrackExercise(UserExercise $userExercise, UpdateTrackedExerciseRequest $request)
    {
        try {
            \DB::beginTransaction();

            $user        = $this->user();
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $startAt     = Carbon::parse($request->startAt, $timezone)->setTimezone($appTimezone);
            $endAt       = Carbon::parse($request->endAt, $timezone)->setTimezone($appTimezone);

            $userExercise->update([
                'calories'   => $request->calories,
                'distance'   => $request->distance,
                'duration'   => $request->duration,
                'start_date' => $startAt->toDateTimeString(),
                'end_date'   => $endAt->toDateTimeString(),
            ]);

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

            return $this->successResponse(
                UpdatedUserExerciseResource::collection([$userExercise])->first(),
                trans('api_messages.exercise.updated')
            );
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
