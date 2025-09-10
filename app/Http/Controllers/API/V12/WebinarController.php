<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V12;

use App\Http\Collections\V12\WebinarListCollection;
use App\Http\Controllers\API\V11\WebinarController as v11WebinarController;
use App\Http\Resources\V12\WebinarListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebinarController extends v11WebinarController
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
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                        ->where('webinar_company.company_id', $company->id);
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
            $user    = $this->user();
            $company = $user->company()->first();
            // Check webinar available with this company or not
            $checkWebinar = $webinar->webinarcompany()->where('company_id', $company->id)->count();

            if ($checkWebinar <= 0) {
                return $this->notFoundResponse('Webinar not found');
            }

            return $this->successResponse(['data' => new WebinarListResource($webinar)], 'Webinar details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * save-un-save Webinar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsave(Request $request, Webinar $webinar)
    {
        try {
            $user = $this->user();

            $pivotExsisting = $webinar
                ->webinarUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('webinar_id', $webinar->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = (($saved == 1) ? 0 : 1);
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    return $this->successResponse([], trans('api_messages.webinar.unsaved'));
                } else {
                    return $this->successResponse([], trans('api_messages.webinar.saved'));
                }
            } else {
                $webinar
                    ->webinarUserLogs()
                    ->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
                return $this->successResponse([], trans('api_messages.webinar.saved'));
            }
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
            $feedRecords = $user->webinarLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                        ->where('webinar_company.company_id', $company->id);
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('webinar_user.saved_at', 'DESC')
                ->orderBy('webinar_user.id', 'DESC')
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
}
