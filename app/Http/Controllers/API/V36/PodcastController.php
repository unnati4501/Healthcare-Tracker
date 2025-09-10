<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V36;

use App\Http\Controllers\Controller;
use App\Http\Collections\V36\CategoryWisePodcastCollection;
use App\Http\Collections\V36\RecentPodcastCollection;
use App\Http\Resources\V36\CategoryWisePodcastResource;
use App\Http\Collections\V36\PodcastDetailsCollection;
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

class PodcastController extends Controller
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
            $company   = $user->company()->select('companies.id')->first();
            $team      = $user->teams()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $mostListenedPodcasts = $getAllPodcastRecentList = $getRecentPodcastIds = [];

            if ($request->podcastSubCategory > 0) {
                $subcatData = SubCategory::find($request->podcastSubCategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $getRecentList  = $this->getAllPodcastList((int)$request->podcastSubCategory, $company, 'new');
            if(!empty($getRecentList)){
                $getRecentPodcastIds = array_column($getRecentList, 'id');
            }
            $getPopularList = $this->getAllPodcastList((int)$request->podcastSubCategory, $company, 'popular', $getRecentPodcastIds);
            $getAllList     = $this->getAllPodcastList((int)$request->podcastSubCategory, $company, '');
            
            if(!empty($getRecentList) && count($getRecentList) > 0 && !empty($getPopularList) && count($getPopularList) > 0){
                $mostListenedPodcasts = array_udiff($getPopularList, $getRecentList,  function (array $obj_a, array $obj_b) {
                    return serialize($obj_a['id']) <=> serialize($obj_b['id']);
                });
            }
            
            if(!empty($getAllList) && count($getAllList) > 0 && !empty($getPopularList) && count($getPopularList) > 0){
                $getAllPodcastList = array_udiff($getAllList, $getPopularList,  function (array $obj_a, array $obj_b) {
                    return serialize($obj_a['id']) <=> serialize($obj_b['id']);
                });
            }

            if(!empty($getAllPodcastList) && count($getAllPodcastList) > 0 && !empty($getRecentList) && count($getRecentList) > 0){
                $getAllPodcastRecentList = array_udiff($getAllPodcastList, $getRecentList,  function (array $obj_a, array $obj_b) {
                    return serialize($obj_a['id']) <=> serialize($obj_b['id']);
                });
            }

            if(!empty($getRecentList) || !empty($mostListenedPodcasts)  || !empty($getAllPodcastRecentList)){
                $allPodcasts                = array_merge($getRecentList, $mostListenedPodcasts, $getAllPodcastRecentList);
                $categoryWisePodcastData    = Podcast::hydrate($allPodcasts)->toArray();
                $categoryWisePodcastData    = $this->paginate($categoryWisePodcastData);
            }
            if (!empty($categoryWisePodcastData) && $categoryWisePodcastData->count() > 0) {
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
     * Get all stories with new and popular tag
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPodcastList($subcategory, $company, $type, $recentPodcastData = [])
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $role     = getUserRole();
            $team     = $user->teams()->first();
            $records = Podcast::select(
                "podcasts.id",
                "podcasts.sub_category_id",
                "podcasts.coach_id",
                "podcasts.title",
                "podcasts.duration",
                "podcasts.view_count",
                DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id`) AS podcast_view_count"),
                DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_podcasts WHERE podcast_id = `podcasts`.`id` GROUP BY podcast_id ) AS count_user_listened"),
                DB::raw("'{$type}' as tag"),
            );
            $records->join('podcast_team', function ($join) use ($team) {
                $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                    ->where('podcast_team.team_id', $team->id);
            })
            ->leftJoin('user_podcast_logs', 'user_podcast_logs.podcast_id', '=', 'podcasts.id')
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'podcasts.sub_category_id');
            });
            
            if (!empty($subcategory) && $subcategory > 0) {
                $records = $records->where("podcasts.sub_category_id", $subcategory);
            } elseif ($subcategory == 0) {
                // My favorite podcasts
                $records = $records->where("user_podcast_logs.user_id", $user->id)
                    ->where(["user_podcast_logs.favourited" => 1, "sub_categories.status" => 1]);

            } elseif (!empty($subcategory) && $subcategory == -1) {
                $records = $records->where("sub_categories.status", 1);
            }

            if ($type == "new") {
                $records = $records->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id')->limit(5);
            } elseif ($type == "popular"){
                // Remove recent podcasts Ids from the popular podcasts to avoid duplication
                if(!empty($recentPodcastData)){
                    $records = $records->whereNotIn("podcasts.id", $recentPodcastData);
                }
                $records = $records->havingRaw("podcast_view_count > 0")
                    ->orderBy('podcasts.view_count', 'DESC')
                    ->groupBy('podcasts.id')->limit(5);
            } else {
                $records = $records->orderBy('podcasts.updated_at', 'DESC')
                    ->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id');
            }
            $records = $records->get()->toArray();
            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
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
            $company = $user->company()->first();
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
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $getRecentPodcastIds = [];

            $recentPodcasts  = $this->getRecentPodcastList('new');
            if(!empty($recentPodcasts)){
                $getRecentPodcastIds = array_column($recentPodcasts, 'id');
            }
            $popularPodcasts = $this->getRecentPodcastList('popular', null, null, $getRecentPodcastIds);
            $getAllPodcasts  = $this->getRecentPodcastList('');
            
            $mostLikedPodcasts = array_udiff($popularPodcasts, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            
            $getAllPodcastsList = array_udiff($getAllPodcasts, $popularPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            
            $getAllPodcastsRecentList = array_udiff($getAllPodcastsList, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            $allPodcasts = array_merge($recentPodcasts, $mostLikedPodcasts, $getAllPodcastsRecentList);  
            $records     = Podcast::hydrate($allPodcasts)->toArray();
            $records     = $this->paginate($records);
        
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
     * Get recent podcasts with new and popular tag
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentPodcastList($type = "", $podcastId = null, $podcastSubcategoryId = null, $recentPodcastIds = [])
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $records = Podcast::select(
                'podcasts.*',
                "sub_categories.name as podcastSubCategory",
                DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id`) AS podcast_view_count"),
                DB::raw("(SELECT duration_listened FROM user_incompleted_podcasts WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_podcasts WHERE podcast_id = `podcasts`.`id` GROUP BY podcast_id ) AS count_user_listened"),
                DB::raw("'{$type}' as tag")
            )
            ->join('podcast_team', function ($join) use ($team) {
                $join->on('podcasts.id', '=', 'podcast_team.podcast_id')
                    ->where('podcast_team.team_id', '=', $team->getKey());
            })
            ->leftJoin('user_podcast_logs', 'user_podcast_logs.podcast_id', '=', 'podcasts.id')
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'podcasts.sub_category_id');
            });

            if(!empty($podcastId) && $podcastId != null){
                $records = $records->where("podcasts.id", '!=', $podcastId)
                    ->where("sub_category_id", $podcastSubcategoryId);
            }
            if ($type == "new") {
                $records = $records->orderBy('podcasts.id', 'DESC')
                    ->limit(5)->groupBy('podcasts.id');
            } elseif ($type == "popular") {
                 // Remove recent podcasts Ids from the popular podcasts to avoid duplication
                 if(!empty($recentPodcastIds)){
                    $records = $records->whereNotIn("podcasts.id", $recentPodcastIds);
                 }
                $records = $records->havingRaw("podcast_view_count > 0")
                    ->orderBy('view_count', 'DESC')
                    ->limit(5)->groupBy('podcasts.id');
            } else {
                $records = $records->orderBy('podcasts.updated_at', 'DESC')
                    ->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id');
            }
            $records = $records->get()->toArray();
            return $records;
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
        $company = $user->company()->first();
        $team    = $user->teams()->first();
        $getRecentPodcastIds = [];

        try{
            $recentPodcasts  = $this->getRecentPodcastList('new', $podcast->id, $podcast->sub_category_id);
            if(!empty($recentPodcasts)){
                $getRecentPodcastIds = array_column($recentPodcasts, 'id');
            }
            $popularPodcasts = $this->getRecentPodcastList('popular', $podcast->id, $podcast->sub_category_id, $getRecentPodcastIds);
            $getAllPodcasts  = $this->getRecentPodcastList('', $podcast->id, $podcast->sub_category_id);
            
            $mostLikedPodcasts = array_udiff($popularPodcasts, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            
            $getAllPodcastsList = array_udiff($getAllPodcasts, $popularPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            
            $getAllPodcastsRecentList = array_udiff($getAllPodcastsList, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            $allPodcasts    = array_merge($recentPodcasts, $mostLikedPodcasts, $getAllPodcastsRecentList);  
            $podcastData     = Podcast::hydrate($allPodcasts)->toArray();
            $podcastData     = $this->paginate($podcastData);

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
            $company = $user->company()->first();
            $team    = $user->teams()->first();
            $getRecentPodcastIds = [];
            $recentPodcasts  = $this->getSavedPodcastList('new');
            if(!empty($recentPodcasts)){
                $getRecentPodcastIds = array_column($recentPodcasts, 'id');
            }
            $popularPodcasts = $this->getSavedPodcastList('popular', $getRecentPodcastIds);
            $getAllList      = $this->getSavedPodcastList('');
            //dd($recentPodcasts);
            $mostLikedPodcasts = array_udiff($popularPodcasts, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });

            $getAllPodcastList = array_udiff($getAllList, $popularPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });

            $getAllPodcastRecentList = array_udiff($getAllPodcastList, $recentPodcasts,  function (array $obj_a, array $obj_b) {
                return serialize($obj_a['id']) <=> serialize($obj_b['id']);
            });
            $allPodcasts  = array_merge($recentPodcasts, $mostLikedPodcasts, $getAllPodcastRecentList);            
            $records     = Podcast::hydrate($allPodcasts)->toArray();
            $records     = $this->paginate($records);

            if ($records->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWisePodcastCollection($records), 'Podcasts Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
            
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get saved podcasts with new and popular tag
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedPodcastList($type = "", $recentPodcastIds = []){
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
           
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
                $records = Podcast::whereIn("podcasts.id", $podcastIds)
                    ->leftJoin("user_incompleted_podcasts", function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_incompleted_podcasts.podcast_id')
                            ->where('user_incompleted_podcasts.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_podcast_logs', function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_podcast_logs.podcast_id')
                            ->where('user_podcast_logs.user_id', '=', $user->getKey());
                    })
                    ->select("podcasts.*", "user_incompleted_podcasts.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"), DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM user_podcast_logs WHERE podcast_id = `podcasts`.`id`) AS podcast_view_count"), DB::raw("'{$type}' as tag"))
                    ->orderBy('user_podcast_logs.saved_at', 'DESC');
                    
                if ($type == "new") {
                    $records = $records->orderBy('podcasts.id', 'DESC')
                        ->limit(5)->groupBy('podcasts.id');
                } elseif ($type == "popular") {
                     // Remove recent podcasts Ids from the popular podcasts to avoid duplication
                    if(!empty($recentPodcastIds)){
                        $records = $records->whereNotIn("podcasts.id", $recentPodcastIds);
                    }
                    $records = $records->havingRaw("podcast_view_count > 0")
                        ->orderBy('podcasts.view_count', 'DESC')
                        ->limit(5)->groupBy('podcasts.id');
                } else {
                    $records = $records->orderBy('podcasts.updated_at', 'DESC')
                        ->orderBy('podcasts.id', 'DESC')
                        ->groupBy('podcasts.id');
                }
                $records = $records->get()->toArray();
            }
            return $records ?? [];
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
