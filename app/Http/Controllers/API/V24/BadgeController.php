<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V24;

use App\Http\Collections\V23\BadgeListCollection;
use App\Http\Collections\V23\BadgeListDetailsCollection;
use App\Http\Controllers\API\V23\BadgeController as v23BadgeController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends v23BadgeController
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
            $checkAccess = getCompanyPlanAccess($user, 'my-challenges');
            // Get General and daily badges
            $data['generalBadge'] = Badge::whereIn('type', ['general', 'daily'])
                ->select(
                    'id',
                    'title',
                    'type',
                    DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND STATUS = 'Active') AS assignCount")
                )->orderBy('assignCount', 'DESC')->get();

            // Get challenge and ongoing badges
            $challengeBadge = Badge::where(function ($query) use ($checkAccess) {
                $query->where('type', 'challenge')
                    ->where('is_default', 1);

                if (!$checkAccess) {
                    $query->where('challenge_type_slug', 'personal');
                }
            })->orWhere('type', 'ongoing')
                ->select(
                    'id',
                    'title',
                    'type',
                    DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND STATUS = 'Active') AS assignCount")
                )
                ->orderBy('assignCount', 'DESC')
                ->get();

            $data['challengeBadge'] = $challengeBadge;

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
}
