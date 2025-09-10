<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V7;

use App\Http\Collections\V7\FeedListCollection;
use App\Http\Controllers\API\V6\FeedController as v6FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v6FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get feed listing accroding to current time and user expertise level
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $subcategory = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            if (!empty($subcategory)) {
                $subcategoryData = SubCategory::find($subcategory);
                if (empty($subcategoryData)) {
                    return $this->notFoundResponse("Sorry! SubCategory data not found");
                }
            }

            $feedRecords = Feed::select('feeds.*', "sub_categories.name as courseSubCategory")
                ->join('feed_company', function ($join) use ($company) {
                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                        ->where('feed_company.company_id', '=', $company->getKey());
                })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            if (!empty($subcategory)) {
                $feedRecords = $feedRecords->where("feeds.sub_category_id", $subcategory);
            }

            $feedRecords = $feedRecords
                ->groupBy('feeds.id')
                ->orderBy('feeds.is_stick', 'DESC')
                ->orderBy('feeds.id', 'DESC')
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
     * get saved feed listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            $user = $this->user();

            $feedRecords = $user->feedLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('feed_user.saved_at', 'DESC')
                ->orderBy('feed_user.id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($feedRecords->count() > 0) ? new FeedListCollection($feedRecords) : ['data' => []],
                ($feedRecords->count() > 0) ? 'Feed List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
