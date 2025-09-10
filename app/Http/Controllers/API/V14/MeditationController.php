<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V14;

use App\Http\Controllers\API\V13\MeditationController as v13MeditationController;
use App\Http\Resources\V11\CategoryWiseTrackResource;
use App\Models\MeditationTrack;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v13MeditationController
{
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackDetails(Request $request, MeditationTrack $meditation)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();
            // Check meditation available with this company or not
            $checkMeditation = $meditation->meditationcompany()->where('company_id', $company->id)->count();

            if ($checkMeditation <= 0) {
                return $this->notFoundResponse('Meditation track not found');
            }

            $categoryWiseTrackData = MeditationTrack::where(["meditation_tracks.id" => $meditation->id, "sub_categories.status" => 1])
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                    $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                        ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                })
                ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                ->first();

            if (!empty($categoryWiseTrackData->id)) {
                if (!is_null($company)) {
                    $meditation->rewardPortalPointsToUser($user, $company, 'meditation');
                }

                return $this->successResponse([
                    "data" => new CategoryWiseTrackResource($categoryWiseTrackData),
                ], 'Meditation Retrieved Successfully');
            } else {
                return $this->notFoundResponse('Meditation not found');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
