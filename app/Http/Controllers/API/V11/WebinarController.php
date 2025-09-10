<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Collections\V11\WebinarListCollection;
use App\Http\Resources\V11\WebinarListResource;

class WebinarController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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

            $webinarExploreData = Webinar::select('webinar.*')
                ->join('webinar_category', function ($join) {
                    $join->on('webinar_category.webinar_id', '=', 'webinar.id');
                })
                ->where('webinar_category.sub_category_id', $subcategory)
                ->orderByRaw("`webinar`.`updated_at` DESC")
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

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
            return $this->successResponse(['data' => new WebinarListResource($webinar)], 'Webinar details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to liked unliked Webinar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeWebinar(Request $request, Webinar $webinar)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();

            $message        = trans('api_messages.webinar.liked');
            $pivotExsisting = $webinar
                ->webinarUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('webinar_id', $webinar->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    $message = trans('api_messages.webinar.unliked');
                }
            } else {
                $webinar
                    ->webinarUserLogs()
                    ->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $webinar->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
