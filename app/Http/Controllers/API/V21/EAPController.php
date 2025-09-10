<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V21;

use App\Http\Collections\V21\EAPListCollection;
use App\Http\Controllers\API\V18\EAPController as v18EAPController;
use App\Models\EAP;
use App\Models\EAPIntroduction;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EAPController extends v18EAPController
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
            $user    = $this->user();
            $company = $user->company()->first();
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

            // check if eap are present or not
            if ($finalEapList->isNotEmpty()) {
                // collect required data and return response
                return $this->successResponse(new EAPListCollection($finalEapList), 'EAP listed successfully');
            } else {
                // if no any eap are present then pass eap introduction only in response
                $introduction = EAPIntroduction::where('company_id', $company->id)->first();
                if (empty($introduction) || (!empty($introduction) && $introduction->introduction == null)) {
                    $introduction = EAPIntroduction::find(1);
                }
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
}
