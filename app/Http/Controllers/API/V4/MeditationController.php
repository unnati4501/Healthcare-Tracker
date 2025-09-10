<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V4;

use App\Http\Collections\V4\CategoryWiseTrackCollection;
use App\Http\Controllers\API\V1\MeditationController as v1MeditationController;
use App\Http\Resources\V4\CategoryWiseTrackResource;
use App\Models\MeditationTrack;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v1MeditationController
{
    /**
     * API to fetch meditation category list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

            if ($favouritedCount > 0) {
                $records = array(0 => "Favourite") + SubCategory::where(['category_id' => 4, 'status' => 1])->get()->pluck("name", "id")->toArray();
            } else {
                $records = SubCategory::where(['category_id' => 4, 'status' => 1])->get()->pluck("name", "id")->toArray() + array(0 => "Favourite");
            }

            $new_array = array_map(function ($id, $name) {
                return array(
                    'id'   => $id,
                    'name' => ucfirst($name),
                );
            }, array_keys($records), $records);

            return $this->successResponse(['data' => $new_array], 'Meditation Categories Retrieved Successfully.');
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
            $user = $this->user();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
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

    /**
     *  API to fetch track data by Meditation category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackList(Request $request)
    {
        try {
            $user = $this->user();
            if ($request->meditationSubCategory != 0) {
                $subcatData = SubCategory::find($request->meditationSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }
            if ($request->meditationSubCategory == 0) {
                $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
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
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"));

                $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $categoryWiseTrackData = MeditationTrack::where("sub_category_id", $request->meditationSubCategory)
                    ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                    ->where(["sub_categories.status" => 1])
                    ->select(
                        "meditation_tracks.*",
                        "user_incompleted_tracks.duration_listened",
                        DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes")
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
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachMeditations(Request $request, User $user)
    {
        try {
            // logged-in user
            $logginUser = $this->user();

            $categoryWiseTrackData = MeditationTrack::where(["meditation_tracks.coach_id" => $user->id, "sub_categories.status" => 1])
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                    $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                        ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                })
                ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"));

            $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                ->groupBy('meditation_tracks.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseTrackData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
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
}
