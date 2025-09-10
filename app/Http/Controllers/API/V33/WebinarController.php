<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V32\WebinarController as v32WebinarController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebinarController extends v32WebinarController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * API to liked unliked Webinar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeWebinar(Request $request, Webinar $webinar)
    {
        try {
            \DB::beginTransaction();
            $user = $this->user();

            $message        = trans('api_messages.webinar.liked');
            $pivotExsisting = $webinar
                ->webinarUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('webinar_id', $webinar->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                UpdatePointContentActivities('webinar', $webinar->id, $user->id, 'like');
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    RemovePointContentActivities('webinar', $webinar->id, $user->id, 'like');
                    $message = trans('api_messages.webinar.unliked');
                }
            } else {
                UpdatePointContentActivities('webinar', $webinar->id, $user->id, 'like');
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
