<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V32;

use App\Http\Collections\V31\FeedListCollection;
use App\Http\Collections\V29\FeedRecentStoriesCollection;
use App\Http\Controllers\API\V31\FeedController as v31FeedController;
use App\Http\Resources\V32\FeedResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use App\Models\Category;
use App\Models\SubCategory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v31FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get recent stories feed [Most Listened, Most Watched, Latest Articles, Most Liked]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentStories(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $data['recentStories'] = $this->getRecentFeedList();
            $data['mostListened']  = $this->getRecentFeedList('listen');
            $data['mostWatched']   = $this->getRecentFeedList('watch');
            $data['mostLiked']     = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(feed_user.liked),0) AS most_liked')
            )

                ->join('feed_team', function ($join) use ($team) {
                    $join->on('feeds.id', '=', 'feed_team.feed_id')
                        ->where('feed_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
                ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                })

                ->where("feeds.is_stick", false)
                ->orderBy('most_liked', 'DESC')
                ->groupBy('feeds.id')
                ->limit(12)
                ->get()
                ->shuffle();

            // Collect required data and return response
            return $this->successResponse(new FeedRecentStoriesCollection($data), 'Feed recent stories listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent stories feed [Most Listened, Most Watched, Latest Articles]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentFeedList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count')
            )
                ->join('feed_team', function ($join) use ($team) {
                    $join->on('feeds.id', '=', 'feed_team.feed_id')
                        ->where('feed_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
                ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            if ($type == 'watch') {
                $records->whereIn("feeds.type", [2, 3, 5]);
            } elseif ($type == 'listen') {
                $records->where("feeds.type", 1);
            } elseif ($type == 'read') {
                $records->where("feeds.type", 4);
            }
            $records->where("feeds.is_stick", false);

            if ($type == 'watch' || $type == 'listen') {
                $records->orderBy('view_count', 'DESC')
                    ->orderBy('feeds.updated_at', 'DESC');
            } else {
                $records->orderBy('feeds.updated_at', 'DESC');
            }

            $records = $records->groupBy('feeds.id')
                ->limit(5)
                ->get()
                ->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
    /**
     * Get feed details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, Feed $feed)
    {
        try {
            $user      = $this->user();
            $role      = getUserRole();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $company   = $user->company()->first();
            $team      = $user->teams()->first();

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $checkRecords = $feed->feedteam()->where('team_id', $team->id)->count();
                if ($checkRecords <= 0) {
                    return $this->notFoundResponse('Feed not found');
                }
            }

            if (!is_null($company)) {
                $feed->rewardPortalPointsToUser($user, $company, 'feed');
            }

            return $this->successResponse([
                'data' => new FeedResource($feed),
            ], 'Feed details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get feed list based on author id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreStories(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->first();
            $timezone    = $user->timezone ?? config('app.timezone');
            $role        = getUserRole();
            $team        = $user->teams()->first();
            $feedRecords = Feed::select(
                'feeds.*',
                'sub_categories.name as courseSubCategory',
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count')
            );

            if ($role->group == 'company' && is_null($company->parent_id) && !$company->is_reseller) {
                $feedRecords->addSelect(DB::raw("CASE
                        WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                        WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                        ELSE 2
                        END AS is_stick_count"));
            } else {
                if ($company->parent_id == null && $company->is_reseller) {
                    $feedRecords->addSelect(DB::raw("CASE
                        WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                        WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                        ELSE 2
                        END AS is_stick_count"));
                } elseif (!is_null($company->parent_id)) {
                    $feedRecords->addSelect(DB::raw("CASE
                        WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                        WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                        WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                        ELSE 3
                        END AS is_stick_count"));
                } else {
                    $feedRecords->addSelect(DB::raw("CASE
                        WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                        WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                        ELSE 2
                        END AS is_stick_count"));
                }
            }

            $feedRecords->join('feed_team', function ($join) use ($team) {
                $join->on('feeds.id', '=', 'feed_team.feed_id')
                    ->where('feed_team.team_id', '=', $team->getKey());
            });

            $feedRecords->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
                ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $feedRecords->where('feeds.creator_id', $request->authorId)
                ->groupBy('feeds.id')
                ->orderBy('is_stick_count', 'ASC')
                ->orderBy('feeds.updated_at', 'DESC')
                ->orderBy('feeds.id', 'DESC');

            $feedRecords = $feedRecords->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                (($feedRecords->count() > 0) ? new FeedListCollection($feedRecords) : ['data' => []]),
                (($feedRecords->count() > 0) ? 'Feed List retrieved successfully.' : 'No results')
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
