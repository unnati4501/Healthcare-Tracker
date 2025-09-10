<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePodcastRequest;
use App\Http\Requests\Admin\EditPodcastRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\Goal;
use App\Models\Podcast;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class PodcastController
 *
 * @package App\Http\Controllers\Admin
 */
class PodcastController extends Controller
{
    /**
     * variable to store the model object
     * @var Podcast
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Podcast $model ;
     */
    public function __construct(Podcast $model, AuditLogRepository $auditLogRepository)
    {
        $this->model                = $model;
        $this->auditLogRepository   = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of company modules
     */
    private function bindBreadcrumbs()
    {
        // podcast crud
        Breadcrumbs::for('podcasts.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Podcasts');
        });
        Breadcrumbs::for('podcasts.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Podcasts', route('admin.podcasts.index'));
            $trail->push('Add Podcast');
        });
        Breadcrumbs::for('podcasts.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Podcasts', route('admin.podcasts.index'));
            $trail->push('Edit Podcast');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-podcast') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                          = array();
            $data['timezone']              = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format']           = config('zevolifesettings.date_format.meditation_recepie_support_createdtime');
            $data['pagination']            = config('zevolifesettings.datatable.pagination.long');
            $data['podcastSubcategory']    = SubCategory::where(['category_id' => 9, 'status' => 1])->pluck('name', 'id')->toArray();
            $data['healthcoach']           = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')->where("is_coach", 1)->pluck('name', 'id')->toArray();
            if ($role->group == 'zevo') {
                $tags                = CategoryTags::where("category_id", 9)->pluck('name', 'id')->toArray();
                $data['tags']        = array_replace(['NA' => 'NA'], $tags);
                $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $data['healthcoach']);
            }
            $data['roleGroup']           = $role->group;
            $data['ga_title']            = trans('page_title.podcasts.podcast_list');
            return \view('admin.podcast.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-podcast') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                = array();
            $data['subcategory'] = SubCategory::where(['category_id' => 9, 'status' => 1])->pluck('name', 'id')->toArray();
            $healthcoach         = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                ->where(["is_coach" => 1, 'is_blocked' => 0])
                ->pluck('name', 'id')
                ->toArray();
            $data['companies']   = $this->getAllCompaniesGroupType();
            $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $healthcoach);
            $data['roleGroup']   = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 9)->pluck('name', 'id')->toArray();
            }
            $data['goalTags'] = Goal::pluck('title', 'id')->toArray();
            $data['ga_title'] = trans('page_title.podcasts.create');
            return \view('admin.podcast.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.podcasts.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreatePodcastRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreatePodcastRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-podcast') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data    = $this->model->storeEntity($request->all());
            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Podcast added successfully", $logData);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('labels.podcast.data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param Request $request ,Podcast $podcast
     * @return View
     */
    public function edit(Request $request, Podcast $podcast)
    {
        $role = getUserRole();
        if (!access()->allow('update-podcast') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data              = $podcast->podcastEditData();
            $data['companies'] = $this->getAllCompaniesGroupType();
            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 9)->pluck('name', 'id')->toArray();
            }
            $data['ga_title'] = trans('page_title.podcasts.edit');
            return \view('admin.podcast.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.podcasts.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditPodcastRequest $request
     * @param Podcast $podcast
     *
     * @return RedirectResponse
     */
    public function update(EditPodcastRequest $request, Podcast $podcast)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-podcast') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $podcast->toArray());
            $data               = $podcast->updateEntity($request->all());
            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Podcast updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('labels.podcast.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getPodcasts(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-podcast') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
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
     * @param  Podcast $podcast
     *
     * @return RedirectResponse
     */

    public function delete(Podcast $podcast)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-podcast') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_podcast_id' => $podcast->id, 'deleted_podcast_name' => $podcast->title]);
            $this->auditLogRepository->created("Podcast deleted successfully", $logs);

            return $podcast->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['deleted' => false], 500);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType()
    {
        $groupType        = config('zevolifesettings.podcast_company_group_type');
        $companyGroupType = [];
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        foreach ($groupType as $value) {
            if ($value == 'Zevo') {
                $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                    ->whereNull('parent_id')
                    ->where('is_reseller', false)
                    ->get()
                    ->toArray();
            }

            if (count($companies) > 0) {
                foreach ($companies as $item) {
                    $diff         = $now->diffInHours($item['subscription_end_date'], false);
                    $startDayDiff = $now->diffInHours($item['subscription_start_date'], false);
                    $days         = (int) ceil($diff / 24);

                    if ($startDayDiff > 0) {
                        $planStatus = 'Inactive';
                    } elseif ($days <= 0) {
                        $planStatus = 'Expired';
                    } else {
                        $planStatus = 'Active';
                    }

                    $companyLocation = CompanyLocation::where('company_id', $item['id'])->select('id', 'name')->get()->toArray();

                    $locationArray = [];
                    foreach ($companyLocation as $locationItem) {
                        $departmentArray   = [];
                        $departmentRecords = DepartmentLocation::join('departments', 'departments.id', '=', 'department_location.department_id')->where('department_location.company_location_id', $locationItem['id'])->where('department_location.company_id', $item['id'])->select('departments.id', 'departments.name')->get()->toArray();

                        foreach ($departmentRecords as $departmentItem) {
                            $teamArray   = [];
                            $teamRecords = TeamLocation::join('teams', 'teams.id', '=', 'team_location.team_id')->where('team_location.department_id', $departmentItem['id'])->where('team_location.company_id', $item['id'])->where('team_location.company_location_id', $locationItem['id'])->select('teams.id', 'teams.name')->get()->toArray();

                            foreach ($teamRecords as $teamItem) {
                                $teamArray[] = [
                                    'id'   => $teamItem['id'],
                                    'name' => $teamItem['name'],
                                ];
                            }

                            if (!empty($teamArray)) {
                                $departmentArray[] = [
                                    'departmentName' => $departmentItem['name'],
                                    'team'           => $teamArray,
                                ];
                            }
                        }

                        $locationArray[] = [
                            'locationName' => $locationItem['name'],
                            'department'   => $departmentArray,
                        ];
                    }

                    $plucked[$value][$item['id']] = [
                        'companyName' => $item['name'] . ' - ' . $planStatus,
                        'location'    => $locationArray,
                    ];
                }
                $companyGroupType[] = [
                    'roleType'  => $value,
                    'companies' => $plucked[$value],
                ];
            }
        }
        return $companyGroupType;
    }
}
