<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\AppSlideCollection;
use App\Http\Controllers\Controller;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends Controller
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
            $slideRecords = AppSlide::paginate(3);

            return $this->successResponse(
                ($slideRecords->count() > 0) ? new AppSlideCollection($slideRecords) : ['data' => []],
                ($slideRecords->count() > 0) ? 'Sliders retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
