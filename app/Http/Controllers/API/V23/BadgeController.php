<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V23;

use App\Http\Collections\V23\BadgeListCollection;
use App\Http\Collections\V23\BadgeListDetailsCollection;
use App\Http\Controllers\API\V22\BadgeController as v22BadgeController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends v22BadgeController
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
            $user = $this->user();

            // Get General and daily badges
            $data['generalBadge'] = Badge::whereIn('type', ['general', 'daily'])
                ->select(
                    'id',
                    'title',
                    'type',
                    DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND STATUS = 'Active') AS assignCount")
                )->orderBy('assignCount', 'DESC')->get();

            // Get challenge and ongoing badges
            $data['challengeBadge'] = Badge::where(function ($query) {
                $query->where('type', 'challenge')
                    ->where('is_default', 1);
            })->orWhere('type', 'ongoing')
                ->select(
                    'id',
                    'title',
                    'type',
                    DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND STATUS = 'Active') AS assignCount")
                )
                ->orderBy('assignCount', 'DESC')
                ->get();

            // Get masterclass badges
            $data['masterclassBadge'] = Badge::whereIn('type', ['masterclass'])->where('is_default', true)->select('id', 'title', 'type')->get();

            // Total Achievement Badge Counts
            $data['totalAchievement'] = Badge::leftJoin("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->select(
                    "badges.id"
                )
                ->where("badge_user.status", "Active")
                ->where("badge_user.user_id", $user->id)
                ->whereNull("badge_user.expired_at")
                ->orderBy("badge_user.created_at", "DESC")
                ->count();

            // Collect required data and return response
            return $this->successResponse(new BadgeListCollection($data), 'Badge listed successfully');
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
    public function badgelistdetails(Request $request, $type, badge $badge)
    {
        try {
            $user          = $this->user();
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
                );

            if ($type != 'masterclass') {
                $userBadgeData->where('badge_user.badge_id', $badge->id);
            } else {
                $userBadgeData->where('badges.type', 'masterclass');
            }

            $userBadgeData = $userBadgeData->where('badge_user.user_id', $user->id)
                ->orderBy('badgeAwardedOn', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if (empty($userBadgeData)) {
                return $this->notFoundResponse('Badge details not found');
            }

            return $this->successResponse(
                (($userBadgeData->count() > 0) ? new BadgeListDetailsCollection($userBadgeData) : ['data' => []]),
                (($userBadgeData->count() > 0) ? 'Badge detail retrieved successfully.' : 'No results')
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
