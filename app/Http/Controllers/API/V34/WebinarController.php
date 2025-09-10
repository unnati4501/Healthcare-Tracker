<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V34;

use App\Http\Controllers\API\V33\WebinarController as v33WebinarController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebinarController extends v33WebinarController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Mark webinar as completed
     *
     * @param Request $request, Webinar $webinar
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted(Request $request, Webinar $webinar)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user          = $this->user();
            $company       = $user->company()->first();
            $companyLimits = $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray();
            $appTimezone   = config('app.timezone');
            $timezone      = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now           = now($timezone)->toDateString();

            $webinar->rewardPortalPointsToUser($user, $company, 'webinar');

            UpdatePointContentActivities('webinar', $webinar->id, $user->getKey(), 'completed', true);

            \DB::commit();

            return $this->successResponse([], 'Webinar marked as completed.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to liked unliked Webinar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeWebinar(Request $request, Webinar $webinar)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $extraPoint     = false;
            $message        = trans('api_messages.webinar.liked');
            $pivotExsisting = $webinar
                ->webinarUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('webinar_id', $webinar->getKey())
                ->first();
            $extraPoint = !is_null($webinar->tag_id) ;
            if (!empty($pivotExsisting)) {
                UpdatePointContentActivities('webinar', $webinar->id, $user->id, 'like', false, $extraPoint);
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    RemovePointContentActivities('webinar', $webinar->id, $user->id, 'like');
                    $message = trans('api_messages.webinar.unliked');
                }
            } else {
                UpdatePointContentActivities('webinar', $webinar->id, $user->id, 'like', false, $extraPoint);
                $webinar
                    ->webinarUserLogs()
                    ->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $webinar->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
