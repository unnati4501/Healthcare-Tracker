<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Http\Collections\V12\WebinarListCollection;
use App\Http\Controllers\API\V12\WebinarController as v12WebinarController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class WebinarController extends v12WebinarController
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

            $webinarExploreData = Webinar::select('webinar.*', DB::raw("(SELECT count(view_count) FROM webinar_user WHERE webinar_id = `webinar`.`id`) AS webinar_view_count"))
                ->join('webinar_category', function ($join) {
                    $join->on('webinar_category.webinar_id', '=', 'webinar.id');
                })
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                        ->where('webinar_company.company_id', $company->id);
                });

            if ($subcategory > 0) {
                $webinarExploreData->where('webinar_category.sub_category_id', $subcategory)
                    ->orderByRaw("`webinar`.`updated_at` DESC");
            } else {
                $webinarExploreData->orderByRaw("`webinar_view_count` DESC, `webinar`.`updated_at` DESC");
            }

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
}
