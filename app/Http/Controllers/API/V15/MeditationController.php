<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V15;

use App\Http\Collections\V15\CategoryWiseTrackCollection;
use App\Http\Controllers\API\V14\MeditationController as v14MeditationController;
use App\Http\Resources\V15\CategoryWiseTrackResource;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Models\MeditationTrack;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v14MeditationController
{
    /**
     *  API to fetch track data by Meditation category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackList(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            if ($request->meditationSubCategory > 0) {
                $subcatData = SubCategory::find($request->meditationSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            if ($request->meditationSubCategory <= 0) {
                $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id');

                if ($request->meditationSubCategory == 0) {
                    $trackIds->where("user_meditation_track_logs.user_id", $user->id)->where(["favourited" => 1, "sub_categories.status" => 1]);
                } else {
                    $trackIds->where("sub_categories.status", 1)
                        ->groupBy('meditation_tracks.id');
                }

                $trackIds = $trackIds->pluck("meditation_tracks.id")
                    ->toArray();

                if (empty($trackIds)) {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }

                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->select("meditation_tracks.id", "meditation_tracks.sub_category_id", "meditation_tracks.coach_id", "meditation_tracks.title", "meditation_tracks.is_premium", "meditation_tracks.duration", "meditation_tracks.type", "meditation_tracks.audio_type", "meditation_tracks.view_count", DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"), DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"), DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"), DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened"))
                    ->orderBy('count_user_listened', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $categoryWiseTrackData = MeditationTrack::where("sub_category_id", $request->meditationSubCategory)
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                    ->where(["sub_categories.status" => 1])
                    ->select(
                        "meditation_tracks.id",
                        "meditation_tracks.sub_category_id",
                        "meditation_tracks.coach_id",
                        "meditation_tracks.title",
                        "meditation_tracks.is_premium",
                        "meditation_tracks.duration",
                        "meditation_tracks.type",
                        "meditation_tracks.audio_type",
                        "meditation_tracks.view_count",
                        DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                        DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes")
                    );

                $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if ($categoryWiseTrackData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
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
                // if (!is_null($company)) {
                //     $meditation->rewardPortalPointsToUser($user, $company, 'meditation');
                // }

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
}
