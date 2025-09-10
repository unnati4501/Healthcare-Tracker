<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Collections\V6\EAPListCollection;
use App\Http\Controllers\API\V6\EAPController as v6EAPController;
use App\Http\Resources\V6\EAPDetailResource as v6EAPDetailResource;
use App\Models\EAP;
use App\Models\EAPIntroduction;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EAPController extends v6EAPController
{
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
            $company   = $user->company()->first();
            if (!$company->is_eap) {
                return $this->notFoundResponse('EAP not found');
            }
            $companyId = $company->id;
            $role      = getUserRole();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            // find eap added by company
            // ->on('eap_order_priority.company_id', '=', 'eap_list.company_id')
            $caEap = EAP::select('eap_list.*', DB::raw('IFNULL(eap_order_priority.order_priority, 0) AS order_priority'));

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null) {
                $caEap->addSelect(DB::raw("CASE
                            WHEN eap_list.company_id = " . $company->id . " then 0
                            WHEN eap_list.company_id IS NULL then 1
                            ELSE 2
                            END AS is_order"));
            }

            $caEap->leftjoin('eap_order_priority', function ($join) use ($companyId) {
                $join
                    ->on('eap_order_priority.eap_id', '=', 'eap_list.id')
                    ->where('eap_order_priority.company_id', '=', $companyId);
            })
                ->leftjoin('eap_company', function ($join) {
                    $join->on('eap_company.eap_id', '=', 'eap_list.id');
                })
                ->groupby('eap_company.eap_id')
                ->where('eap_company.company_id', $company->id);

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null) {
                $caEap->orderBy("is_order", 'ASC');
                $caEap->orderByDesc('eap_list.updated_at');
            } else {
                $caEap->orderByRaw('eap_order_priority.order_priority = 0 DESC, eap_order_priority.order_priority ASC');
            }

            $caEap = $caEap->get();

            // find eap added by admin
            // $saEap = EAP::whereNull('company_id')->orderByDesc('updated_at')->get();

            // merger CA ans SA EAPs in to new collection
            $finalEapList = new Collection();
            $finalEapList = $finalEapList->merge($caEap);
            // $finalEapList = $finalEapList->merge($saEap);
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            // Update view count for all listing records ( max = 2 )
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                if ($finalEapList) {
                    $eapIds = $finalEapList->pluck('id')->toArray();
                    $this->updateViewCount($eapIds);
                }
            }

            // check if eap are present or not
            if ($finalEapList->isNotEmpty()) {
                // collect required data and return response
                return $this->successResponse(new EAPListCollection($finalEapList), 'EAP listed successfully');
            } else {
                // if no any eap are present then pass eap introduction only in response
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
     * To Update view count for listing records
     * @param array $ids
     * @return bool
     */
    public function updateViewCount($ids = array())
    {
        $user = auth()->user();
        foreach ($ids as $key => $value) {
            $modelData = EAP::find($value);

            if (!empty($modelData)) {
                $pivotExsisting = $modelData->eapUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('eap_id', $value)->first();

                $updateCount = false;
                if (!empty($pivotExsisting)) {
                    if ($pivotExsisting->pivot->view_count < 2) {
                        $pivotExsisting->pivot->view_count = $pivotExsisting->pivot->view_count + 1;
                        $pivotExsisting->pivot->save();
                        $updateCount = true;
                    }
                } else {
                    $modelData->eapUserLogs()->attach($user, ['view_count' => 1]);
                    $updateCount = true;
                }

                return $updateCount;
            }
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
            $user      = $this->user();
            $company   = $user->company()->first();

            if (!$company->is_eap) {
                return $this->notFoundResponse('EAP not found');
            }

            return $this->successResponse(['data' => new v6EAPDetailResource($eap)], 'EAP detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
