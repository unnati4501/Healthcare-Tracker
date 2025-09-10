<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V31;

use App\Http\Collections\V11\CategoryWiseTrackCollection;
use App\Http\Collections\V30\RecentMeditationCollection;
use App\Http\Controllers\API\V30\MeditationController as v30MeditationController;
use App\Http\Resources\V15\CategoryWiseTrackResource;
use App\Models\MeditationTrack;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v30MeditationController
{

    /**
     *  API to fetch track data by Meditation category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackList(Request $request)
    {
        try {
            $user      = $this->user();
            $company   = $user->company()->select('companies.id')->first();
            $team      = $user->teams()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($request->meditationSubCategory > 0) {
                $subcatData = SubCategory::find($request->meditationSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            if ($request->meditationSubCategory <= 0) {
                $trackIds = MeditationTrack::join('meditation_tracks_team', function ($join) use ($team) {
                        $join
                            ->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_team.team_id', $team->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id');

                if ($request->meditationSubCategory == 0) {
                    //My favorite track list
                    $trackIds
                        ->join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                        ->where("user_meditation_track_logs.user_id", $user->id)
                        ->where(["favourited" => 1, "sub_categories.status" => 1]);
                } elseif ($request->meditationSubCategory == '-2') {
                    //Guided track list (track type:audio , audio type:vocal)
                    $trackIds
                        ->where("meditation_tracks.type", 1)
                        ->where("meditation_tracks.audio_type", 2);
                } else {
                    //All tracks
                    $trackIds
                        ->where("sub_categories.status", 1)
                        ->groupBy('meditation_tracks.id');
                }

                $trackIds = $trackIds->pluck("meditation_tracks.id")->toArray();
                if (empty($trackIds)) {
                    return $this->successResponse(['data' => []], 'No results');
                }

                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
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
                        DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                        DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                        DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened")
                    )
                    ->when(($xDeviceOs != config('zevolifesettings.PORTAL') && $request->meditationSubCategory != 0), function ($query) {
                        // audio, youtube, vimeo, video
                        $query->orderByRaw('FIELD(meditation_tracks.type, 1, 3, 4, 2)');
                    })
                    ->orderByDesc('count_user_listened')
                    ->orderByDesc('meditation_tracks.id')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $categoryWiseTrackData = MeditationTrack::where("sub_category_id", $request->meditationSubCategory)
                    ->join('meditation_tracks_team', function ($join) use ($team) {
                        $join
                            ->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_team.team_id', $team->id);
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
                    )
                    ->when($xDeviceOs != config('zevolifesettings.PORTAL'), function ($query) {
                        // audio, youtube, vimeo, video
                        $query->orderByRaw('FIELD(meditation_tracks.type, 1, 3, 4, 2)');
                    })
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->orderBy('meditation_tracks.updated_at', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if ($categoryWiseTrackData->count() > 0) {
                return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get recent meditations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentMeditations(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.most_liked_meditation_limit');

            $data['recentMeditations'] = $this->getRecentMeditationList();
            $data['guidedMeditations'] = $this->getRecentMeditationList('guided');
            $data['mostPlayed']        = $this->getRecentMeditationList('played');
            $data['mostLiked']         = MeditationTrack::select(
                'meditation_tracks.*',
                "sub_categories.name as meditationSubCategory",
                DB::raw('IFNULL(sum(user_meditation_track_logs.liked),0) AS most_liked'),
                DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened")
            )
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks.id', '=', 'meditation_tracks_team.meditation_track_id')
                        ->where('meditation_tracks_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', '=', 'meditation_tracks.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'meditation_tracks.sub_category_id');
                })
                ->orderBy('most_liked', 'DESC')
                ->groupBy('meditation_tracks.id')
                ->having('most_liked', '>', '0')
                ->limit($limit)
                ->get();
            //->shuffle();
            // Collect required data and return response
            return $this->successResponse(new RecentMeditationCollection($data), 'recent meditations listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent meditation [Recent, Most Played, Guided]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentMeditationList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.recent_meditation_limit');

            $records = MeditationTrack::select(
                'meditation_tracks.*',
                "sub_categories.name as meditationSubCategory",
                DB::raw('IFNULL(sum(user_meditation_track_logs.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_meditation_track_logs.liked),0) AS most_liked'),
                DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened")
            )
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks.id', '=', 'meditation_tracks_team.meditation_track_id')
                        ->where('meditation_tracks_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', '=', 'meditation_tracks.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'meditation_tracks.sub_category_id');
                });

            if ($type == 'guided') {
                $limit = config('zevolifesettings.default_limits.guided_meditation_limit');
                $records->where(["meditation_tracks.type" => 1, "meditation_tracks.audio_type" => 2]);
            }

            if ($type == 'played') {
                $records->orderBy('view_count', 'DESC')
                    ->orderBy('meditation_tracks.updated_at', 'DESC');
            } else {
                $records->orderBy('meditation_tracks.updated_at', 'DESC');
            }
            $records = $records->groupBy('meditation_tracks.id');
            $records = $records->limit($limit)->get()->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
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
            $team    = $user->teams()->first();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                        ->where('meditation_tracks_team.team_id', $team->id);
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
            $team    = $user->teams()->first();

            // Check meditation available with this company or not
            $checkMeditation = $meditation->meditationteam()->where('team_id', $team->id)->count();

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
}
