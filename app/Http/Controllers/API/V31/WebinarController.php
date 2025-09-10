<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V31;

use App\Http\Collections\V31\WebinarListCollection;
use App\Http\Collections\V31\RecentWebinarCollection;
use App\Http\Controllers\API\V30\WebinarController as v30WebinarController;
use App\Http\Resources\V31\WebinarListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebinarController extends v30WebinarController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Get recent webinars
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentWebinars(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.most_liked_webinar_limit');

            $data['recentWebinars'] = $this->getRecentWebinarList();
            $data['mostPlayed']     = $this->getRecentWebinarList("watch");
            $data['mostLiked']      = Webinar::select(
                'webinar.*',
                "sub_categories.name as webinarSubCategory",
                DB::raw('IFNULL(sum(webinar_user.liked),0) AS most_liked')
            )
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('webinar_user', 'webinar_user.webinar_id', '=', 'webinar.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->orderBy('most_liked', 'DESC')
                ->groupBy('webinar.id')
                ->having('most_liked', '>', '0')
                ->limit($limit)
                ->get();
            // Collect required data and return response
            return $this->successResponse(new RecentWebinarCollection($data), 'recent webinars listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent webinar [Recent, Most Played, Guided]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentWebinarList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.recent_webinar_limit');

            $records = Webinar::select(
                'webinar.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(webinar_user.view_count),0) AS view_count')
            )
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('webinar_user', 'webinar_user.webinar_id', '=', 'webinar.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                });

            if ($type == 'watch') {
                $records->orderBy('view_count', 'DESC')
                    ->orderBy('webinar.updated_at', 'DESC');
            } else {
                $records->orderBy('webinar.updated_at', 'DESC');
            }
            $records->orderBy('webinar.updated_at', 'DESC');
            $records = $records->groupBy('webinar.id')
                ->limit($limit)
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
     * List all the webinar based on user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($subcategory, Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();
            
            if ($subcategory > 0) {
                $subcatData = SubCategory::find($subcategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $webinarExploreData = Webinar::select('webinar.*', DB::raw("(SELECT count(view_count) FROM webinar_user WHERE webinar_id = `webinar`.`id`) AS webinar_view_count"))
                ->join('webinar_category', function ($join) {
                    $join->on('webinar_category.webinar_id', '=', 'webinar.id');
                })
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                });

            if ($subcategory > 0) {
                $webinarExploreData->where('webinar_category.sub_category_id', $subcategory)
                    ->orderByRaw("`webinar`.`updated_at` DESC");
            } elseif ($subcategory == 0) {
                $webinarExploreData->join('webinar_user', 'webinar.id', '=', 'webinar_user.webinar_id')
                        ->join('sub_categories', 'sub_categories.id', '=', 'webinar_category.sub_category_id')
                        ->where("webinar_user.user_id", $user->id)
                        ->where(["favourited" => 1, "sub_categories.status" => 1])
                        ->orderByRaw("`webinar_view_count` DESC, `webinar`.`updated_at` DESC");
            } else {
                $webinarExploreData->orderByRaw("`webinar_view_count` DESC, `webinar`.`updated_at` DESC");
            }
            $webinarExploreData = $webinarExploreData->groupBy("webinar.id");
            $webinarExploreData = $webinarExploreData->paginate(config('zevolifesettings.datatable.pagination.short'));
            
            if ($webinarExploreData->count() > 0) {
                // Collect required data and return response
                return $this->successResponse(new WebinarListCollection($webinarExploreData), 'Webinar listed successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get webinar details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, Webinar $webinar)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();

            // Check webinar available with this company or not
            $checkWebinar = $webinar->webinarteam()->where('team_id', $team->id)->count();

            if ($checkWebinar <= 0) {
                return $this->notFoundResponse('Webinar not found');
            }

            if (!is_null($company)) {
                $webinar->rewardPortalPointsToUser($user, $company, 'webinar');
            }

            return $this->successResponse([
                'data' => new WebinarListResource($webinar),
            ], 'Webinar details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * get saved webinar listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->first();
            $team        = $user->teams()->first();
            $feedRecords = $user->webinarLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('webinar_user.saved_at', 'DESC')
                ->orderBy('webinar_user.id', 'DESC')
                ->groupBy('webinar.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($feedRecords->count() > 0) ? new WebinarListCollection($feedRecords) : ['data' => []],
                ($feedRecords->count() > 0) ? 'Webinar List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to favorited unfavourited Webinar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouriteWebinar(Request $request, Webinar $webinar)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.webinar.favorited');
            $pivotExsisting = $webinar
                ->webinarUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('webinar_id', $webinar->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                             = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited      = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at   = now()->toDateTimeString();
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.webinar.unfavorited');
                }
            } else {
                $webinar
                    ->webinarUserLogs()
                    ->attach($user, ['favourited' => true, 'favourited_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
