<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V32\MeditationController as v32MeditationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Models\MeditationTrack;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v32MeditationController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Mark Meditation as completed
     *
     * @param Request $request, MeditationTrack $meditation
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user                    = $this->user();
            $company                 = $user->company()->first();
            $companyLimits           = $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray();
            $appTimezone             = config('app.timezone');
            $timezone                = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now                     = now($timezone)->toDateString();
            $daily_meditation_limits = ($companyLimits['daily_meditation_limit'] ?? config("zevolifesettings.default_limits.daily_meditation_limit", 10));
            $daily_track_limit       = ($companyLimits['daily_track_limit'] ?? config("zevolifesettings.default_limits.daily_track_limit", 5));
            // $daily_meditation_limits = config('zevolifesettings.daily_meditation_limits');

            // fetch user MeditationTrack data
            $meditation->trackIncompletedUserLogs()->wherePivot('user_id', $user->getKey())->detach();

            // get count of total listened meditation for current day by user
            $totalListenedMeditationCount = $user
                ->completedMeditationTracks()
                ->whereDate(\DB::raw("CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                ->count('user_listened_tracks.meditation_track_id');

            // check user reach maxmimum meditation limit for the day
            if ($totalListenedMeditationCount < $daily_meditation_limits) {
                // get count of perticular meditation for current day listen by the user
                $meditationListenCount = $meditation
                    ->trackListenedUserLogs()
                    ->wherePivot('user_id', $user->getKey())
                    ->whereDate(\DB::raw("CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                    ->count('user_listened_tracks.meditation_track_id');

                // check user reach maxmimum limit for the day for same meditation
                if ($meditationListenCount < $daily_track_limit) {
                    $meditation->trackListenedUserLogs()->attach($user, ['duration_listened' => $meditation->duration]);
                }
            }

            $meditation->rewardPortalPointsToUser($user, $company, 'meditation');

            UpdatePointContentActivities('meditation', $meditation->id, $user->getKey(), 'completed');

            \DB::commit();

            // dispatch job to awarg badge to user for running challenge
            //$this->dispatch(new AwardChallengeBadgeToUser($user));

            // dispatch job to award general badge to user
            $this->dispatch(new AwardGeneralBadgeToUser($user, 'meditations', now($appTimezone)->toDateTimeString()));

            return $this->successResponse([], 'Meditation marked as completed.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to liked unliked MeditationTrack by MeditationTrack
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeTrack(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.mediation.liked');
            $pivotExsisting = $meditation
                ->trackUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('meditation_track_id', $meditation->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    RemovePointContentActivities('meditation', $meditation->id, $user->id, 'like');
                    $message = trans('api_messages.mediation.unliked');
                } else {
                    UpdatePointContentActivities('meditation', $meditation->id, $user->id, 'like');
                }
            } else {
                UpdatePointContentActivities('meditation', $meditation->id, $user->id, 'like');

                $meditation
                    ->trackUserLogs()
                    ->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $meditation->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
