<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChallengeExportRequest;
use App\Http\Requests\Admin\CreateChallengeRequest;
use App\Http\Requests\Admin\EditChallengeRequest;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeExportHistory;
use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentLocation;
use App\Models\Group;
use App\Models\ContentChallenge;
use Breadcrumbs;
use Carbon\Carbon;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ChallengeController
 *
 * @package App\Http\Controllers\Admin
 */
class ChallengeController extends Controller
{
    /**
     * variable to store the model object
     * @var Challenge
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param Challenge $model ;
     */
    public function __construct(Challenge $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of challenges module
     */
    private function bindBreadcrumbs()
    {
        // Individual Challenge Breadcrumbs
        Breadcrumbs::for('challenges.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.individual.manage'));
        });
        Breadcrumbs::for('challenges.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.individual.manage'), route('admin.challenges.index'));
            $trail->push(trans('challenges.title.individual.add'));
        });
        Breadcrumbs::for('challenges.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.individual.manage'), route('admin.challenges.index'));
            $trail->push(trans('challenges.title.individual.edit'));
        });
        Breadcrumbs::for('challenges.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.individual.manage'), route('admin.challenges.index'));
            $trail->push(trans('challenges.title.individual.details'));
        });
        Breadcrumbs::for('challenges.addPoints', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.individual.manage'), route('admin.challenges.index'));
            $trail->push(trans('challenges.points.title'));
        });

        // Team Challenge Breadcrumbs
        Breadcrumbs::for('teamChallenges.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.team.manage'));
        });
        Breadcrumbs::for('teamChallenges.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.team.manage'), route('admin.teamChallenges.index'));
            $trail->push(trans('challenges.title.team.add'));
        });
        Breadcrumbs::for('teamChallenges.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.team.manage'), route('admin.teamChallenges.index'));
            $trail->push(trans('challenges.title.team.edit'));
        });
        Breadcrumbs::for('teamChallenges.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.team.manage'), route('admin.teamChallenges.index'));
            $trail->push(trans('challenges.title.team.details'));
        });
        Breadcrumbs::for('teamChallenges.addPoints', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.team.manage'), route('admin.teamChallenges.index'));
            $trail->push(trans('challenges.points.title'));
        });

        // Company Goal Challenge Breadcrumbs
        Breadcrumbs::for('companyGoalChallenges.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.company.manage'));
        });
        Breadcrumbs::for('companyGoalChallenges.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.company.manage'), route('admin.companyGoalChallenges.index'));
            $trail->push(trans('challenges.title.company.add'));
        });
        Breadcrumbs::for('companyGoalChallenges.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.company.manage'), route('admin.companyGoalChallenges.index'));
            $trail->push(trans('challenges.title.company.edit'));
        });
        Breadcrumbs::for('companyGoalChallenges.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.company.manage'), route('admin.companyGoalChallenges.index'));
            $trail->push(trans('challenges.title.company.details'));
        });
        Breadcrumbs::for('companyGoalChallenges.addPoints', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.company.manage'), route('admin.companyGoalChallenges.index'));
            $trail->push(trans('challenges.points.title'));
        });

        // Inter-Company Challenge Breadcrumbs
        Breadcrumbs::for('interCompanyChallenges.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.inter-company.manage'));
        });
        Breadcrumbs::for('interCompanyChallenges.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.inter-company.manage'), route('admin.interCompanyChallenges.index'));
            $trail->push(trans('challenges.title.inter-company.add'));
        });
        Breadcrumbs::for('interCompanyChallenges.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.inter-company.manage'), route('admin.interCompanyChallenges.index'));
            $trail->push(trans('challenges.title.inter-company.edit'));
        });
        Breadcrumbs::for('interCompanyChallenges.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.inter-company.manage'), route('admin.interCompanyChallenges.index'));
            $trail->push(trans('challenges.title.inter-company.details'));
        });
        Breadcrumbs::for('interCompanyChallenges.addPoints', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challenges.title.inter-company.manage'), route('admin.interCompanyChallenges.index'));
            $trail->push(trans('challenges.points.title'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $user         = auth()->user();
        $role         = getUserRole();
        $company      = $user->company->first();

        if ((!access()->allow('manage-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app)) {
            abort(403);
        }

        if ((!access()->allow('manage-inter-company-challenge') && $route == 'interCompanyChallenges') || ($role->group == "reseller" && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        try {
            $data                        = array();
            $data['pagination']          = config('zevolifesettings.datatable.pagination.short');
            $data['timezone']            = ($user->timezone ?? config('app.timezone'));
            $data['date_format']         = config('zevolifesettings.date_format.moment_default_datetime');
            $data['recursive']           = array('all' => 'All', 'yes' => 'Yes', 'no' => 'No');
            $data['challengeStatusData'] = array('all' => 'All', 'ongoing' => 'Ongoing', 'upcoming' => 'Upcoming', 'finished' => 'Completed', 'cancelled' => 'Cancelled');
            $data['loginemail']          = ($user->email ?? "");
            $challengeType               = "";
            if ($route == 'challenges') {
                $data['challengeCategoryData'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->pluck('name', 'id')
                    ->toArray();
                $challengeType     = "Individual";
                $data['pageTitle'] = trans('challenges.title.individual.manage');
            } elseif ($route == 'teamChallenges') {
                $data['challengeCategoryData'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['streak', 'fastest'])
                    ->pluck('name', 'id')
                    ->toArray();
                $challengeType     = "Team";
                $data['pageTitle'] = trans('challenges.title.team.manage');
            } elseif ($route == 'companyGoalChallenges') {
                $data['challengeCategoryData'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['most', 'fastest'])
                    ->pluck('name', 'id')
                    ->toArray();
                $challengeType     = "Company goal";
                $data['pageTitle'] = trans('challenges.title.company.manage');
            } elseif ($route == 'interCompanyChallenges') {
                $data['challengeCategoryData'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['streak', 'combined'])
                    ->pluck('name', 'id')
                    ->toArray();
                $challengeType     = "Inter-company";
                $data['pageTitle'] = trans('challenges.title.inter-company.manage');
            }

            $data['route']    = $route;
            $data['ga_title'] = $challengeType . " " . trans('page_title.challenges.challenges_list');
            return \view('admin.challenge.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challenge.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $user         = auth()->user();
        $role         = getUserRole($user);
        $company      = $user->company->first();
        if ((!access()->allow('create-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app)) {
            abort(403);
        }
        if ((!access()->allow('create-inter-company-challenge') && $route == 'interCompanyChallenges' && $role->group != 'zevo') || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        try {
            $data                      = array();
            $groupObj                  = new Group();
            $data['recurring_type']    = config('zevolifesettings.recurring_type');
            $data['badgeTypes']        = config('zevolifesettings.badgeTypes');
            $data['route']             = $route;
            $data['companyLocations']  = [];
            $data['companyDepartment'] = [];
            $data['hideOpenChallenge'] = '';
            if ($route == 'challenges') {
                $data['challenge_categories'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->pluck('name', 'id')
                    ->toArray();
                $data['companyLocations'] = \App\Models\CompanyLocation::where('company_id', $company->id)
                    ->select('id', 'name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['map_library'] = \App\Models\ChallengeMapLibrary::join('map_company', 'map_company.map_id', 'map_library.id')
                    ->where('map_company.company_id', $company->id)
                    ->select('map_library.id', 'map_library.name')
                    ->whereNotIn('status', [3])
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['pageTitle'] = trans('challenges.title.individual.add');
            } elseif ($route == 'teamChallenges') {
                $data['challenge_categories'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['streak', 'fastest'])
                    ->pluck('name', 'id')
                    ->toArray();
                $data['map_library'] = \App\Models\ChallengeMapLibrary::join('map_company', 'map_company.map_id', 'map_library.id')
                    ->where('map_company.company_id', $company->id)
                    ->select('map_library.id', 'map_library.name')
                    ->whereNotIn('status', [3])
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['pageTitle'] = trans('challenges.title.team.add');
            } elseif ($route == 'companyGoalChallenges') {
                $data['challenge_categories'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['most', 'fastest'])
                    ->pluck('name', 'id')
                    ->toArray();
                $data['pageTitle'] = trans('challenges.title.company.add');
            } elseif ($route == 'interCompanyChallenges') {
                $data['challenge_categories'] = \App\Models\ChallengeCategory::where("is_excluded", 0)
                    ->whereNotIn('short_name', ['streak', 'combined'])
                    ->pluck('name', 'id')
                    ->toArray();
                $data['pageTitle'] = trans('challenges.title.inter-company.add');
            }

            $data['challenge_targets']   = \App\Models\ChallengeTarget::where("is_excluded", 0)->pluck('name', 'id')->toArray();

            $data['challenge_targets_map'] = \App\Models\ChallengeTarget::where("is_excluded", 0)
                    ->whereIn('name', ['Steps', 'Distance'])
                    ->pluck('name', 'id')
                    ->toArray();
            $data['exercises']           = \App\Models\Exercise::pluck('title', 'id')->toArray();
            $data['exerciseType']        = \App\Models\Exercise::pluck('type', 'id')->toArray();
            $data['companyData']         = $groupObj->getTeamMembersData(true);
            $data['uoms']                = array();
            $data['uom_data']            = config('zevolifesettings.uom');
            $data['dayValue']            = config('zevolifesettings.recurring_day_value');
            $data['allowTargetUnitEdit'] = false;
            $data['locationDepartmentEdit'] = false;
            if ($route == 'interCompanyChallenges') {
                $data['challenge_targets'] = \App\Models\ChallengeTarget::where("is_excluded", 0)
                    ->whereNotIn('name', ['Exercises', 'Meditations', 'Content'])
                    ->pluck('name', 'id')
                    ->toArray();
            }
            $data['contentCategories']   = \App\Models\ContentChallenge::pluck('category', 'id')->toArray();

            foreach ($data['companyData'] as $value) {
                $companyId = isset($company) ? $company->id : null;
                if ($value['id'] == $companyId) {
                    $data['departmentData'] = $value['departments'];
                }
            }

            // Get ongoing badges
            $ongoingBadges         = Badge::where('type', 'ongoing')->where('uom', 'count')->select('id', 'title')->get()->pluck('title', 'id')->toArray();
            $data['ongoingBadges'] = $ongoingBadges;

            $data['ga_title'] = trans('page_title.challenges.create_' . $route);
            return \view('admin.challenge.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateChallengeRequest $request
     * @return RedirectResponse
     */
    public function store(CreateChallengeRequest $request)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        try {
            \DB::beginTransaction();
            $payload          = $request->all();
            $payload['route'] = $route;

            $data = $this->model->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.added'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.' . $route . '.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Challenge $challenge
     * @return View
     */
    public function edit(Request $request, Challenge $challenge)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $user         = auth()->user();
        $company      = $user->company->first();
        if ((!access()->allow('update-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        if (access()->allow('update-challenge') && $route != 'interCompanyChallenges') {
            $companyId = isset($company) ? $company->id : null;
            if ($challenge->company_id != $companyId) {
                abort(403);
            }
        }

        if (!access()->allow('update-inter-company-challenge') && $route == 'interCompanyChallenges') {
            abort(403);
        }

        $timezone = $user->timezone ?? config('app.timezone');
        $endDate  = Carbon::parse($challenge->end_date)->setTimezone($timezone)->toDateTimeString();
        $now      = now($timezone)->toDateTimeString();

        if (($challenge->cancelled) || ($now > $endDate)) {
            abort(403);
        }

        try {
            $data                      = array();
            $data                      = $challenge->challengeEditData();
            $data['route']             = $route;
            $data['ga_title']          = trans('page_title.challenges.edit_' . $route);
            $data['companyLocations']  = [];
            $data['companyDepartment'] = [];
            $locationArray             = explode(',', $challenge->locations);
            if ($route == 'challenges') {
                $data['pageTitle']        = trans('challenges.title.individual.edit');
                $data['companyLocations'] = \App\Models\CompanyLocation::where('company_id', $company->id)
                    ->select('id', 'name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $companyDepartment = DepartmentLocation::whereIn('company_location_id', $locationArray)
                    ->where('company_id', $company->id)
                    ->select('department_id')
                    ->get()
                    ->pluck('department_id')
                    ->toArray();
                $data['companyDepartment'] = Department::whereIn('id', $companyDepartment)->select('id', 'name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['map_library'] = \App\Models\ChallengeMapLibrary::join('map_company', 'map_company.map_id', 'map_library.id')
                    ->where('map_company.company_id', $company->id)
                    ->select('map_library.id', 'map_library.name')
                    ->whereNotIn('status', [3])
                    ->where('map_library.id', $challenge->map_id)
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            } elseif ($route == 'teamChallenges') {
                $data['map_library'] = \App\Models\ChallengeMapLibrary::join('map_company', 'map_company.map_id', 'map_library.id')
                    ->where('map_company.company_id', $company->id)
                    ->select('map_library.id', 'map_library.name')
                    ->whereNotIn('status', [3])
                    ->where('map_library.id', $challenge->map_id)
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['pageTitle']        = trans('challenges.title.team.edit');
            } elseif ($route == 'companyGoalChallenges') {
                $data['pageTitle'] = trans('challenges.title.company.edit');
            } elseif ($route == 'interCompanyChallenges') {
                $data['pageTitle'] = trans('challenges.title.inter-company.edit');
            }

            // Get ongoing badges
            $uom                   = ($data['challengeData']->challengeRules[0]->challenge_target_id == 1) ? 'count' : 'meter';
            $ongoingBadges         = Badge::where('type', 'ongoing')->where('uom', $uom)->select('id', 'title')->get()->pluck('title', 'id')->toArray();
            $data['ongoingBadges'] = $ongoingBadges;

            return \view('admin.challenge.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditChallengeRequest $request ,Challenge $challenge
     * @return RedirectResponse
     */
    public function update(EditChallengeRequest $request, Challenge $challenge)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        try {
            \DB::beginTransaction();
            $payload          = $request->all();
            $payload['route'] = $route;
            $data             = $challenge->updateEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.updated'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.' . $route . '.edit', $challenge->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function getChallenges(Request $request)
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $user         = auth()->user();
        $role         = getUserRole();
        $company      = $user->company->first();

        if ((!access()->allow('manage-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            return response()->json([
                'message' => trans('challenges.messages.unauthorized'),
            ], 422);
        }
        if ((!access()->allow('manage-inter-company-challenge') && $route == 'interCompanyChallenges') || ($role->group == "reseller" && !$company->allow_app)) {
            return response()->json([
                'message' => trans('challenges.messages.unauthorized'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Challenge $challenge
     * @return JsonResponse
     */
    public function delete(Challenge $challenge)
    {
        $user         = auth()->user();
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $role         = getUserRole();
        $company      = $user->company->first();
        if ((!access()->allow('delete-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        if (access()->allow('delete-challenge') && $route != 'interCompanyChallenges') {
            $companyId = isset($company) ? $company->id : null;
            if ($challenge->company_id != $companyId) {
                abort(403);
            }
        }

        if (!access()->allow('delete-inter-company-challenge') && $route == 'interCompanyChallenges' && $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $challenge->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Challenge $challenge
     * @return View
     */
    public function getDetails(Challenge $challenge)
    {
        $user         = auth()->user();
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $company      = $user->company->first();
        $loginemail   = ($user->email ?? "");
        if ((!access()->allow('view-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        if (access()->allow('view-challenge') && $route != 'interCompanyChallenges') {
            $companyId = isset($company) ? $company->id : null;
            if ($challenge->company_id != $companyId) {
                abort(403);
            }
        }

        if (!access()->allow('view-inter-company-challenge') && $route == 'interCompanyChallenges') {
            abort(403);
        }

        if (access()->allow('view-inter-company-challenge') && $route == 'interCompanyChallenges') {
            $companyId = isset($company) ? $company->id : null;
            if (!empty($companyId)) {
                if (!in_array($companyId, $challenge->memberCompanies()->distinct('company_id')->pluck('company_id')->toArray())) {
                    abort(403);
                }
            }
        }

        try {
            $data                  = array();
            $data['route']         = $route;
            $data['pagination']    = config('zevolifesettings.datatable.pagination.short');
            $data['timezone']      = $user->timezone ?? config('app.timezone');
            $data['challengeData'] = $challenge;
            $data['status']        = "";
            $data['loginemail']    = $loginemail;

            if ($challenge->cancelled) {
                $data['status'] = "Cancelled";
            } else {
                if ($challenge->start_date <= now(config('app.timezone'))->toDateTimeString() && $challenge->end_date >= now(config('app.timezone'))->toDateTimeString()) {
                    $data['status'] = "Ongoing";
                } elseif ($challenge->start_date > now(config('app.timezone'))->toDateTimeString()) {
                    $data['status'] = "Upcoming";
                } elseif ($challenge->end_date < now(config('app.timezone'))->toDateTimeString()) {
                    $data['status'] = "Finished";
                }
            }

            $data['exercises'] = \App\Models\Exercise::pluck('title', 'id')->toArray();
            $data['badgeData'] = Badge::select("badges.*")
                ->join("challenge_badges", "challenge_badges.badge_id", "=", "badges.id")
                ->where("challenge_badges.challenge_id", $challenge->id)
                ->get();
            $data['target'] = $challenge->challengeRules()->join('challenge_targets', 'challenge_rules.challenge_target_id', '=', 'challenge_targets.id')->select("challenge_targets.name as targetName", "challenge_rules.*")->get();

            $data['totalMembers']   = 0;
            $data['totalTeams']     = 0;
            $data['totalCompanies'] = 0;
            $challengeHistory       = $challenge->challengeHistory;

            if (!empty($challengeHistory) && $challenge->finished) {
                $data['totalMembers'] = $challenge->challengeHistoryParticipants->count();
                if ($route != 'challenges' && $route != 'interCompanyChallenges') {
                    $data['totalMembers'] = \DB::table('freezed_team_challenge_participents')->where('challenge_id', $challenge->id)->count();
                    $data['totalTeams']   = \DB::table('freezed_challenge_participents')->where('challenge_id', $challenge->id)->distinct('team_id')->pluck('team_id')->count();
                }
                if ($route == 'interCompanyChallenges') {
                    $data['totalMembers'] = \DB::table('freezed_team_challenge_participents')->where('challenge_id', $challenge->id)->count();
                    $data['totalTeams']   = \DB::table('freezed_challenge_participents')->where('challenge_id', $challenge->id)->distinct('team_id')->pluck('team_id')->count();
                    $data['totalCompanies'] = \DB::table('freezed_challenge_participents')->where('challenge_id', $challenge->id)->distinct('company_id')->pluck('company_id')->count();
                }
            } else {
                if ($data['status'] == 'Ongoing') {
                    $data['totalMembers'] = $challenge->members()->where('status', '=', 'Accepted')->count();
                    if ($route != 'challenges') {
                        $memberTeamIds        = $challenge->memberTeams()->where('status', '=', 'Accepted')->pluck('team_id')->toArray();
                        $memberUsersCount     = \DB::table('user_team')->whereIn('team_id', $memberTeamIds)->count();
                        $data['totalMembers'] = $memberUsersCount;
                        $data['totalTeams']   = $challenge->memberTeams()->where('status', '=', 'Accepted')->count();
                    }
                    if ($route == 'interCompanyChallenges') {
                        $memberTeamIds          = $challenge->memberTeams()->where('status', '=', 'Accepted')->pluck('team_id')->toArray();
                        $memberUsersCount       = \DB::table('user_team')->whereIn('team_id', $memberTeamIds)->count();
                        $data['totalMembers']   = $memberUsersCount;
                        $data['totalTeams']     = $challenge->memberTeams()->where('status', '=', 'Accepted')->count();
                        $data['totalCompanies'] = $challenge->memberCompanies()->groupBy('challenge_participants.company_id')->get()->count();
                    }
                } else {
                    $data['totalMembers'] = $challenge->members()->where('status', '=', 'Accepted')->count();
                    if ($route != 'challenges') {
                        $memberTeamIds        = $challenge->memberTeams()->pluck('team_id')->toArray();
                        $memberUsersCount     = \DB::table('user_team')->whereIn('team_id', $memberTeamIds)->count();
                        $data['totalMembers'] = $memberUsersCount;
                        $data['totalTeams']   = $challenge->memberTeams->count();
                    }
                    if ($route == 'interCompanyChallenges') {
                        $memberTeamIds          = $challenge->memberTeams()->pluck('team_id')->toArray();
                        $memberUsersCount       = \DB::table('user_team')->whereIn('team_id', $memberTeamIds)->count();
                        $data['totalMembers']   = $memberUsersCount;
                        $data['totalTeams']     = $challenge->memberTeams->count();
                        $data['totalCompanies'] = $challenge->memberCompanies()->groupBy('challenge_participants.company_id')->get()->count();
                    }
                }
            }
            $data['ga_title'] = trans('page_title.challenges.details_' . $route) . "(" . $challenge->title . ")";
            if ($route == 'challenges') {
                $data['pageTitle'] = trans('challenges.title.individual.details');
            } elseif ($route == 'teamChallenges') {
                $data['pageTitle'] = trans('challenges.title.team.details');
            } elseif ($route == 'companyGoalChallenges') {
                $data['pageTitle'] = trans('challenges.title.company.details');
            } elseif ($route == 'interCompanyChallenges') {
                $data['pageTitle'] = trans('challenges.title.inter-company.details');
            }
            $data['contentCategories']   = \App\Models\ContentChallenge::pluck('category', 'id')->toArray();
            return \view('admin.challenge.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Challenge $challenge
     * @return View
     */
    public function addPoints(Challenge $challenge)
    {
        $user         = auth()->user();
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $company      = $user->company->first();
        if ((!access()->allow('add-points') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        if (access()->allow('add-points') && $route != 'interCompanyChallenges') {
            $companyId = isset($company) ? $company->id : null;
            if ($challenge->company_id != $companyId) {
                abort(403);
            }
        }

        if (!access()->allow('add-points-for-inter-company-challenge') && $route == 'interCompanyChallenges') {
            abort(403);
        }

        try {
            $data                      = array();
            $data['route']             = $route;
            $data['challengeData']     = $challenge;
            $data['participantData']   = $challenge->getParticipantMembersData();
            $data['timezone']          = $user->timezone ?? config('app.timezone');
            $data['challenge_targets'] = \App\Models\ChallengeTarget::pluck('name', 'id')->toArray();
            $data['ga_title']          = trans('page_title.challenges.addPoints');
            return \view('admin.challenge.addpoints', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Challenge $challenge
     * @return RedirectResponse
     */
    public function managePoints(Request $request, Challenge $challenge)
    {
        $user         = auth()->user();
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $role         = getUserRole($user);
        if ((!access()->allow('add-points') && $route != 'interCompanyChallenges') || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        if (access()->allow('add-points') && $route != 'interCompanyChallenges') {
            $company   = \Auth::user()->company->first();
            $companyId = isset($company) ? $company->id : null;
            if ($challenge->company_id != $companyId) {
                abort(403);
            }
        }

        if (!access()->allow('add-points-for-inter-company-challenge') && $route == 'interCompanyChallenges' && $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data    = $challenge->updatePoints($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.points.messages.added'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.' . $route . '.addPoints', $challenge->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.' . $route . '.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , Challenge $challenge
     * @return JsonResponse
     */
    public function getMembersList(Request $request, Challenge $challenge)
    {
        try {
            return $challenge->getMembersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request ,Group $group
     *
     * @return RedirectResponse
     */
    public function getMembersListOther(Request $request, Challenge $challenge)
    {
        try {
            return $challenge->getMembersOthersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request , Challenge $challenge
     * @return JsonResponse
     */
    public function getTeamMembersList(Request $request, Challenge $challenge)
    {
        try {
            return $challenge->getTeamMembersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request , Challenge $challenge
     * @return JsonResponse
     */
    public function getCompanyMembersList(Request $request, Challenge $challenge)
    {
        try {
            return $challenge->getCompanyMembersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportReport(ChallengeExportRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('export-inter-company-challenge') || $role->group != 'zevo' || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportDataEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.report_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Challenge $challenge
     * @return RedirectResponse
     */
    public function getexporthistory(Challenge $challenge)
    {
        $getRecords = $challenge->challengeExportHistory()->orderByDesc('id')->first();

        if (!empty($getRecords) && $getRecords->status == 1) {
            return 1;
        }
        return 0;
    }

    /**
     * @param Challenge $challenge
     * @return RedirectResponse
     */
    public function setAccurateData(Challenge $challenge)
    {
        try {
            if (!$challenge->finished && !$challenge->cancelled) {
                $challengeExecuteStatus = $challenge->checkExecutionRequired();
                if ($challengeExecuteStatus) {
                    $pointCalcRules = config('zevolifesettings.default_limits');
                    $procedureData  = [
                        config('app.timezone'),
                        $challenge->id,
                        $pointCalcRules['steps'],
                        $pointCalcRules['distance'],
                        $pointCalcRules['exercises_distance'],
                        $pointCalcRules['exercises_duration'],
                        $pointCalcRules['meditations'],
                    ];
                    DB::select('CALL sp_inter_comp_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                }
            }
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param type
     * @return RedirectResponse
     */
    public function getOngoingBadge($type = 1)
    {
        // Get ongoing badges
        $uom           = ($type == 1) ? 'count' : 'meter';
        return Badge::where('type', 'ongoing')->where('uom', $uom)->select('id', 'title')->get()->pluck('title', 'id')->toArray();
    }

    /**
     * @param  Challenge $challenge
     * @return JsonResponse
     */
    public function cancel(Challenge $challenge, Request $request)
    {
        $user         = auth()->user();
        if (!getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }

        try {
            return $challenge->cancelRecord($request->all());
        } catch (\Exception $exception) {
            dd($exception);
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Request $request
     * @return JsonResponse
     */
    public function getDepartments(Challenge $challenge, Request $request)
    {
        $user    = auth()->user();
        $company = $user->company->first();
        if (!getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        try {
            return $challenge->getDepartment($request->all(), $company->id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Request $request
     * @return JsonResponse
     */
    public function getMemberData(Challenge $challenge, Request $request)
    {
        $user    = auth()->user();
        $company = $user->company->first();
        if (!getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        try {
            return $challenge->getMemberData($request->all(), $company->id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function exportChallengeDetails(Request $request)
    {
        $user           = auth()->user();
        $explodeRoute   = explode('.', \Route::currentRouteName());
        $route          = $explodeRoute[1];
        $company        = $user->company->first();
        if ((!access()->allow('view-challenge') && $route != 'interCompanyChallenges') || ($route != 'interCompanyChallenges' && !$company->allow_app) || !getCompanyPlanAccess($user, 'my-challenges')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->exportDataEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.report_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
