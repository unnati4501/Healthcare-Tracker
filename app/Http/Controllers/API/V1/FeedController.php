<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\FeedCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FeedResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
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
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', '{$timezone}')"), '<=', now($timezone)->toDateTimeString())->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', '{$timezone}')"), '>=', now($timezone)->toDateTimeString())->orWhere('feeds.end_date', null);
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
     * like-un-like feed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlike(Request $request, Feed $feed)
    {
        try {
            $user = $this->user();

            $pivotExsisting = $feed->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $feed->getKey())->first();

            if (!empty($pivotExsisting)) {
                $liked = $pivotExsisting->pivot->liked;

                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;

                $pivotExsisting->pivot->save();

                if ($liked == 1) {
                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], 'Feed unliked successfully');
                } else {
                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], 'Feed liked successfully');
                }
            } else {
                $feed->feedUserLogs()->attach($user, ['liked' => true]);

                return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], 'Feed liked successfully');
            }
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
            $user = $this->user();

            $pivotExsisting = $feed->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $feed->getKey())->first();

            if (!empty($pivotExsisting)) {
                $saved = $pivotExsisting->pivot->saved;

                $pivotExsisting->pivot->saved = ($saved == 1) ? 0 : 1;

                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();

                $pivotExsisting->pivot->save();
                if ($saved == 1) {
                    return $this->successResponse([], 'Feed unsaved successfully');
                } else {
                    return $this->successResponse([], 'Feed saved successfully');
                }
            } else {
                $feed->feedUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);

                return $this->successResponse([], 'Feed saved successfully');
            }
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
        return $this->underMaintenanceResponse();
        try {
            $user = $this->user();

            $feedRecords = $user->feedLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('feed_user.saved_at', 'DESC')
                ->orderBy('feed_user.id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

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
