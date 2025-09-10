<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V36\FeedController as v36FeedController;
use App\Http\Collections\V38\FeedCardListingCollection;
use App\Http\Collections\V38\FeedListCollection;
use App\Http\Collections\V38\FeedRecentStoriesCollection;
use App\Http\Resources\V38\FeedResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use App\Models\Category;
use App\Models\SubCategory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class FeedController extends v36FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait, PaginationTrait;
    /**
     * Get feed listing accroding to current time and user expertise level
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $type = 'read', $subcategory = '')
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $role     = getUserRole();
            $team     = $user->teams()->first();

            if (!empty($subcategory) && $subcategory > 0) {
                $subcategoryData = SubCategory::find($subcategory);

                if (empty($subcategoryData)) {
                    return $this->notFoundResponse("Sorry! SubCategory data not found");
                }
            }

            $feedRecords = Feed::select(
                'feeds.*',
                'sub_categories.name as courseSubCategory',
                DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM feed_user WHERE feed_id = `feeds`.`id`) AS feed_view_count"),
                //B::raw("'{$filterType}' as tag"),
            );
    
            $feedRecords->addSelect(DB::raw("CASE
                    WHEN feeds.caption = 'New' then 0
                    WHEN feeds.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ));
    
            if (!empty($subcategory) && $subcategory >= -1) {
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
                ->where(function (Builder $query) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });
    
            if ($type == 'watch') {
                $feedRecords->whereIn("feeds.type", [2, 3, 5]);
            } elseif ($type == 'listen') {
                $feedRecords->where("feeds.type", 1);
            } elseif ($type == 'read') {
                $feedRecords->where("feeds.type", 4);
            } elseif ((int)$type === 0 && $type != 'all') {
                //My favourite feed query
                $feedRecords
                    ->where("feed_user.user_id", $user->id)
                    ->where(["feed_user.favourited" => 1, "sub_categories.status" => 1]);
            }
    
            if (!empty($subcategory) && $subcategory > 0) {
                $feedRecords = $feedRecords->where("feeds.sub_category_id", $subcategory);
            }
    
            if (!empty($subcategory) && $subcategory >= -1) {
                $feedRecords->orderBy('is_stick_count', 'ASC');
                if ($type != 0) {
                    $feedRecords->havingRaw(("sum(feed_user.view_count) > 0 "));
                }
            }
            $feedRecords = $feedRecords->orderBy('caption_order', 'ASC')
            ->orderBy('feeds.updated_at', 'DESC')
            ->orderBy('feeds.id', 'DESC')
            ->groupBy('feeds.id')
            ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                (($feedRecords->count() > 0) ? new FeedListCollection($feedRecords) : ['data' => []]),
                (($feedRecords->count() > 0) ? 'Feed List retrieved successfully.' : 'No results')
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent stories feed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentStories(Request $request)
    {
        try {
            $user     = $this->user();
            $team     = $user->teams()->first();
            $records = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM feed_user WHERE feed_id = `feeds`.`id`) AS feed_view_count"),
            );

            $records->addSelect(DB::raw("CASE
                WHEN feeds.caption = 'New' then 0
                WHEN feeds.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"
            ));
            
            $records->join('feed_team', function ($join) use ($team) {
                $join->on('feeds.id', '=', 'feed_team.feed_id')
                    ->where('feed_team.team_id', '=', $team->getKey());
            })
            ->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
            ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
            })
            ->where(function (Builder $query) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
            })
            ->where(function (Builder $query) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
            });

            $records = $records->where("feeds.is_stick", false);

            $records = $records->orderBy('caption_order', 'ASC')
                ->orderBy('feeds.updated_at', 'DESC')
                ->orderBy('feeds.id', 'DESC')
                ->groupBy('feeds.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            // Collect required data and return response
            if (count($records) > 0) {
                return $this->successResponse(new FeedRecentStoriesCollection($records), 'Recent feeds retrived successfully');
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
     * Get feed list based on author id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreStories(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->first();
            $role        = getUserRole();
            $team        = $user->teams()->first();
            $feedRecords = Feed::select(
                'feeds.*',
                'sub_categories.name as courseSubCategory',
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
                ->where(function (Builder $query) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) {
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
            dd($e);
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
     * get saved feed listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            $user     = $this->user();
            $team     = $user->teams()->first();
           
            $records = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw("(SELECT SUM(NULLIF(view_count, 0)) FROM feed_user WHERE feed_id = `feeds`.`id`) AS feed_view_count"),
            );

            $records->addSelect(DB::raw("CASE
                WHEN feeds.caption = 'New' then 0
                WHEN feeds.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"
            ));
            $records->join('feed_user', 'feed_user.feed_id', '=', 'feeds.id');

            $records->join('feed_team', function ($join) use ($team) {
                $join->on('feeds.id', '=', 'feed_team.feed_id')
                    ->where('feed_team.team_id', '=', $team->getKey());
            })
            ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
            });
            $records = $records->where('feed_user.user_id', $user->getKey())
                ->where('feed_user.saved', true);

            $records = $records->orderBy('caption_order', 'ASC')
                ->orderBy('feed_user.saved_at', 'DESC')
                ->orderBy('feeds.updated_at', 'DESC')
                ->orderBy('feeds.id', 'DESC')
                ->groupBy('feeds.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

           
            return $this->successResponse(
                ($records->count() > 0) ? new FeedListCollection($records) : ['data' => []],
                ($records->count() > 0) ? 'Feed List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
