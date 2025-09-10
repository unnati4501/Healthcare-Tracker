<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V23;

use App\Http\Controllers\API\V22\MoveController as v22MoveController;
use App\Http\Requests\Api\V1\StepRequest;
use App\Jobs\AwardDailyBadgeToUser;
use App\Jobs\AwardGeneralBadgeToUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Jobs\AwardOngoingChallengeBadgeToUser;

class MoveController extends v22MoveController
{
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

                $from          = $date->toDateString() . ' 00:00:00';
                $to            = $date->toDateString() . ' 23:59:59';
                $userBadgeData = \DB::table('badge_user')
                    ->leftJoin('badges', 'badges.id', '=', 'badge_user.badge_id')
                    ->where('badges.type', 'daily')
                    ->where('badges.is_default', true)
                    ->where("user_id", $user->id)
                    ->whereBetween('date_for', [$from, $to])
                    ->count();

                if (!empty($item['steps']) && $userBadgeData <= 0) {
                    // dispatch job to award Daily badge to user for steps
                    $this->dispatch(new AwardDailyBadgeToUser($user, $date->toDateTimeString()));
                }

                if (!empty($item['steps'])) {
                    // dispatch job to award general badge to user for steps
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'steps', $date->toDateTimeString()));

                    // Dispatch job to award ongoing badge to user for steps
                    $this->dispatch(new AwardOngoingChallengeBadgeToUser($user, 'steps', $date->toDateTimeString()));
                }

                if (!empty($item['distance'])) {
                    // dispatch job to award general badge to user for distance
                    $this->dispatch(new AwardGeneralBadgeToUser($user, 'distance', $date->toDateTimeString()));

                    // Dispatch job to award ongoing badge to user for distance
                    $this->dispatch(new AwardOngoingChallengeBadgeToUser($user, 'distance', $date->toDateTimeString()));
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
