<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Controllers\API\V1\OnboardController as v1OnboardController;
use App\Http\Collections\V8\AppSlideCollection;
use App\Http\Collections\V8\GoalTagCollection;
use App\Http\Controllers\Controller;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class OnboardController extends v1OnboardController
{
    use ServesApiTrait;

    /**
     * Returns onboarding slides
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sliders(Request $request)
    {
        try {
            $type = "app";
            if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                $type = "portal";
            }

            $slideRecords = AppSlide::where('type', $type)->orderBy("order_priority", "ASC")->paginate(3);

            $goalObj = new Goal();
            $goalRecords = $goalObj->getAssociatedGoalTags();

            $data = array();

            $data['data']['sliders'] = ($slideRecords->count() > 0) ? new AppSlideCollection($slideRecords) : [];
            $data['data']['goals'] = ($goalRecords->count() > 0) ? new GoalTagCollection($goalRecords, []) : [];

            return $this->successResponse($data, 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
