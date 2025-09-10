<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V40;

use App\Http\Controllers\API\V38\MoveController as v38MoveController;
use App\Http\Requests\Api\V1\StepRequest;
use App\Jobs\SyncStepsJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MoveController extends v38MoveController
{
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
