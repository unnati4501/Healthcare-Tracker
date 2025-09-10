<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V37\WebinarController as v37WebinarController;
use App\Http\Collections\V38\WebinarListCollection;
use App\Http\Collections\V38\RecentWebinarCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class WebinarController extends v37WebinarController
{
    use ServesApiTrait, ProvidesAuthGuardTrait, PaginationTrait;

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
            $webinarExploreData->addSelect(DB::raw("CASE
                WHEN webinar.caption = 'New' then 0
                WHEN webinar.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"
            ));
            if ($subcategory > 0) {
                $webinarExploreData->where('webinar_category.sub_category_id', $subcategory)
                    ->orderBy('caption_order', 'ASC')
                    ->orderByRaw("`webinar`.`updated_at` DESC");
            } elseif ($subcategory == 0) {
                $webinarExploreData->join('webinar_user', 'webinar.id', '=', 'webinar_user.webinar_id')
                        ->join('sub_categories', 'sub_categories.id', '=', 'webinar_category.sub_category_id')
                        ->where("webinar_user.user_id", $user->id)
                        ->where(["favourited" => 1, "sub_categories.status" => 1])
                        ->orderBy('caption_order', 'ASC')
                        ->orderByRaw("`webinar_view_count` DESC, `webinar`.`updated_at` DESC");
            } else {
                $webinarExploreData->orderBy('caption_order', 'ASC')
                ->orderByRaw("`webinar_view_count` DESC, `webinar`.`updated_at` DESC");
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
     * Get recent webinars
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentWebinars(Request $request)
    {
        try {
            $user     = $this->user();
            $team     = $user->teams()->first();

            $data     = Webinar::select(
                'webinar.*',
                "sub_categories.name as webinarSubCategory",
                DB::raw('IFNULL(sum(webinar_user.liked),0) AS most_liked'),
                DB::raw('IFNULL(sum(webinar_user.view_count),0) AS view_count'),
            );
            $data->addSelect(DB::raw("CASE
                WHEN webinar.caption = 'New' then 0
                WHEN webinar.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"
            ));
            $data = $data->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('webinar_user', 'webinar_user.webinar_id', '=', 'webinar.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->orderBy('caption_order', 'ASC')
                ->orderBy('webinar.updated_at', 'DESC')
                ->orderBy('webinar.id', 'DESC')
                ->groupBy('webinar.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            // Collect required data and return response
            if ($data->count() > 0) {
                return $this->successResponse(new RecentWebinarCollection($data, true), 'Webinars Retrieved Successfully');
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
     * get saved webinar listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            $user           = $this->user();
            $team           = $user->teams()->first();
            $webinarRecords = $user->webinarLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                });

            $webinarRecords->addSelect(DB::raw("CASE
                WHEN webinar.caption = 'New' then 0
                WHEN webinar.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"
            ));
            $webinarRecords = $webinarRecords->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('caption_order', 'ASC')
                ->orderBy('webinar_user.saved_at', 'DESC')
                ->orderBy('webinar_user.id', 'DESC')
                ->groupBy('webinar.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($webinarRecords->count() > 0) ? new WebinarListCollection($webinarRecords) : ['data' => []],
                ($webinarRecords->count() > 0) ? 'Webinar List retrieved successfully.' : 'No results'
            );

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
