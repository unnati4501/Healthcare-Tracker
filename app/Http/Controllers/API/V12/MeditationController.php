<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V12;

use App\Http\Collections\V11\CategoryWiseTrackCollection;
use App\Http\Controllers\API\V11\MeditationController as v11MeditationController;
use App\Http\Resources\V11\CategoryWiseTrackResource;
use App\Models\MeditationTrack;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v11MeditationController
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
            if ($request->meditationSubCategory != 0) {
                $subcatData = SubCategory::find($request->meditationSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            if ($request->meditationSubCategory == 0) {
                $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                    ->where("user_meditation_track_logs.user_id", $user->id)
                    ->where(["favourited" => 1, "sub_categories.status" => 1])
                    ->pluck("meditation_tracks.id")
                    ->toArray();

                if (empty($trackIds)) {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }

                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->select("meditation_tracks.id", "meditation_tracks.sub_category_id", "meditation_tracks.coach_id", "meditation_tracks.title", "meditation_tracks.is_premium", "meditation_tracks.duration", "meditation_tracks.type", "meditation_tracks.audio_type", "meditation_tracks.view_count", DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"), DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"), DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"))
                    ->orderBy('favourited_at', 'DESC')
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
            $logginUser = $this->user();
            $company    = $logginUser->company()->first();
            // Check meditation available with this company or not
            $checkMeditation = $meditation->meditationcompany()->where('company_id', $company->id)->count();

            if ($checkMeditation <= 0) {
                return $this->notFoundResponse('Meditation track not found');
            }

            $categoryWiseTrackData = MeditationTrack::where(["meditation_tracks.id" => $meditation->id, "sub_categories.status" => 1])
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->leftJoin("user_incompleted_tracks", function ($join) use ($logginUser) {
                    $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                        ->where('user_incompleted_tracks.user_id', '=', $logginUser->getKey());
                })
                ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                ->first();

            if (!empty($categoryWiseTrackData->id)) {
                // get coach course details data with json response
                $data = array("data" => new CategoryWiseTrackResource($categoryWiseTrackData));
                return $this->successResponse($data, 'Meditation Retrieved Successfully');
            } else {
                return $this->notFoundResponse('Meditation not found');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch saved meditation data by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->join('meditation_tracks_company', function ($join) use ($company) {
                    $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                        ->where('meditation_tracks_company.company_id', $company->id);
                })
                ->where("user_meditation_track_logs.user_id", $user->id)
                ->where(["user_meditation_track_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("meditation_tracks.id")
                ->toArray();

            if (!empty($trackIds)) {
                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_meditation_track_logs', function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                            ->where('user_meditation_track_logs.user_id', '=', $user->getKey());
                    })
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                    ->orderBy('user_meditation_track_logs.saved_at', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if ($categoryWiseTrackData->count() > 0) {
                    // collect required data and return response
                    return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
