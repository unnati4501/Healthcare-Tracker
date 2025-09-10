<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Collections\V6\CategoryWiseTrackCollection;
use App\Http\Controllers\API\V6\MeditationController as v6MeditationController;
use App\Models\MeditationTrack;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v6MeditationController
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
                $records = array(0 => "My ❤️") + SubCategory::where(['category_id' => 4, 'status' => 1])->get()->pluck("name", "id")->toArray();
            } else {
                $records = SubCategory::where(['category_id' => 4, 'status' => 1])->get()->pluck("name", "id")->toArray() + array(0 => "My ❤️");
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
                    ->leftJoin('user_meditation_track_logs', function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                            ->where('user_meditation_track_logs.user_id', '=', $user->getKey());
                    })
                    // ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"), 'favourited_at')
                    ->orderBy('user_meditation_track_logs.favourited_at', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
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
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to favorited unfavourited MeditationTrack by MeditationTrack
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouriteTrack(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.mediation.favorited');
            $pivotExsisting = $meditation
                ->trackUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('meditation_track_id', $meditation->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                           = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited    = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.mediation.unfavorited');
                }
            } else {
                $meditation
                    ->trackUserLogs()
                    ->attach($user, ['favourited' => true, 'favourited_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
