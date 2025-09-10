<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Controllers\API\V14\WebinarController as v14WebinarController;
use App\Http\Collections\V30\RecentWebinarCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class WebinarController extends v14WebinarController
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
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.most_liked_webinar_limit');

            $data['recentWebinars'] = $this->getRecentWebinarList();
            $data['mostPlayed']    = $this->getRecentWebinarList("watch");
            $data['mostLiked']     = Webinar::select(
                'webinar.*',
                "sub_categories.name as webinarSubCategory",
                DB::raw('IFNULL(sum(webinar_user.liked),0) AS most_liked')
            )
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar.id', '=', 'webinar_company.webinar_id')
                        ->where('webinar_company.company_id', '=', $company->getKey());
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
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.recent_webinar_limit');

            $records = Webinar::select(
                'webinar.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(webinar_user.view_count),0) AS view_count')
            )
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar.id', '=', 'webinar_company.webinar_id')
                        ->where('webinar_company.company_id', '=', $company->getKey());
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
}
