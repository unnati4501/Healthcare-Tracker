<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V14;

use App\Http\Controllers\API\V13\WebinarController as v13WebinarController;
use App\Http\Resources\V12\WebinarListResource;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebinarController extends v13WebinarController
{
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
}
