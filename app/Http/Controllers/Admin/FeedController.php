<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateFeedRequest;
use App\Http\Requests\Admin\EditFeedRequest;
use App\Http\Requests\Admin\CloneFeedRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\Feed;
use App\Models\Goal;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\Timezone;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;

/**
 * Class FeedController
 *
 * @package App\Http\Controllers\Admin
 */
class FeedController extends Controller
{
    /**
     * variable to store the model object
     * @var Feed
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Feed $model ;
     */
    public function __construct(Feed $model, AuditLogRepository $auditLogRepository)
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
        Breadcrumbs::for('feed.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Stories');
        });
        Breadcrumbs::for('feed.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Stories', route('admin.feeds.index'));
            $trail->push('Add Story');
        });
        Breadcrumbs::for('feed.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Stories', route('admin.feeds.index'));
            $trail->push('Edit Story');
        });
        Breadcrumbs::for('feed.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Stories', route('admin.feeds.index'));
            $trail->push('Story Details');
        });
        Breadcrumbs::for('feed.clone', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Stories', route('admin.feeds.index'));
            $trail->push('Clone Story');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user                       = auth()->user();
        $role                       = getUserRole($user);
        $checkPlanAccessForReseller = getDTAccessForParentsChildCompany($user, 'explore');
        if (!access()->allow('manage-story') || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $data                = array();
            $role                = getUserRole();
            $data['timezone']    = (auth()->user()->timezone ?? config('app.timezone'));
            $companyData         = auth()->user()->company()->get()->first();
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_datetime');

            $data['isSA']                       = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));
            $data['companyColVisibility']       = ($role->group == 'zevo');
            $data['visabletocompanyVisibility'] = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));

            if ($role->group == 'reseller' && $companyData->parent_id == null) {
                $data['company'] = Company::where('id', $companyData->id)->orwhere('parent_id', $companyData->id)->where('subscription_start_date', '<=', Carbon::now())->pluck('name', 'id')->toArray();
            } elseif ($role->group == 'zevo') {
                $data['company'] = array_replace(['zevo' => 'Zevo'], Company::where('subscription_start_date', '<=', Carbon::now())->pluck('name', 'id')->toArray());
            }

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $tags         = CategoryTags::where("category_id", 2)->pluck('name', 'id')->toArray();
                $data['tags'] = array_replace(['NA' => 'NA'], $tags);
            }

            $data['pagination']           = config('zevolifesettings.datatable.pagination.long');
            $data['sheduled_contentData'] = array("all" => "All", "scheduled" => "Scheduled");
            $data['subcategories']        = SubCategory::where('status', 1)->where("category_id", 2)->pluck('name', 'id')->toArray();
            $data['ga_title']             = trans('page_title.feeds.feeds_list');
            $data['feedType']             = config('zevolifesettings.type_array_filter');
            return \view('admin.feed.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('feed.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('create-story')) {
            abort(403);
        }

        try {
            $user            = Auth::user();
            $role            = getUserRole($user);
            $data            = array();
            $companyData     = $user->company->first();
            $data['isSA']    = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));
            $data['company'] = $this->getAllCompaniesGroupType($role->group, $companyData);

            $data['subcategories'] = SubCategory::where('status', 1)->where("category_id", 2)->pluck('name', 'id')->toArray();
            $feedTypes             = config('zevolifesettings.feed_type');
            if ($role->group == 'reseller' || (!empty($companyData) && !$companyData->is_reseller && $companyData->parent_id != null)) {
                unset($feedTypes[3]);
            }
            $data['feed_types']            = $feedTypes;
            $data['timezoneArray']         = Timezone::pluck('name', 'name')->toArray();
            $data['healthcoach']           = [];
            $data['goalTags']              = Goal::pluck('title', 'id')->toArray();
            $data['appBackgroundImage']    = true;
            $data['portalBackgroundImage'] = true;
            if ($role->group == 'zevo') {
                $healthcoach = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                    ->where(["is_coach" => 1, 'is_blocked' => 0])
                    ->pluck('name', 'id')
                    ->toArray();

                $data['healthcoach']           = array_replace([1 => 'Zevo Admin'], $healthcoach);
                $data['appBackgroundImage']    = true;
                $data['portalBackgroundImage'] = true;
            } elseif ($role->group == 'company') {
                $companyUsers = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                    ->join('user_team', function ($join) use ($companyData) {
                        $join->on('user_team.user_id', '=', 'users.id')
                            ->where('user_team.company_id', $companyData->id);
                    })
                    ->where(['users.is_blocked' => 0])
                    ->pluck('users.name', 'users.id')
                    ->toArray();

                $healthcoach = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                    ->where(["is_coach" => 1, 'is_blocked' => 0])
                    ->pluck('name', 'id')
                    ->toArray();

                $data['healthcoach']           = array_replace($healthcoach, $companyUsers);
                $data['appBackgroundImage']    = true;
                $data['portalBackgroundImage'] = false;
            } elseif ($role->group == 'reseller') {
                $data['healthcoach'] = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                    ->join('user_team', function ($join) use ($companyData) {
                        $join->on('user_team.user_id', '=', 'users.id')
                            ->where('user_team.company_id', $companyData->id);
                    })
                    ->where(['users.is_blocked' => 0])
                    ->pluck('users.name', 'users.id')
                    ->toArray();

                if ($companyData->parent_id == null) {
                    $data['appBackgroundImage']    = false;
                    $data['portalBackgroundImage'] = true;
                } else {
                    $data['appBackgroundImage']    = $companyData->allow_app;
                    $data['portalBackgroundImage'] = $companyData->allow_portal;
                }
            }

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 2)->pluck('name', 'id')->toArray();
            }

            $data['ga_title'] = trans('page_title.feeds.create');

            return \view('admin.feed.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateFeedRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateFeedRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('create-story')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Feed added successfully", $logData);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('feed.message.data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('feed.message.something_wrong_try_again'),
                ], 422);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('feed.message.invalid_youtube_link'),
            ], 422);
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
     * @return View
     */
    public function edit(Request $request, Feed $feed)
    {
        if (!access()->allow('update-story')) {
            abort(403);
        }

        $user        = auth()->user();
        $role        = getUserRole($user);
        $companyData = $user->company()->get()->first();

        if ($role->group != 'zevo' && $feed->company_id != $companyData->id) {
            $childcompany = company::where('parent_id', $companyData->id)->pluck('id')->toArray();
            if ($role->group == 'reseller') {
                if (!in_array($feed->company_id, $childcompany)) {
                    abort(403);
                }
            } else {
                abort(403);
            }
        }

        try {
            $data = array();
            $data = $feed->feedEditData();

            $is_visible = 0;
            if ($feed->company_id == null || (!empty($companyData) && $feed->company_id == $companyData->id)) {
                $is_visible = 1;
            }

            $data['is_visible']    = $is_visible;
            $data['company']       = $this->getAllCompaniesGroupType($role->group, $companyData);
            $data['isSA']          = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));
            $data['feed_types']    = config('zevolifesettings.feed_type');
            $data['timezoneArray'] = Timezone::pluck('name', 'name')->toArray();
            $data['ga_title']      = trans('page_title.feeds.edit');

            $data['appBackgroundImage']    = true;
            $data['portalBackgroundImage'] = true;
            
            if ($role->group == 'company') {
                $data['portalBackgroundImage'] = false;
            } elseif ($role->group == 'reseller') {
                if ($companyData->parent_id == null) {
                    $data['appBackgroundImage']    = false;
                } else {
                    $data['appBackgroundImage']    = $companyData->allow_app;
                    $data['portalBackgroundImage'] = $companyData->allow_portal;
                }
            }

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 2)->pluck('name', 'id')->toArray();
            }

            return \view('admin.feed.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditFeedRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditFeedRequest $request, Feed $feed)
    {
        $user = auth()->user();
        if (!access()->allow('update-story')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $feed->toArray());
            $data = $feed->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Feed updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('feed.message.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('feed.message.something_wrong_try_again'),
                ], 422);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('feed.message.invalid_youtube_link'),
            ], 422);
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
     * @return View
     */

    public function getFeeds(Request $request)
    {
        if (!access()->allow('manage-story')) {
            return response()->json([
                'message' => trans('feed.message.unauthorized_access'),
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
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Feed $feed)
    {
        $user = auth()->user();
        if (!access()->allow('delete-story')) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_feed_id' => $feed->id,'deleted_feed_title' => $feed->title]);
            $this->auditLogRepository->created("Feed deleted successfully", $logs);

            return $feed->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function getDetails(Feed $feed)
    {
        if (!access()->allow('view-story')) {
            abort(403);
        }

        try {
            $user             = auth()->user();
            $data             = array();
            $role             = getUserRole();
            $data['user']     = $user;
            $data['role']     = $role;
            $data['feedData'] = $feed;
            $data['timezone'] = $user->timezone ?? config('app.timezone');

            $background_logo      = "";
            $square_logo          = '';
            $data['isShowButton'] = true;

            if ($role->group != 'zevo') {
                $companyId            = $user->company->first()->id;
                $childcompany         = company::where('parent_id', $companyId)->pluck('id')->toArray();
                $data['isShowButton'] = (in_array($feed->company_id, $childcompany));
            }

            $data['square_logo']     = $square_logo;
            $data['background_logo'] = $background_logo;
            $data['ga_title']        = trans('page_title.feeds.details');
            return \view('admin.feed.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function deleteFeedMedia(Feed $feed, $type = '')
    {
        try {
            return $feed->deleteMediaRecord($type);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * function for stick a feed
     * @param Feed $feed
     */
    public function stickUnstick(Feed $feed, Request $request)
    {
        if (!access()->allow('sticky-story')) {
            abort(403);
        }

        try {
            $role    = getUserRole();
            $user    = auth()->user();
            $company = $user->company->first();
            $action  = $request->input('action', "stick");
            $data    = [
                'status'  => 0,
                'message' => trans('feed.modal.failed_feed_action', [
                    'action' => $action,
                ]),
            ];
            $stickCount = config('zevolifesettings.stories.all');
            $stickWarningMsg = trans('feed.message.max_two_feed');

            if ($role->group == 'zevo') {
                $stickCount = config('zevolifesettings.stories.zsa');
                $stickWarningMsg = trans('feed.message.max_four_feed');
            }


            if ($action == 'stick') {
                if ($feed->is_stick == 1) {
                    $data['message'] = trans('feed.message.feed_already_stick');
                } else {
                    $companyId = ($role->group != 'zevo') ? $company->id : null;

                    $sticked_feed_count = Feed::where('is_stick', 1)
                        ->where('company_id', $companyId)
                        ->count();

                    if ($feed->company_id != $companyId) {
                        $data['message'] = trans('feed.message.stick_only_your_feed');
                    } elseif ($sticked_feed_count >= $stickCount) {
                        $data['message'] = $stickWarningMsg;
                    } else {
                        $sticked = $feed->stickUnstick($action);
                        if ($sticked) {
                            $data['status']  = 1;
                            $data['message'] = trans('feed.message.feed_stick_successfully');
                        } else {
                            $data['message'] = trans('feed.message.failed_stick_feed');
                        }
                    }
                }
            } else {
                if ($feed->is_stick == 0) {
                    $data['message'] = trans('feed.message.feed_unstick');
                } else {
                    $sticked = $feed->stickUnstick($action);
                    if ($sticked) {
                        $data['status']  = 1;
                        $data['message'] = trans('feed.message.feed_unstick_successfully');
                    } else {
                        $data['message'] = trans('feed.message.failed_unstick_feed');
                    }
                }
            }

            return response()->json($data, 200);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType($role = '', $companiesDetails = [])
    {
        $groupType        = config('zevolifesettings.content_company_group_type');
        $companyGroupType = [];
        if ($role == 'reseller') {
            unset($groupType[1]);
        }
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        foreach ($groupType as $value) {
            switch ($value) {
                case 'Zevo':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
                    break;
                case 'Parent':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', true);
                    if ($role == 'reseller') {
                        $companies->where('id', $companiesDetails->id);
                    }
                    $companies = $companies->get()
                        ->toArray();
                    break;
                case 'Child':
                    $companies      = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNotNull('parent_id')
                        ->where('is_reseller', false);
                    if ($role == 'reseller') {
                        $companies->where('parent_id', $companiesDetails->id);
                    }
                    $companies = $companies->get()
                        ->toArray();
                    break;
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

    /**
     * Display the clone html in which only start and end date can be editable
     * @param Request $request
     * @param Feed $feed
     * @return View
     */
    public function clone(Request $request, Feed $feed)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $companyData = $user->company()->get()->first();

        if ($role->group == 'reseller' || $companyData->is_reseller) {
            abort(403);
        }

        try {
            $data = array();
            $data = $feed->feedEditData();

            $is_visible = 0;
            if ($feed->company_id == null) {
                $is_visible = 1;
            } elseif ($companyData) {
                if ($feed->company_id == $companyData->id) {
                    $is_visible = 1;
                }
            }
            $data['is_visible']    = $is_visible;
            $data['company']       = $this->getAllCompaniesGroupType($role->group, $companyData);
            $data['isSA']          = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));
            $data['feed_types']    = config('zevolifesettings.feed_type');
            $data['timezoneArray'] = Timezone::pluck('name', 'name')->toArray();
            $data['ga_title']      = trans('page_title.feeds.edit');

            $data['appBackgroundImage']    = true;
            $data['portalBackgroundImage'] = true;
            if ($role->group == 'company') {
                $data['portalBackgroundImage'] = false;
            } elseif ($role->group == 'reseller') {
                if ($companyData->parent_id == null) {
                    $data['appBackgroundImage']    = false;
                } else {
                    $data['appBackgroundImage']    = $companyData->allow_app;
                    $data['portalBackgroundImage'] = $companyData->allow_portal;
                }
            }

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 2)->pluck('name', 'id')->toArray();
            }

            return \view('admin.feed.clone', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.feeds.index')->with('message', $messageData);
        }
    }

    /**
     * Save the cloned story
     * @param Request $request
     * @param Feed $feed
     * @return RedirectResponse
     */
    public function storeClone(Request $request, Feed $feed)
    {
        if (!access()->allow('clone-story')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->cloneEntity($feed, $request->all());
            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('feed.message.data_clone_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('feed.message.something_wrong_try_again'),
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
}
