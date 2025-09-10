<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V22;

use App\Http\Controllers\API\V17\BadgeController as v17BadgeController;
use App\Http\Resources\V22\BadgeDetailResource;
use App\Http\Resources\V22\UserBadgeDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends v17BadgeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Badges acheivements screen in app
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function active(Request $request)
    {
        try {
            $user          = $this->user();
            $userBadgeData = Badge::leftJoin("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->select(
                    "badges.id",
                    "badges.title",
                    "badges.type",
                    'badge_user.id as badgeUserId',
                    "badge_user.user_id",
                    "badge_user.status",
                    "badge_user.expired_at as badgeExpiredAt",
                    "badge_user.model_id as badgeModelId",
                    "badge_user.model_name as badgeModelName",
                    "badge_user.created_at as badgeAwardedOn"
                )
                ->where("badge_user.status", "Active")
                ->where("badge_user.user_id", $user->id)
                ->whereNull("badge_user.expired_at")
                ->orderBy("badge_user.created_at", "DESC")
                ->get();

            $badges = [];
            foreach ($userBadgeData as $key => $value) {
                $badges[] = new UserBadgeDetailsResource($value);
            }

            $dataArray = [
                'data'             => $badges,
                'totalAchievement' => $userBadgeData->count(),
            ];

            return $this->successResponse($dataArray, 'Achievements list retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get badge details by badgeUserId
     *
     * @param Request $request, $badgeUserId
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, $badgeUserId)
    {
        try {
            $userBadgeData = Badge::leftJoin("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->leftJoin("users", "badge_user.user_id", "=", "users.id")
                ->select(
                    "badges.id",
                    "badges.title",
                    "badges.type",
                    "badges.challenge_type_slug",
                    'badges.description',
                    "badges.model_id as modelId",
                    "badges.model_name as modelName",
                    'badge_user.id as badgeUserId',
                    "badge_user.user_id",
                    "badge_user.status",
                    'badge_user.level',
                    "badge_user.expired_at as badgeExpiredAt",
                    "badge_user.model_id as badgeModelId",
                    "badge_user.model_name as badgeModelName",
                    "badge_user.created_at as badgeAwardedOn",
                    \DB::raw("CONCAT(users.first_name,' ',users.last_name) as achieverName")
                )
                ->where('badge_user.id', $badgeUserId)
                ->first();

            if (empty($userBadgeData)) {
                return $this->notFoundResponse('Badge details not found');
            }

            return $this->successResponse(['data' => new BadgeDetailResource($userBadgeData)], 'Badge detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
