<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\EAPListCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V6\EAPDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\EAP;
use App\Models\EAPIntroduction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EAPController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * List all the eap based on user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user      = $this->user();
            $companyId = $user->company()->first()->id;
            $EAPData   = EAP::where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->orderBy('company_id', 'DESC')
            ->orderBy('updated_at', 'DESC');

            if ($EAPData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new EAPListCollection($EAPData->get()), 'EAP listed successfully');
            } else {
                $introduction = EAPIntroduction::find(1);

                // return empty response
                return $this->successResponse(['data' => [
                    'introduction' => ($introduction->introduction ?? ''),
                    'eapList'      => [],
                ]], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recipe details by id
     *
     * @param Request $request, EAP $eap
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, EAP $eap)
    {
        try {
            return $this->successResponse(['data' => new EAPDetailResource($eap)], 'EAP detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
