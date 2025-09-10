<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V36\PodcastController as v36PodcastController;
use App\Http\Controllers\Controller;
use App\Http\Collections\V38\CategoryWisePodcastCollection;
use App\Http\Collections\V38\RecentPodcastCollection;
use App\Http\Resources\V38\CategoryWisePodcastResource;
use App\Http\Collections\V38\PodcastDetailsCollection;
use App\Http\Requests\Api\V36\PodcastDurationRequest;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Podcast;
use App\Models\User;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class PodcastController extends v36PodcastController
{
    use ServesApiTrait, ProvidesAuthGuardTrait, PaginationTrait;

    /**
     *  API to fetch podcast data by Podcast category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function podcastList(Request $request)
    {
        try {
            $user      = $this->user();
            $team      = $user->teams()->first();

            if ($request->podcastSubCategory > 0) {
                $subcatData = SubCategory::find($request->podcastSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            if ($request->podcastSubCategory <= 0) {
                $trackIds = Podcast::join('podcast_team', function ($join) use ($team) {
                        $join
                            ->on('podcast_team.podcast_id', '=', 'podcasts.id')
                            ->where('podcast_team.team_id', $team->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id');

                if ($request->podcastSubCategory == 0) {
                    //My favorite track list
                    $trackIds
                        ->join('user_podcast_logs', 'podcasts.id', '=', 'user_podcast_logs.podcast_id')
                        ->where("user_podcast_logs.user_id", $user->id)
                        ->where(["favourited" => 1, "sub_categories.status" => 1]);
                } else {
                    //All tracks
                    $trackIds
                        ->where("sub_categories.status", 1)
                        ->groupBy('podcasts.id');
                }

                $trackIds = $trackIds->pluck("podcasts.id")->toArray();
                if (empty($trackIds)) {
                    return $this->successResponse(['data' => []], 'No results');
                }

                $categoryWisePodcastData = Podcast::whereIn("podcasts.id", $trackIds)
                    ->select(
                        "podcasts.id",
                        "podcasts.sub_category_id",
                        "podcasts.coach_id",
                        "podcasts.title",
                        "podcasts.duration",
                        "podcasts.view_count",
                        "podcasts.caption",
                        DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                        DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                        DB::raw("(SELECT favourited_at FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                        DB::raw("(SELECT count(id) FROM user_listened_podcasts WHERE podcast_id = `podcasts`.`id` GROUP BY podcast_id ) AS count_user_listened")
                    )
                    ->addSelect(DB::raw("CASE
                        WHEN podcasts.caption = 'New' then 0
                        WHEN podcasts.caption = 'Popular' then 1
                        ELSE 2
                        END AS caption_order"
                    ))
                    ->orderBy('caption_order', 'ASC')
                    ->orderByDesc('count_user_listened')
                    ->orderByDesc('podcasts.id')
                    ->groupBy('podcasts.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $categoryWisePodcastData = Podcast::where("sub_category_id", $request->podcastSubCategory)
                    ->join('podcast_team', function ($join) use ($team) {
                        $join
                            ->on('podcast_team.podcast_id', '=', 'podcasts.id')
                            ->where('podcast_team.team_id', $team->id);
                    })
                    ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                    ->where(["sub_categories.status" => 1])
                    ->select(
                        "podcasts.id",
                        "podcasts.sub_category_id",
                        "podcasts.coach_id",
                        "podcasts.title",
                        "podcasts.duration",
                        "podcasts.view_count",
                        "podcasts.caption",
                        DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                        DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes")
                    )
                    ->addSelect(DB::raw("CASE
                        WHEN podcasts.caption = 'New' then 0
                        WHEN podcasts.caption = 'Popular' then 1
                        ELSE 2
                        END AS caption_order"
                    ))
                    ->orderBy('caption_order', 'ASC')
                    ->orderByDesc('podcasts.updated_at', 'DESC')
                    ->groupBy('podcasts.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
                    
            }
            if ($categoryWisePodcastData->count() > 0) {
                return $this->successResponse(new CategoryWisePodcastCollection($categoryWisePodcastData), 'Podcasts Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Podcast details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function podcastDetails(Request $request, Podcast $podcast)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $team    = $user->teams()->first();

            // Check podcast available with this company or not
            $checkPodcast = $podcast->podcastteam()->where('team_id', $team->id)->count();

            if ($checkPodcast <= 0) {
                return $this->notFoundResponse('Podcast not found');
            }

            $podcastDetailData = Podcast::where(["podcasts.id" => $podcast->id, "sub_categories.status" => 1])
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                ->leftJoin("user_incompleted_podcasts", function ($join) use ($user) {
                    $join->on('podcasts.id', '=', 'user_incompleted_podcasts.podcast_id')
                        ->where('user_incompleted_podcasts.user_id', '=', $user->getKey());
                })
                ->leftJoin("user_podcast_logs", "podcasts.id", "=", "user_podcast_logs.podcast_id")
                ->select("podcasts.*", "user_incompleted_podcasts.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                ->first();
                
            if (!empty($podcastDetailData)) {
                $moreLikePodcastData = Podcast::join('podcast_team', function ($join) use ($team) {
                    $join
                        ->on('podcast_team.podcast_id', '=', 'podcasts.id')
                        ->where('podcast_team.team_id', $team->id);
                })
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                ->where(["sub_categories.status" => 1])
                ->where("podcasts.id", '!=', $podcast->id)
                ->where("sub_category_id", $podcast->sub_category_id)
                ->select(
                    "podcasts.id",
                    "podcasts.sub_category_id",
                    "podcasts.coach_id",
                    "podcasts.title",
                    "podcasts.duration",
                    "podcasts.view_count",
                    "podcasts.caption",
                    DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                    DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                    DB::raw("'new' as tag"),
                )
                ->orderByDesc('podcasts.id', 'DESC')
                ->groupBy('podcasts.id')
                ->limit(1)
                ->get();

                $podcastDetailData ['moreLikePodcasts'] = $moreLikePodcastData;    
                return $this->successResponse([
                    "data" => new PodcastDetailsCollection($podcastDetailData),
                ], 'Podcast Retrieved Successfully');
            } else {
                return $this->notFoundResponse('Podcast not found');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to favorited unfavourited Podcast
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouritePodcast(Request $request, Podcast $podcast)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.podcast.favorited');
            $pivotExsisting = $podcast
                ->podcastUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('podcast_id', $podcast->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                           = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited    = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.podcast.unfavorited');
                }
            } else {
                $podcast
                    ->podcastUserLogs()
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

    /**
     * API to liked unliked Podcast
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikePodcast(Request $request, Podcast $podcast)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.podcast.liked');
            $pivotExsisting = $podcast
                ->podcastUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('podcast_id', $podcast->getKey())
                ->first();
            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    $message = trans('api_messages.podcast.unliked');
                }
            } else {
                $podcast
                    ->podcastUserLogs()
                    ->attach($user, ['liked' => true]);
            }
            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $podcast->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to saved unsaved Podcast
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsavePodcast(Request $request, Podcast $podcast)
    {
        try {
            \DB::beginTransaction();

            $user           = $this->user();
            $message        = trans('api_messages.podcast.saved');
            $pivotExsisting = $podcast
                ->podcastUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('podcast_id', $podcast->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = (($saved == 1) ? 0 : 1);
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    $message = trans('api_messages.podcast.unsaved');
                }
            } else {
                $podcast->podcastUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent podcasts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentPodcasts(Request $request)
    {
        try {
            $user     = $this->user();
            $team     = $user->teams()->first();

            $records = Podcast::select(
                'podcasts.*',
                "sub_categories.name as podcastSubCategory",
                DB::raw('IFNULL(sum(user_podcast_logs.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_podcast_logs.liked),0) AS most_liked'),
                DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_podcasts WHERE podcast_id = `podcasts`.`id` GROUP BY podcast_id ) AS count_user_listened")
            )
                ->addSelect(DB::raw("CASE
                    WHEN podcasts.caption = 'New' then 0
                    WHEN podcasts.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ))
                ->join('podcast_team', function ($join) use ($team) {
                    $join->on('podcasts.id', '=', 'podcast_team.podcast_id')
                        ->where('podcast_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_podcast_logs', 'user_podcast_logs.podcast_id', '=', 'podcasts.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'podcasts.sub_category_id');
                })
                ->orderBy('caption_order', 'ASC')
                ->orderBy('podcasts.updated_at', 'DESC')
                ->groupBy('podcasts.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));
              
            // Collect required data and return response
            if ($records->count() > 0) {
                return $this->successResponse(new RecentPodcastCollection($records), 'Podcasts Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Save the duration
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDuration(PodcastDurationRequest $request, Podcast $podcast)
    {

        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // fetch user Podcast data
            $pivotExsisting = $podcast->podcastIncompletedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('podcast_id', $podcast->getKey())->first();

            if (!empty($pivotExsisting)) {
                $pivotExsisting->pivot->duration_listened = $request->duration;
                $pivotExsisting->pivot->save();
            } else {
                $podcast->podcastIncompletedUserLogs()->attach($user, ['duration_listened' => $request->duration]);
            }

            \DB::commit();
            return $this->successResponse([], 'Podcast duration(s) saved successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Mark Podcast as completed
     *
     * @param Request $request, Podcast $podcast
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted(Request $request, Podcast $podcast)
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
            $daily_meditation_limits = ($companyLimits['daily_meditation_limit'] ?? config("zevolifesettings.default_limits.daily_podcast_limit", 10));
            $daily_track_limit       = ($companyLimits['daily_track_limit'] ?? config("zevolifesettings.default_limits.daily_track_limit", 5));

            // fetch user Podcast data
            $podcast->podcastIncompletedUserLogs()->wherePivot('user_id', $user->getKey())->detach();

            // get count of total listened podcast for current day by user
            $totalListenedPodcastCount = $user
                ->completedPodcasts()
                ->whereDate(\DB::raw("CONVERT_TZ(user_listened_podcasts.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                ->count('user_listened_podcasts.podcast_id');

            // check user reach maxmimum podcast limit for the day
            if ($totalListenedPodcastCount < $daily_meditation_limits) {
                // get count of perticular podcast for current day listen by the user
                $podcastListenCount = $podcast
                    ->podcastListenedUserLogs()
                    ->wherePivot('user_id', $user->getKey())
                    ->whereDate(\DB::raw("CONVERT_TZ(user_listened_podcasts.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                    ->count('user_listened_podcasts.podcast_id');

                // check user reach maxmimum limit for the day for same podcast
                if ($podcastListenCount < $daily_track_limit) {
                    $podcast->podcastListenedUserLogs()->attach($user, ['duration_listened' => $podcast->duration]);
                }
            }
            \DB::commit();

            return $this->successResponse([], 'Podcast marked as completed.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * More Like Podcasts (List of all the pocast with same category)
     *
     * @param Request $request, Podcast $podcast
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreLikePodcasts(Podcast $podcast, Request $request){
        $user    = $this->user();
        $team    = $user->teams()->first();

        try{
            $podcastData = Podcast::join('podcast_team', function ($join) use ($team) {
                $join
                        ->on('podcast_team.podcast_id', '=', 'podcasts.id')
                        ->where('podcast_team.team_id', $team->id);
                })
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')                ->where(["sub_categories.status" => 1])
                ->where("podcasts.id", '!=', $podcast->id)
                ->where("sub_category_id", $podcast->sub_category_id)
                ->select(
                    "podcasts.id",
                    "podcasts.sub_category_id",
                    "podcasts.coach_id",
                    "podcasts.title",
                    "podcasts.duration",
                    "podcasts.view_count",
                    "podcasts.caption",
                    DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                    DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes")
                )
                ->addSelect(DB::raw("CASE
                    WHEN podcasts.caption = 'New' then 0
                    WHEN podcasts.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ))
                ->orderBy('caption_order', 'ASC')
                ->orderByDesc('podcasts.updated_at', 'DESC')
                ->groupBy('podcasts.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if ($podcastData->count() > 0) {
                    return $this->successResponse(new CategoryWisePodcastCollection($podcastData), 'Podcasts Retrieved Successfully');
                } else {
                    return $this->successResponse(['data' => []], 'No results');
                }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * API to fetch saved podcast data by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $team    = $user->teams()->first();

            $podcastIds = Podcast::join('user_podcast_logs', 'podcasts.id', '=', 'user_podcast_logs.podcast_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                ->join('podcast_team', function ($join) use ($team) {
                    $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                        ->where('podcast_team.team_id', $team->id);
                })
                ->where("user_podcast_logs.user_id", $user->id)
                ->where(["user_podcast_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("podcasts.id")
                ->toArray();

            if (!empty($podcastIds)) {
                $categoryWisePodcastData = Podcast::whereIn("podcasts.id", $podcastIds)
                    ->leftJoin("user_incompleted_podcasts", function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_incompleted_podcasts.podcast_id')
                            ->where('user_incompleted_podcasts.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_podcast_logs', function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_podcast_logs.podcast_id')
                            ->where('user_podcast_logs.user_id', '=', $user->getKey());
                    })
                    ->select("podcasts.*", "user_incompleted_podcasts.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                    ->addSelect(DB::raw("CASE
                        WHEN podcasts.caption = 'New' then 0
                        WHEN podcasts.caption = 'Popular' then 1
                        ELSE 2
                        END AS caption_order"
                    ))
                    ->orderBy('caption_order', 'ASC')
                    ->orderBy('user_podcast_logs.saved_at', 'DESC')
                    ->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if ($categoryWisePodcastData->count() > 0) {
                    // collect required data and return response
                    return $this->successResponse(new CategoryWisePodcastCollection($categoryWisePodcastData), 'Podcasts Retrieved Successfully');
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
