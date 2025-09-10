<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Collections\V21\EAPListCollection;
use App\Http\Controllers\API\V34\EAPController as v34EAPController;
use App\Models\Department;
use App\Models\EAP;
use App\Models\EAPIntroduction;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EAPController extends v34EAPController
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
            $user = $this->user();
            $company = $user->company()->first();
            //Get logged in user's department
            $userDepartment = $user->department()->first();
            $team = $user->teams()->select('teams.id', 'teams.name')->first();
            $userLocation = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();

            if (!$company->is_eap) {
                return $this->notFoundResponse('EAP not found');
            }
            $companyId = $company->id;
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            // find eap added by company
            // ->on('eap_order_priority.company_id', '=', 'eap_list.company_id')
            $caEap = EAP::select('eap_list.*', DB::raw('IFNULL(eap_order_priority.order_priority, 0) AS order_priority'));

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null) {
                $caEap->addSelect(DB::raw("CASE
                            WHEN eap_list.is_stick = 1 then 0
                            WHEN eap_list.company_id = " . $company->id . " then 1
                            WHEN eap_list.company_id IS NULL then 2
                            ELSE 3
                            END AS is_order"));
            }else{
                $caEap->addSelect(DB::raw("CASE
                            WHEN eap_list.is_stick = 1 then 0
                            ELSE 1
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
                ->leftjoin('eap_department', function ($join) {
                    $join->on('eap_list.id', '=', 'eap_department.eap_id');
                })
                ->groupby('eap_company.eap_id');
            //->where('eap_company.company_id', $company->id);

            /*if ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null) {
            $caEap->where('eap_company.company_id', $company->id);
            } else {*/
            if (!empty($userDepartment) || !empty($userLocation)) {
                $caEap->where(function ($query) use ($userDepartment, $userLocation, $company) {
                    $query->where(function ($subQuery) use ($userDepartment, $userLocation, $company) {
                        $subQuery
                            ->whereRaw('(FIND_IN_SET(?, (eap_list.departments)))', $userDepartment->id)
                            ->whereRaw('(FIND_IN_SET(?, (eap_list.locations)))', $userLocation->id)
                            ->whereNotNull('eap_list.departments')
                            ->whereNotNull('eap_list.locations');
                        //->where('eap_list.company_id', $company->id);

                        if ($company->parent_id != null) {
                            $subQuery->where(function ($innerSubQuery) use ($company) {
                                $innerSubQuery->where('eap_list.company_id', $company->id)
                                    ->orWhere('eap_list.company_id', $company->parent_id);
                            });
                        } else {
                            $subQuery->where('eap_list.company_id', $company->id);
                        }
                    });

                    $query->orWhere(function ($subQuery) use ($userLocation, $company) {
                        $subQuery
                            ->whereRaw('(FIND_IN_SET(?, (eap_list.locations)))', $userLocation->id)
                            ->whereNull('eap_list.departments');
                        // ->where('eap_list.company_id', $company->id);
                        if ($company->parent_id != null) {
                            $subQuery->where(function ($innerSubQuery) use ($company) {
                                $innerSubQuery->where('eap_list.company_id', $company->id)
                                    ->orWhere('eap_list.company_id', $company->parent_id);
                            });
                        } else {
                            $subQuery->where('eap_list.company_id', $company->id);
                        }
                    });

                    $query->orWhere(function ($subQuery1) use ($userDepartment, $userLocation, $company) {
                        $subQuery1->where(function ($subQuery1) use ($userDepartment, $userLocation) {
                            $subQuery1
                                ->whereNull('eap_list.company_id')
                                ->whereRaw('(FIND_IN_SET(?, (eap_list.departments)))', $userDepartment->id)
                                ->whereRaw('(FIND_IN_SET(?, (eap_list.locations)))', $userLocation->id)
                                ->whereNotNull('eap_list.departments')
                                ->whereNotNull('eap_list.locations');
                        });
                        $subQuery1->orWhere(function ($subQuery1) use ($userLocation) {
                            $subQuery1
                                ->whereNull('eap_list.company_id')
                                ->whereRaw('(FIND_IN_SET(?, (eap_list.locations)))', $userLocation->id)
                                ->whereNull('eap_list.departments');
                        });
                        $subQuery1->orWhere(function ($subQuery1) use ($userDepartment, $userLocation, $company) {
                            $subQuery1
                                ->where('eap_company.company_id', $company->id)
                                ->where('eap_department.location_id', $userLocation->id)
                                ->where('eap_department.department_id', $userDepartment->id)
                                ->whereNull('eap_list.departments')
                                ->whereNull('eap_list.locations');
                        });
                        $subQuery1->orWhere(function ($subQuery1) use ($company) {
                            $subQuery1
                                ->where('eap_company.company_id', $company->id)
                                ->whereNull('eap_department.location_id')
                                ->where('eap_list.is_rca', 0)
                                ->whereNull('eap_department.department_id');
                        });
                    });
                });
                /*}*/
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null) {
                $caEap->orderBy("is_order", 'ASC');
                $caEap->orderByDesc('eap_list.updated_at');
            } else {
                $caEap->orderBy("is_order", 'ASC');
                $caEap->orderByRaw('eap_order_priority.order_priority = 0 DESC, eap_order_priority.order_priority ASC');
            }

            $caEap = $caEap->get();

            // merger CA ans SA EAPs in to new collection
            $finalEapList = new Collection();
            $finalEapList = $finalEapList->merge($caEap);

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
                    'eapList' => [],
                ]], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
