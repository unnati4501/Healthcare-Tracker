<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V29;

use App\Http\Collections\V29\FeedCardListingCollection;
use App\Http\Collections\V29\FeedRecentStoriesCollection;
use App\Http\Controllers\API\V27\FeedController as v27FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Feed;
use App\Models\SubCategory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v27FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get feed listing with all sub-categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cardListingStories(Request $request, $type = 'read')
    {
        try {
            $data          = array();
            $subCategories = $this->subCategoriesRecords($type);

            foreach ($subCategories as $key => $categories) {
                if ($categories['short_name'] == 'recently_added') {
                    $records = $this->getFeedRecords($type);
                } elseif ($categories['short_name'] == 'most_popular') {
                    $records = $this->getFeedRecords($type);
                } else {
                    $records = $this->getFeedRecords($type, $categories['id']);
                }
                if ($records->count() > 0) {
                    $tempArray = [
                        'category' => $categories,
                        'data'     => $records,
                    ];
                    $data[] = new FeedCardListingCollection($tempArray);
                }
            }

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get feed records
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getFeedRecords($type = 'read', $subcategory = null)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $role     = getUserRole($user);

            $records = Feed::select(
                'feeds.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count')
            );
            if ($role->group == 'company' && is_null($company->parent_id) && !$company->is_reseller) {
                $records->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
            } else {
                if ($company->parent_id == null && $company->is_reseller) {
                    $records->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                } elseif (!is_null($company->parent_id)) {
                    $records->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                            ELSE 3
                            END AS is_stick_count"));
                } else {
                    $records->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                }
            }
            $records->join('feed_company', function ($join) use ($company) {
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

            if (!is_null($subcategory)) {
                $records->where("feeds.sub_category_id", $subcategory);
            }

            $records = $records->orderBy('feeds.updated_at', 'DESC')
                ->orderBy('feeds.id', 'DESC')
                ->groupBy('feeds.id')
                ->limit(10)
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
     * Get Feed Sub-categories based on request type [Read, Listen, Watch]
     * Internal use only
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function subCategoriesRecords($type = 'read')
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $category = Category::where('id', 2)->first();
            $records  = SubCategory::where('category_id', 2)
                ->orderBy('is_excluded', 'DESC')
                ->get();
            $customCategory = array();

            $records = $records->filter(function ($item, $key) use ($category) {
                if ($category->short_name == 'group') {
                    if ($item->short_name == 'public') {
                        return true;
                    }
                    return $item->groups()
                        ->where('is_archived', 0)
                        ->where('is_visible', 1)
                        ->count() > 0;
                } else {
                    return true;
                }
            });

            $customCategoryExtra = array(0 => "Recently Added");

            if ($type == 'watch') {
                $customCategory = array(-3 => "Most Popular");
            } elseif ($type == 'listen') {
                $customCategory = array(-2 => "Most Popular");
            } else {
                $customCategory = array(-1 => "Most Popular");
            }

            $records = $customCategoryExtra + $customCategory + $records->pluck("name", "id")->toArray();

            $newArray = array_map(function ($id, $name) {
                return array(
                    'id'         => $id,
                    'name'       => $name,
                    'short_name' => str_replace(' ', '_', strtolower($name)),
                );
            }, array_keys($records), $records);

            return $newArray;
        } catch (\Exception $e) {
            \DB::rollback();
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

            $data['recentStories'] = $this->getRecentFeedList();
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
    private function getRecentFeedList($type = "")
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
            } elseif ($type == 'read') {
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
