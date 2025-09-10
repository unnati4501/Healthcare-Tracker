<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\FeedListCollection;
use App\Http\Controllers\API\V5\FeedController as v5FeedController;
use App\Http\Resources\V6\FeedResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends v5FeedController
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
     * Get feed details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, Feed $feed)
    {
        try {
            return $this->successResponse(['data' => new FeedResource($feed)], 'Feed details retrived successfully');
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

    /**
     * save-un-save feed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsave(Request $request, Feed $feed)
    {
        try {
            $user           = $this->user();
            $pivotExsisting = $feed
                ->feedUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('feed_id', $feed->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = (($saved == 1) ? 0 : 1);
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    return $this->successResponse([], trans('api_messages.feed.unsaved'));
                } else {
                    return $this->successResponse([], trans('api_messages.feed.saved'));
                }
            } else {
                $feed
                    ->feedUserLogs()
                    ->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
                return $this->successResponse([], trans('api_messages.feed.saved'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * like-un-like feed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlike(Request $request, Feed $feed)
    {
        try {
            $user           = $this->user();
            $pivotExsisting = $feed
                ->feedUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('feed_id', $feed->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = (($liked == 1) ? 0 : 1);
                $pivotExsisting->pivot->save();

                if ($liked == 1) {
                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.unliked'));
                } else {
                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.liked'));
                }
            } else {
                $feed
                    ->feedUserLogs()
                    ->attach($user, ['liked' => true]);
                return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.liked'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
