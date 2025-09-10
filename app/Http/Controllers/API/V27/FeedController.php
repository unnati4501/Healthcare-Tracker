<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Collections\V4\SubCategoryCollection as v4subcategorycollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Controllers\API\V26\FeedController as v26FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Feed;
use App\Models\SubCategory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v26FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get Feed Sub-categories based on request type [Read, Listen, Watch]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subCategories(Request $request, $type = 'read')
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

            if ($type == 'watch') {
                $customCategory = array(-3 => "Most Watched");
            } elseif ($type == 'listen') {
                $customCategory = array(-2 => "Most Listened");
            } else {
                $customCategory = array(-4 => "Most Read");
            }

            $records = array(-1 => "All") + $customCategory + $records->pluck("name", "id")->toArray();

            $new_array = array_map(function ($id, $name) {
                return array(
                    'id'         => $id,
                    'name'       => $name,
                    'short_name' => str_replace(' ', '_', strtolower($name)),
                );
            }, array_keys($records), $records);

            $records = SubCategory::hydrate($new_array);

            return $this->successResponse(($records->count() > 0) ? new v4subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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
                $feedRecords->havingRaw(\DB::raw("sum(feed_user.view_count) > 0 "));
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
}
