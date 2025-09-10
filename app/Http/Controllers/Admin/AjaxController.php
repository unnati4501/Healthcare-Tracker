<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use App\Models\Company;
use App\Models\Country;
use App\Models\Course;
use App\Models\Department;
use App\Models\CompanyLocation;
use App\Models\HsCategories;
use App\Models\Industry;
use App\Models\Role;
use App\Models\SurveyCategory;
use App\Models\Team;
use App\Models\Timezone;
use App\Models\User;
use App\Models\ZcSurvey;
use App\Models\CpFeatures;
use App\Models\CpPlan;
use App\Models\TempDigitalTherapySlots;
use App\Models\DigitalTherapySlots;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\CompanyBranding;
use App\Models\DigitalTherapySpecific;

/**
 * Class AjaxController
 *
 * @package App\Http\Controllers\Admin
 */
class AjaxController extends Controller
{
    use ServesApiTrait;

    /**
     * @param Country $country
     *
     * @return JsonResponse
     */
    public function getStates(Country $country): JsonResponse
    {
        $returnArr = [];
        $records   = $country->states;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Country $country
     *
     * @return JsonResponse
     */
    public function getTimezones(Country $country): JsonResponse
    {

        $returnArr = [];
        $records   = Timezone::where('country_code', $country->sortname)->get();

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->name;
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function getDepartments(Company $company): JsonResponse
    {

        $returnArr = [];
        $records   = $company->departments;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Department $department
     *
     * @return JsonResponse
     */
    public function getTeams(Department $department): JsonResponse
    {

        $returnArr = [];
        $records   = $department->teams;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * To get team list limit wise
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function getLimitWiseTeams(Department $department, $currTeam = null): JsonResponse
    {
        $data    = [];
        $company = $department->company()->select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')->first();
        $department
            ->teams()
            ->select('teams.id', 'teams.name', 'teams.default')
            ->when($company->auto_team_creation, function ($query) use ($company, $currTeam) {
                $query
                    ->withCount('users')
                    ->having('users_count', '<', $company->team_limit, 'or')
                    ->having('teams.default', '=', true, 'or');
                if (!is_null($currTeam)) {
                    $query->having('teams.id', '=', $currTeam, 'or');
                }
            })
            ->get()
            ->each(function ($team) use (&$data) {
                $data[] = [
                    'id'   => $team->id,
                    'name' => $team->name,
                ];
            });
        return $this->successResponse($data);
    }

    /**
     * @param Department $department
     *
     * @return JsonResponse
     */
    public function getDepartmentLocations(Department $department): JsonResponse
    {

        $returnArr = [];
        $records   = $department->departmentlocations;
        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function getCompanyLocations(Company $company): JsonResponse
    {
        $returnArr = [];
        $records   = $company->locations;
        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param String $group
     *
     * @return JsonResponse
     */
    public function getRoles(String $group): JsonResponse
    {
        $records = Role::where('group', $group)->whereNotIn('slug', ['user'])->get();

        $returnArr = [];
        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param String $group
     *
     * @return JsonResponse
     */
    public function getBadges(Request $request): JsonResponse
    {
        $returnArr = [];
        $records   = array();
        $companyId = !is_null(\Auth::user()->company->first()) ? \Auth::user()->company->first()->id : null;

        if ($request->get('challenge_category') == '2') {
            if (!empty($request->get('target_type'))) {
                $records = Badge::where("challenge_target_id", $request->get('target_type'))
                    ->where("type", "challenge")
                    ->where(function ($query) use ($companyId) {
                        $query->whereNull("company_id");
                        if (!empty($companyId)) {
                            $query->orWhere("company_id", $companyId);
                        }
                    })
                    ->get();
                if (!empty($records) && $records->count() > 0) {
                    foreach ($records as $key => $record) {
                        $returnArr[$key]['id']   = $record->getKey();
                        $returnArr[$key]['name'] = $record->title;
                    }
                }
            }
        } else {
            $target_units  = (!empty($request->get('target_units'))) ? $request->get('target_units') : 0;
            $target_units1 = (!empty($request->get('target_units1'))) ? $request->get('target_units1') : 0;

            $target_type  = (!empty($request->get('target_type'))) ? $request->get('target_type') : 0;
            $target_type1 = (!empty($request->get('target_type1'))) ? $request->get('target_type1') : 0;

            if (!empty($request->get('target_type')) || !empty($request->get('target_type1'))) {
                $records = Badge::where("type", "challenge")
                    ->where(function ($query) use ($companyId) {
                        $query->whereNull("company_id");
                        if (!empty($companyId)) {
                            $query->orWhere("company_id", $companyId);
                        }
                    });
                $records = $records->where(function ($query) use ($target_type, $target_type1, $target_units, $target_units1) {
                    $query->where(function ($subQuery) use ($target_type, $target_units) {
                        $subQuery->where("challenge_target_id", $target_type)
                            ->where("target", "<=", $target_units);
                    })->orWhere(function ($subQuery1) use ($target_type1, $target_units1) {
                        $subQuery1->where("challenge_target_id", $target_type1)
                            ->where("target", "<=", $target_units1);
                    });
                })->get();

                if (!empty($records) && $records->count() > 0) {
                    foreach ($records as $key => $record) {
                        $returnArr[$key]['id']   = $record->getKey();
                        $returnArr[$key]['name'] = $record->title;
                    }
                }
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param String $group, Role $role
     *
     * @return JsonResponse
     */
    public function getPermissions(String $group, Role $role): JsonResponse
    {
        $permissionData = $role->getPermissionData($group);

        return response()->json([
            'body' => view('admin.roles.permissionLists', compact('permissionData'))->render(),
        ]);
    }

    /**
     * @param HsCategories
     *
     * @return JsonResponse
     */
    public function getHsSubCategories(HsCategories $hsCategories): JsonResponse
    {
        $returnArr = [];
        $records   = $hsCategories->hsSubCategories->where('status', 1);

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->display_name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function getCompanyTeams(Company $company): JsonResponse
    {

        $returnArr = [];
        $records   = $company->teams;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Team $team
     *
     * @return JsonResponse
     */
    public function getTeamMembers(Team $team): JsonResponse
    {

        $returnArr = [];
        $records   = $team->users;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['value'] = $record->getKey();
                $returnArr[$key]['user']  = "$record->first_name $record->last_name($record->email)";
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param SurveyCategory $surveyCategory
     *
     * @return JsonResponse
     */
    public function getZcSubCategories(SurveyCategory $surveyCategory): JsonResponse
    {
        $returnArr = [];
        $records   = $surveyCategory->subcategories;

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->display_name;
            }
        }

        return $this->successResponse($returnArr);
    }

    public function getSurveys(Request $request)
    {
        $surveysList = "";
        $surveys     = ZcSurvey::where('status', '!=', 'Draft');
        if ($request->hasPremium == "false") {
            $surveys->where('is_premium', false);
        }
        $surveys = $surveys->get();
        $surveys->each(function ($survey) use (&$surveysList) {
            $surveysList .= "<option value='" . $survey->id . "'>" . (($survey->is_premium) ? "⭐" : "") . " " . $survey->title . "</option>";
        });
        return $surveysList;
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function getCompany(Industry $industry): JsonResponse
    {

        $role        = getUserRole();
        $companyData = Auth::user()->company->first();
        $records     = Company::select('id', 'name');
        if (isset($industry->id)) {
            $records->where('industry_id', $industry->id);
        }
        if ($role->group == 'reseller' && $companyData->parent_id == null) {
            $records->where(function ($where) use ($companyData) {
                $where->where('id', $companyData->id)
                    ->orWhere('parent_id', $companyData->id);
            });
        }
        $records = $records->get();

        $returnArr = [];

        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->id;
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function showMeditationHours(Company $company): JsonResponse
    {
        $meditationHoursFlag = true;
        if (!empty($company)) {
            if ($company->is_reseller == 1) {
                $meditationHoursFlag = false;
            } elseif ($company->is_reseller == 0 && !is_null($company->parent_id) && $company->allow_app == 0) {
                $meditationHoursFlag = false;
            }
        }
        $returnArr = ['flag' => $meditationHoursFlag];
        return $this->successResponse($returnArr);
    }

    /**
     * Check Content Validation
     *
     * @return array
     **/
    public function checkContentValidation(Request $request)
    {
        $content      = $request->content;
        $type         = config('zevolifesettings.company_content_master_type');
        $courseCount  = \App\Models\Course::select('id')->count();
        $trackCount   = \App\Models\MeditationTrack::select('id')->count();
        $webinarCount = \App\Models\Webinar::select('id')->count();
        $feedCount    = \App\Models\Feed::select('id')->count();
        $recipeCount  = \App\Models\Recipe::select('id')->count();

        if ($courseCount <= 0) {
            unset($type[1]);
        }
        if ($trackCount <= 0) {
            unset($type[4]);
        }
        if ($webinarCount <= 0) {
            unset($type[7]);
        }
        if ($feedCount <= 0) {
            unset($type[2]);
        }
        if ($recipeCount <= 0) {
            unset($type[5]);
        }

        foreach ($content as $value) {
            $splitValue  = explode('-', $value);
            $masterId    = $splitValue[0];
            $tempArray[] = $masterId;
        }
        $typeKey     = array_keys($type);
        $uniqueArray = array_unique($tempArray);
        $diffArray   = array_diff($typeKey, $uniqueArray);
        if (count($diffArray) == 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param $subcategory
     * @param $course
     * @param Request $request
     * @return String
     */
    public function getCategorywiseMasterclasses($subcategory, Course $course, Request $request)
    {
        $list = "";
        $course->select('title', 'id')
            ->when($subcategory, function ($query, $subcategory) {
                $query->where('sub_category_id', $subcategory);
            })
            ->get()
            ->each(function ($item) use (&$list) {
                $list .= "<option value='{$item->id}'>{$item->title}</option>";
            });
        return $list;
    }

    /**
     * @param Request $request
     * @return String
     */
    public function checkEmailExists(Request $request)
    {
        $email     = $request->email;
        $disposableEmailFlag = false;
        $appEnvironment = app()->environment();
        if($appEnvironment == 'performance' || $appEnvironment == 'preprod' || $appEnvironment == 'uat' || $appEnvironment == 'production') {
            $disposableEmail = config('emaildisposable');
            $emailExtenstion = strtolower(explode('@', $email)[1]);
            if (in_array($emailExtenstion, $disposableEmail)) {
                $disposableEmailFlag = true;
            }
        }
        
        $usersData = User::where('users.email', '=', $email);
        if (!empty($request->moderatorId)) {
            $usersData->whereNotIn("users.id", [$request->moderatorId]);
        }
        $usersData = $usersData->get()->toArray();
        if ($disposableEmailFlag) {
            echo "disposable";
        } else if (sizeof($usersData) > 0) {
            echo "exists";
        } else {
            echo "not_exists";
        }
    }

    /**
     * @param Request $request
     * @return String
     */
    public function portalDomainExists(Request $request)
    {
        $portalDomain = $request->portalDomain;
        $brandingData = CompanyBranding::where('portal_domain', '=', $portalDomain)->whereNotIn("portal_domain", ["localhost"])->get()->toArray();
        if (sizeof($brandingData) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get company plan features for portal and android both
     * @param $group, Role $role
     * @return JsonResponse
    */
    public function getCompanyPlanFeatures($group, CpFeatures $cpFeatures): JsonResponse
    {
        $cpFeaturesData = $cpFeatures->getCpPlanFeatures($group);
        return response()->json([
            'body' => view('admin.companyplan.plan-features', compact('cpFeaturesData'))->render(),
        ]);
    }

     /**
     * @param Request $request
     * @return String
     */
    public function checkDTIncluded(Request $request, CpPlan $cpPlan, CpFeatures $cpFeatures)
    {
        $planId = $request->planId;
        $cpFeaturesData = CpFeatures::
                    leftjoin('cp_plan_features','cp_plan_features.feature_id', '=',  'cp_features.id')
                    ->leftJoin('cp_plan', 'cp_plan.id', '=', 'cp_plan_features.plan_id')
                    ->where('cp_plan.id', $planId)->where('cp_features.group',2)->where('cp_features.slug','digital-therapy')->get();
        if (sizeof($cpFeaturesData) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /** @param Request $request
     * @return String
     */
    public function dtLocationSlots(Request $request)
    {
        $daywiseSlots   = [];
        $dt_servicemode = false;
        $dtSlots        = [];
        $dtSlotsTemp    = [];
        $daywiseSlotsTemp = [];
        $user           = auth()->user();
        $user_role      = getUserRole($user);
        $locationId     = $request->locationId;
        $companyId      = $request->companyId;
        $removedSlots   = $updatedSlots = [];
        if (!empty($request->removedIds)) {
            $removedSlots = explode(',', $request->removedIds);
        }
        if (!empty($request->updatedIds)) {
            $updatedSlots = explode(',', $request->updatedIds);
        }
        $company        = Company::find($companyId);
        if ($user_role->group == 'reseller') {
            $dt_servicemode = true;
        }
        $wellbeingSp = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.id')->
                leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
                ->leftJoin('roles', function ($join) {
                    $join->on('roles.id', '=', 'role_user.role_id');
                })
                ->leftJoin('ws_user', function ($join) {
                    $join->on('ws_user.user_id', '=', 'users.id');
                })
                ->whereNull('users.deleted_at')
                ->where('roles.slug', 'wellbeing_specialist')
                ->where('ws_user.is_cronofy', true)
                ->pluck('name', 'users.id')
                ->toArray();
        $dtSlots1  = $company->digitalTherapySlots()->where('location_id', $locationId)->whereNotIn('id', $removedSlots)->whereNotIn('id', $updatedSlots);
        if ($dtSlots1->count() > 0) {
            foreach ($dtSlots1->get() as $slots) {
                $wsTemplate       = "";
                $wsHiddenTemplate = "";
                if (!empty($slots->ws_id)) {
                    $wsId  = explode(',', $slots->ws_id);
                    $count = 1;
                    foreach ($wsId as $id) {
                        $blankString = "";
                        if ($count < count($wsId)) {
                            $blankString = ", ";
                        }
                        if (array_key_exists($id, $wellbeingSp)) {
                            $ws_name    = $wellbeingSp[$id];
                            $value      = $id;
                            $key        = $slots->day;
                            $id         = $slots->id;
                            $locationId = $slots->location_id;
                            $wsTemplate .= view('admin.companies.slot-ws-preview', compact('ws_name', 'value', 'key', 'id', 'locationId'))->render() . $blankString;
                            $wsHiddenTemplate .= view('admin.companies.slot-ws-hidden', compact('ws_name', 'value', 'key', 'id', 'locationId'))->render();
                        }
                        $count++;
                    }
                }
                $daywiseSlots[$slots->day][] = [
                    'id'               => $slots->id,
                    'location_id'      => $slots->location_id,
                    'start_time'       => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                    'end_time'         => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                    'ws_id'            => $wsTemplate,
                    'wsHiddenTemplate' => $wsHiddenTemplate,
                    'from'             => 'mainTable'
                ];
            }
        }

        /* ======TEMP SLOTS====== */
        $dtSlotsTemp  = DB::table('temp_digital_therapy_slots')->where('company_id', $companyId)->where('location_id', $locationId);
        if ($dtSlotsTemp->count() > 0) {
            foreach ($dtSlotsTemp->get() as $slots) {
                $wsTemplate       = "";
                $wsHiddenTemplate = "";
                if (!empty($slots->ws_id)) {
                    $wsId  = explode(',', $slots->ws_id);
                    $count = 1;
                    foreach ($wsId as $id) {
                        $blankString = "";
                        if ($count < count($wsId)) {
                            $blankString = ", ";
                        }
                        if (array_key_exists($id, $wellbeingSp)) {
                            $ws_name    = $wellbeingSp[$id];
                            $value      = $id;
                            $key        = $slots->day;
                            $id         = $slots->id;
                            $locationId = $slots->location_id;
                            $wsTemplate .= view('admin.companies.slot-ws-preview', compact('ws_name', 'value', 'key', 'id', 'locationId'))->render() . $blankString;
                            $wsHiddenTemplate .= view('admin.companies.slot-ws-hidden', compact('ws_name', 'value', 'key', 'id', 'locationId'))->render();
                        }
                        $count++;
                    }
                }
                $daywiseSlotsTemp[$slots->day][] = [
                    'id'               => $slots->id,
                    'location_id'      => $slots->location_id,
                    'start_time'       => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                    'end_time'         => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                    'ws_id'            => $wsTemplate,
                    'wsHiddenTemplate' => $wsHiddenTemplate,
                    'from'             => 'tempTable'
                ];
            }
        }
        $dtSlots = array_merge_recursive($daywiseSlots,$daywiseSlotsTemp);

        $dt_availability_days = config('zevolifesettings.hc_availability_days');
        $html = "";
        foreach($dt_availability_days as $keyday => $day){
            $html.= '<div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="'. $keyday .'">
            <div class="set-availability-day">
                '.$day.'
            </div>
            <div class="w-100 slots-wrapper-location">
                <div class="d-flex align-items-center no-data-block '.(array_key_exists($keyday, $dtSlots) ? 'hide' : '') .'">
                    <div class="set-availability-date-time">
                        '. trans('labels.user.not_available') .'
                    </div>';
                    if(!$dt_servicemode){
                    $html.= '<div class="d-flex set-availability-btn-area justify-content-end">
                        <a class="add-location-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                            <i class="far fa-plus">
                            </i>
                        </a>
                    </div>';
                    }
                $html.= '</div>';
                if(array_key_exists($keyday, $dtSlots)){
                    foreach($dtSlots[$keyday] as $slot){
                        $html.= view('admin.companies.steps.digitaltherapy.location-general-slots.slot-preview', [
                            'start_time' => Carbon::parse($slot['start_time'])->format('H:i'),
                            'end_time' => Carbon::parse($slot['end_time'])->format('H:i'),
                            'time' => Carbon::parse($slot['start_time'])->format('h:i A') . ' - ' . Carbon::parse($slot['end_time'])->format('h:i A'),
                            'key' => $keyday,
                            'id' => $slot['id'],
                            'dt_servicemode' => $dt_servicemode,
                            'ws_selected' => $slot['ws_id'],
                            'ws_hidden_field' => $slot['wsHiddenTemplate'],
                            'from' => $slot['from']
                        ]);
                    }
                }
            $html.='</div>
            </div>';
        }
        echo $html;
    }

    /** @param Request $request
     * @return String
     */
    public function dtWbsList(Request $request)
    {
        return User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.id')
                ->whereIn('id', $request->id)
                ->pluck('name', 'users.id')
                ->toArray();
    }

    /**
     * @param Department $department
     *
     * @return JsonResponse
     */
    public function getLocationFromDepartments(CompanyLocation $location): JsonResponse
    {

        $returnArr = [];
        $records   = $location->departments;
        if ($records) {
            foreach ($records as $key => $record) {
                $returnArr[$key]['id']   = $record->getKey();
                $returnArr[$key]['name'] = $record->name;
            }
        }

        return $this->successResponse($returnArr);
    }

    /**
     * @param Request $request
     * @return String
     */
    public function getLocationGeneralAvabilities(Request $request)
    {
        $newlyAddedSlots = TempDigitalTherapySlots::select('id')
            ->where('company_id', $request->company_id)->whereNotNull('location_id')->get()
            ->toArray();

        $existingSlots = DigitalTherapySlots::select('id')
                ->where('company_id', $request->company_id)->whereNotNull('location_id')->get()
                ->toArray();
        
        if (sizeof($newlyAddedSlots) > 0 || sizeof($existingSlots) > 0) {
            return 1;
        } else {
            return 0;
        }
    }
}
