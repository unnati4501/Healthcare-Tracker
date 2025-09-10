<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V26;

use App\Http\Collections\V4\SubCategoryCollection as v4subcategorycollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Collections\V22\FeedRecentStoriesCollection;
use App\Http\Controllers\API\V24\FeedController as v24FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Feed;
use App\Models\SubCategory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v24FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
            $timezone = $user->timezone ?? config('app.timezone');
            $role     = getUserRole();

            if (!empty($subcategory) && $subcategory > 0) {
                $subcategoryData = SubCategory::find($subcategory);

                if (empty($subcategoryData)) {
                    return $this->notFoundResponse("Sorry! SubCategory data not found");
                }
            }

            $feedRecords = Feed::select(
                'feeds.*',
                'sub_categories.name as courseSubCategory',
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count')
            );

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

            $feedRecords->join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
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

            if ($type == 'watch') {
                $feedRecords->whereIn("feeds.type", [2, 3, 5]);
            } elseif ($type == 'listen') {
                $feedRecords->where("feeds.type", 1);
            } elseif ($type == 'read') {
                $feedRecords->where("feeds.type", 4);
            }

            if (!empty($subcategory) && $subcategory > 0) {
                $feedRecords = $feedRecords->where("feeds.sub_category_id", $subcategory);
            }

            $feedRecords->groupBy('feeds.id');

            if (!empty($subcategory) && $subcategory >= -1) {
                $feedRecords->orderBy('is_stick_count', 'ASC')
                    ->orderBy('feeds.updated_at', 'DESC')
                    ->orderBy('feeds.id', 'DESC');
            } else {
                $feedRecords->orderBy('view_count', 'DESC')
                    ->orderBy('feeds.updated_at', 'DESC');
            }

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
            $timezone = $user->timezone ?? config('app.timezone');

            $data['latestArticle'] = $this->getRecentFeedList('read');
            $data['mostListened']  = $this->getRecentFeedList('listen');
            $data['mostWatched']   = $this->getRecentFeedList('watch');
            $data['mostLiked']     = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(feed_user.liked),0) AS most_liked')
            )
                ->join('feed_company', function ($join) use ($company) {
                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                        ->where('feed_company.company_id', '=', $company->getKey());
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
    private function getRecentFeedList($type = 'read')
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count')
            )
                ->join('feed_company', function ($join) use ($company) {
                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                        ->where('feed_company.company_id', '=', $company->getKey());
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
            } else {
                $records->where("feeds.type", 4);
            }

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
}
