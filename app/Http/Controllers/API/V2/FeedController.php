<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V2;

use App\Http\Collections\V2\FeedCollection;
use App\Http\Controllers\API\V1\FeedController as v1FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v1FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get feed listing accroding to current time and user expertise level
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            return $this->updateAppResponse();

            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            //\DB::enableQueryLog();

            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            })
                ->join('feed_expertise_level', function ($join) {
                    $join->on('feeds.id', '=', 'feed_expertise_level.feed_id');
                })
            // ->join('user_expertise_level',function($join) use($user) {
            //     $join->on('user_expertise_level.category_id', '=', 'feed_expertise_level.category_id')
            //     ->on('user_expertise_level.expertise_level', '=', 'feed_expertise_level.expertise_level')
            //     ->where('user_expertise_level.user_id', '=', $user->getKey());
            // })
                ->select('feeds.*')
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            if (!empty($request->expertise)) {
                $feedRecords = $feedRecords->whereIn("feed_expertise_level.expertise_level", $request->expertise);
            }

            if (!empty($request->insights)) {
                $feedRecords = $feedRecords->whereIn("feeds.tag", $request->insights);
            }

            $feedRecords = $feedRecords->groupBy('feeds.id')
                ->orderByDesc('feeds.updated_at')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            //dd(\DB::getQueryLog());

            return $this->successResponse(
                ($feedRecords->count() > 0) ? new FeedCollection($feedRecords) : ['data' => []],
                ($feedRecords->count() > 0) ? 'Feed List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
