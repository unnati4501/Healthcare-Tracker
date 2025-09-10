<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateTeamRequest;
use App\Http\Requests\Admin\EditTeamRequest;
use App\Http\Requests\Admin\SetTeamLimitRequest;
use App\Http\Requests\Admin\UpdateTeamAssignmentRequest;
use App\Http\Requests\Admin\NpsReportExportRequest;
use App\Models\Company;
use App\Models\Team;
use App\Models\SubCategory;
use App\Models\Webinar;
use App\Models\Recipe;
use App\Models\Course;
use App\Models\Podcast;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class TeamController
 *
 * @package App\Http\Controllers\Admin
 */
class TeamController extends Controller
{
    /**
     * variable to store the model object
     * @var Team
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Team $model ;
     */
    public function __construct(Team $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('team.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Teams');
        });
        Breadcrumbs::for('team.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Teams', route('admin.teams.index'));
            $trail->push('Add Team');
        });
        Breadcrumbs::for('team.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Teams', route('admin.teams.index'));
            $trail->push('Edit Team');
        });
        Breadcrumbs::for('team.setlimit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Teams', route('admin.teams.index'));
            $trail->push('Set Limit');
        });
        Breadcrumbs::for('team.teamassignment', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Team Assignment');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('manage-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        try {
            $appTimezone         = config('app.timezone');
            $timezone            = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $now                 = now($timezone)->toDateTimeString();
            $company             = $user->company()->first();
            $companies           = [];
            $hasOngoingChallenge = false;

            if ($role->group == 'zevo') {
                $companies = Company::get()->pluck('name', 'id')->toArray();
            } elseif ($role->group == 'reseller') {
                $companyData = Company::where('id', $company->id);
                if ($company->parent_id == null) {
                    $companyData->orWhere('parent_id', $company->id);
                }
                $companies = $companyData->pluck('name', 'id')->toArray();
            } else {
                $companies = Company::where('id', $company->id)->pluck('name', 'id')->toArray();
                // get ongoing + upcoming challenge ids
                $challenge = $company->challenge()
                    ->select('challenges.id', 'challenges.challenge_type')
                    ->where('challenges.cancelled', false)
                    ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                    ->where(function ($query) use ($now, $appTimezone, $timezone) {
                        $query
                            ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ? , ?) >= ?",[$appTimezone,$timezone,$now,$appTimezone,$timezone,$now])
                            ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[$appTimezone,$timezone,$now,$appTimezone,$timezone,$now]);
                    })
                    ->groupBy('challenges.id')
                    ->get();

                // get ongoing + upcoming inter_company challenge ids
                $icChallenge = $company->icChallenge()
                    ->select('challenges.id', 'challenges.challenge_type')
                    ->where('challenges.cancelled', false)
                    ->where(function ($query) use ($now, $appTimezone, $timezone) {
                        $query
                            ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                                $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                            ])
                            ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                                $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                            ]);
                    })
                    ->groupBy('challenges.id')
                    ->get();

                $hasOngoingChallenge = (($challenge->count() + $icChallenge->count()) > 0);
            }

            $data = [
                'role'                => $role,
                'companiesDetails'    => $company,
                'company_id'          => !is_null($company) ? $company->id : null,
                'companies'           => $companies,
                'hasOngoingChallenge' => $hasOngoingChallenge,
                'pagination'          => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'            => trans('page_title.teams.teams_list'),
                'loginemail'          => ($user->email ?? ""),
                'timezone'            => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format'         => config('zevolifesettings.date_format.moment_default_date'),
                'role'                => $role,
                'company'             => $company,
                
            ];

            return \view('admin.team.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        
        if (!access()->allow('create-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $companiesDetails            = auth()->user()->company()->first();
            $data                        = array();
            $data['companyDepartment']   = array();
            $data['company']             = array();
            $data['role']                = $role;
            $data['companiesDetails']    = $companiesDetails;
            $data['hasOngoingChallenge'] = false;
            if ($companiesDetails == null) {
                $data['department'] = array();
            } else {
                $data['department'] = \Auth::user()->company->first()->departments->pluck('name', 'id');
            }
            $data['location'] = array();

            if ($role->group == 'zevo') {
                $data['company'] = Company::pluck('name', 'id')->toArray();
            } elseif ($role->group == 'reseller') {
                $companyData = Company::where('id', $companiesDetails->id);
                if ($companiesDetails->parent_id == null) {
                    $companyData->orWhere('parent_id', $companiesDetails->id);
                }
                $data['company'] = $companyData->pluck('name', 'id')->toArray();
            } else {
                $data['company'] = Company::where('id', $companiesDetails->id)->pluck('name', 'id')->toArray();
            }

            $data['masterContentType']  = $this->getAllMasterContent($companiesDetails, 'zevo');
            $data['ga_title'] = trans('page_title.teams.create');
            return \view('admin.team.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateTeamRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateTeamRequest $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('create-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($payload);

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Team added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('team.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.teams.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('team.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.teams.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request, Team $team)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('update-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        $company       = $user->company()->first();
        $userCompanyId = $company != null ? $company->id : null;

        if ($role->group != 'zevo') {
            if ($role->group == 'company') {
                if ($company->id != $team->company_id) {
                    return view('errors.401');
                }
            } elseif ($role->group == 'reseller') {
                if ($company->is_reseller) {
                    $allcompanies = Company::where('parent_id', $company->id)->orWhere('id', $company->id)->get()->pluck('id')->toArray();
                    if (!in_array($team->company_id, $allcompanies)) {
                        return view('errors.401');
                    }
                } elseif (!$company->is_reseller && $team->company_id != $company->id) {
                    return view('errors.401');
                } elseif ($userCompanyId != null && $userCompanyId != $team->company_id) {
                    return view('errors.401');
                }
            }
        }

        try {
            $data                        = array();
            $data                        = $this->model->teamEditData($team->id);
            $data['ga_title']            = trans('page_title.teams.edit');
            $data['role']                = getUserRole();
            $data['companiesDetails']    = auth()->user()->company()->first();
            $data['hasOngoingChallenge'] = false;
            if ($role->slug == 'super_admin') {
                $selectedCompany             = Company::find($team->company_id);
                $data['masterContentType']   = $this->getAllMasterContent($company, 'zevo');
                $data['selectedContent']     = $this->getAllSeletedParentResellerData($selectedCompany, 'zevo', $team);
            }
            
            return \view('admin.team.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditTeamRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditTeamRequest $request, Team $team)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('update-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $team->toArray());
            $data = $this->model->updateEntity($request->all(), $team->id);

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Team updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('team.message.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.teams.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('team.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.teams.edit', $team->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getTeams(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('manage-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            return response()->json([
                'message' => trans('team.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Team $team)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('delete-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_team_id' => $team->id,'deleted_team_name' => $team->name]);
            $this->auditLogRepository->created("Team deleted successfully", $logs);

            return $team->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
        }
    }

    public function oldteamAssignmentIndex()
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();
        if (!access()->allow('team-assignment') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null) || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $data               = array();
            $data['department'] = Auth::user()->company->first()->departments->pluck('name', 'id')->toArray();
            $data['ga_title']   = trans('page_title.teams.team-assignment');
            return \view('admin.team.old-team-assignment-index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    public function oldgetAssignmentTeamMembers($teams)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();
        if (!access()->allow('team-assignment') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null) || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            return response()->json([
                'message' => trans('team.message.unauthorized_access'),
            ], 422);
        }

        try {
            $teamMembers = $this->model->oldgetAssignmentTeamMembers($teams);
            $returnData  = [
                'status' => 1,
                'data'   => $teamMembers,
            ];
            return response()->json($returnData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    public function oldupdateTeamAssignment(UpdateTeamAssignmentRequest $request)
    {
        try {
            $queryString = [
                'fromdepartment' => $request->input('fromdepartment', 0),
                'fromteam'       => $request->input('fromteam', 0),
                'todepartment'   => $request->input('todepartment', 0),
                'toteam'         => $request->input('toteam', 0),
            ];
            \DB::beginTransaction();
            $data = $this->model->oldupdateUsersTeam($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('team.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.old-team-assignment.index', $queryString)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('team.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.old-team-assignment.index', $queryString)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.old-team-assignment.index', $queryString)->with('message', $messageData);
        }
    }

    /**
     * Set team wise limit and enable auto team creation feature
     *
     * @param Request $request
     * @return View
     */
    public function setTeamLimit(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('set-team-limit') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        try {
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now         = now($timezone)->toDateTimeString();
            $company     = $user->company()->first();

            // get ongoing + upcoming challenge ids
            $challenge = $company->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $company->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            $hasOngoingChallenge = (($challenge->count() + $icChallenge->count()) > 0);
            if ($hasOngoingChallenge) {
                $messageData = [
                    'data'   => trans('team.validation.set_limit_error_msg'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.teams.index')->with('message', $messageData);
            }

            $data = [
                'autoTeamCreationValue' => (($company->auto_team_creation) ? "checked" : ""),
                'visibilityClass'       => ((!$company->auto_team_creation) ? 'hide' : ''),
                'teamLimit'             => (($company->auto_team_creation) ? $company->team_limit : null),
                'ga_title'              => trans('page_title.teams.set-team-limit'),
            ];

            return \view('admin.team.set-team-limit', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Set team wise limit and enable auto team creation feature
     *
     * @param SetTeamLimitRequest $request
     * @return View
     */
    public function updateTeamLimit(SetTeamLimitRequest $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('set-team-limit') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        try {
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now         = now($timezone)->toDateTimeString();
            $company     = $user->company()->first();

            // get ongoing + upcoming challenge ids
            $challenge = $company->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $company->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->groupBy('challenges.id')
                ->get();

            $hasOngoingChallenge = (($challenge->count() + $icChallenge->count()) > 0);
            if ($hasOngoingChallenge) {
                $messageData = [
                    'data'   => trans('team.validation.set_limit_error_msg'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.teams.index')->with('message', $messageData);
            }

            \DB::beginTransaction();
            $data = $company->updateTeamLimit($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('team.message.limit_updated'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.teams.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('team.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::back()->withInput($request->all())->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            \DB::rollback();
            $messageData = [
                'data'   => trans('team.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::back()->withInput($request->all())->with('message', $messageData);
        }
    }

    /**
     * Team assignement4
     *
     * @param Request $request
     * @return View
     */
    public function teamAssignmentIndex(Request $request)
    {
        try {
            $user    = auth()->user();
            $role    = getUserRole($user);
            $company = $user->company()->select('companies.id', 'companies.is_reseller')->first();

            // check logged-in user have access of the module
            if (!access()->allow('team-assignment') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->is_reseller) || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
                return view('errors.401');
            }

            $data = [
                'department' => $company->departments()->select('name', 'id')->pluck('name', 'id')->toArray(),
                'ga_title'   => trans('page_title.teams.team-assignment'),
            ];

            return \view('admin.team.team-assignment-index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To get team members(can pass multiple team id by ,)
     *
     * @param String $teams
     * @return Json
     */
    public function getAssignmentTeamMembers($teams)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->select('companies.id', 'companies.parent_id')->first();

        // check logged-in user have access of the module
        if (!access()->allow('team-assignment') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null) || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            return response()->json([
                'message' => trans('team-assignment.message.unauthorized_access'),
            ], 422);
        }

        try {
            return response()->json([
                'data'   => $this->model->getAssignmentTeamMembers($teams),
                'status' => 1,
            ], 200);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('team-assignment.message.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Update teams of the member
     *
     * @param String $teams
     * @return Redirect response
     */
    public function updateTeamAssignment(UpdateTeamAssignmentRequest $request)
    {
        try {
            $queryString = [
                'fromdepartment' => $request->input('fromdepartment', 0),
                'fromteam'       => $request->input('fromteam', 0),
                'todepartment'   => $request->input('todepartment', 0),
                'toteam'         => $request->input('toteam', 0),
            ];
            \DB::beginTransaction();
            $data = $this->model->updateUsersTeam($request->all());
            if (isset($data['status']) && $data['status']) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('team-assignment.message.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'url'    => route('admin.team-assignment.index', $queryString),
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'data'   => (!empty($data['message']) ? $data['message'] : trans('team-assignment.message.something_wrong_try_again')),
                    'status' => 0,
                ], 422);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'data'   => trans('team-assignment.message.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportTeams(NpsReportExportRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('manage-team') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->exportTeamDataEntity($request->all());
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
            return \Redirect::route('admin.teams.index')->with('message', $messageData);
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
     * Get All Master Content Type
     * @param $company Company
     * @return array
     **/
    protected function getAllMasterContent($company = null, $companyType)
    {
        $type = config('zevolifesettings.company_content_master_type');
        if ($companyType == 'zevo') {
            $type = config('zevolifesettings.company_content_master_type_zevo');
        }
        foreach ($type as $key => $value) {
            $subcategory = SubCategory::select('id', 'name')
                ->where('status', 1)->where("category_id", $key)
                ->pluck('name', 'id')->toArray();
            $subcategoryArray = [];
            foreach ($subcategory as $subKey => $subValue) {
                $result = null;
                switch ($value) {
                    case 'Masterclass':
                        $result = Course::select('courses.id', 'courses.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT masterclass_id FROM `masterclass_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('masterclass_id')->toArray();
                                    $query->whereIn('courses.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'courses.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Meditation':
                        $result = MeditationTrack::select('meditation_tracks.id', 'meditation_tracks.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT meditation_track_id FROM `meditation_tracks_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('meditation_track_id')->toArray();
                                    $query->whereIn('meditation_tracks.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'meditation_tracks.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Webinar':
                        $result = Webinar::select('webinar.id', 'webinar.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT webinar_id FROM `webinar_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('webinar_id')->toArray();
                                    $query->whereIn('webinar.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'webinar.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Feed':
                        $result = Feed::select('feeds.id', 'feeds.title', 'category_tags.name as categoryTag')
                            ->where('company_id', null)
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT feed_id FROM `feed_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('feed_id')->toArray();
                                    $query->whereIn('feeds.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'feeds.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Podcast':
                        $result = Podcast::select('podcasts.id', 'podcasts.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT podcast_id FROM `podcast_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('podcast_id')->toArray();
                                    $query->whereIn('podcasts.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'podcasts.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    default:
                        $result = Recipe::select('recipe.id', 'recipe.title', 'category_tags.name as categoryTag')
                            ->where('company_id', null)
                            ->join('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                            ->where('recipe_category.sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT recipe_id FROM `recipe_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('recipe_id')->toArray();
                                    $query->whereIn('recipe.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'recipe.tag_id')
                            ->get()
                            ->toArray();
                        break;
                }
                if (!empty($result)) {
                    foreach ($result as $item) {
                        $categoryTag = 'N/A';
                        if (!empty($item['categoryTag']) && $item['categoryTag'] != '') {
                            $categoryTag = $item['categoryTag'];
                        }
                        $plucked[$subValue][$value][$item['id']] = $item['title'] . ' - ' . $categoryTag;
                    }
                    $subcategoryArray[] = [
                        'id'              => $subKey,
                        'subcategoryName' => $subValue,
                        $value            => $plucked[$subValue][$value],
                    ];
                }
            }
            $masterContentType[] = [
                'id'           => $key,
                'categoryName' => $value,
                'subcategory'  => $subcategoryArray,
            ];
        }
        return $masterContentType;
    }

    /**
     * Get All Selected Parent Reseller Data
     * @param $company Company
     * @return array
     **/
    protected function getAllSeletedParentResellerData($company = [], $companyType, $team)
    {
        $type = config('zevolifesettings.company_content_master_type');
        if ($companyType == 'zevo') {
            $type = config('zevolifesettings.company_content_master_type_zevo');
        }
        foreach ($type as $key => $value) {
            $subcategory = SubCategory::select('id', 'name')
                ->where('status', 1)->where("category_id", $key)
                ->pluck('name', 'id')->toArray();
            foreach ($subcategory as $subKey => $subValue) {
                $result = null;
                switch ($value) {
                    case 'Masterclass':
                        $result = DB::table('masterclass_team')->where('team_id', $team->id)->pluck('masterclass_id')->toArray();
                        break;
                    case 'Meditation':
                        $result = DB::table('meditation_tracks_team')->where('team_id', $team->id)->pluck('meditation_track_id')->toArray();
                        break;
                    case 'Webinar':
                        $result = DB::table('webinar_team')->where('team_id', $team->id)->pluck('webinar_id')->toArray();
                        break;
                    case 'Feed':
                        $result = DB::table('feed_team')->where('team_id', $team->id)->pluck('feed_id')->toArray();
                        break;
                    case 'Podcast':
                        $result = DB::table('podcast_team')->where('team_id', $team->id)->pluck('podcast_id')->toArray();
                        break;
                    default:
                        $result = DB::table('recipe_team')->where('team_id', $team->id)->pluck('recipe_id')->toArray();
                        break;
                }

                if (!empty($result)) {
                    foreach ($result as $resultValue) {
                        $contentId          = $key . '-' . $subKey . '-' . $resultValue;
                        $subcategoryArray[] = $contentId;
                    }
                }
            }
        }

        return $subcategoryArray ?? [];
    }
}
