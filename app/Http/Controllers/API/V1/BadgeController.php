<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\UserBadgeDetailsListCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserBadgeDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function active(Request $request)
    {
        try {
            $user          = $this->user();
            $userBadgeData = Badge::select("badges.*", "badge_user.user_id", "badge_user.status", "badge_user.expired_at as badgeExpiredAt", "badge_user.model_id as badgeModelId", "badge_user.model_name as badgeModelName", "badge_user.created_at as badgeAwardedOn")
                ->join("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->where("badge_user.status", "Active")
                ->where("badge_user.user_id", $user->id)
                ->whereNull("badge_user.expired_at")
                ->get();
            $dataArray         = array();
            $dataArray['data'] = array();
            foreach ($userBadgeData as $key => $value) {
                $dataArray['data'][] = new UserBadgeDetailsResource($value);
            }
            $dataArray['totalAchievement'] = $userBadgeData->count();

            return $this->successResponse($dataArray, 'Achievements list retrieved successfully.');
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
    public function timeline(Request $request)
    {
        try {
            $user          = $this->user();
            $userBadgeData = Badge::select("badges.*", "badge_user.user_id", "badge_user.status", "badge_user.expired_at as badgeExpiredAt", "badge_user.model_id as badgeModelId", "badge_user.model_name as badgeModelName", "badge_user.created_at as badgeAwardedOn")
                ->join("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->where("badge_user.user_id", $user->id)
                ->orderBy("badge_user.updated_at", "DESC")
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($userBadgeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new UserBadgeDetailsListCollection($userBadgeData), 'Achievements list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
