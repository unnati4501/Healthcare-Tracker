<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBadgeRequest;
use App\Http\Requests\Admin\EditBadgeRequest;
use App\Models\Badge;
use App\Models\ChallengeTarget;
use App\Models\Exercise;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class BadgeController
 *
 * @package App\Http\Controllers\Admin
 */
class BadgeController extends Controller
{
    /**
     * variable to store the model object
     * @var Badge
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Badge $model ;
     */
    public function __construct(Badge $model, AuditLogRepository $auditLogRepository)
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
        Breadcrumbs::for('badge.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Badges');
        });
        Breadcrumbs::for('badge.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Badges', route('admin.badges.index'));
            $trail->push('Add Badge');
        });
        Breadcrumbs::for('badge.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Badges', route('admin.badges.index'));
            $trail->push('Edit Badge');
        });
        Breadcrumbs::for('badge.getmasterclasslist', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Badges', route('admin.badges.index'));
            $trail->push('Masterclass Badges');
        });
    }
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user    = Auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('manage-badge') || ($role->group == 'reseller' && $company->parent_id == null) || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');
            $data['badgeTypes'] = config('zevolifesettings.badgeTypes');
            $data['isSA']       = ($role->group == 'zevo');
            $data['expire']     = array('all' => 'All', 'yes' => 'Yes', 'no' => 'No');
            $data['ga_title']   = trans('page_title.badges.badges_list');
            return \view('admin.badge.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('badge.message.something_wrong'), 400)
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
        if (!access()->allow('create-badge') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['badgeTypes'] = config('zevolifesettings.createBadgeTypes');
            $role               = getUserRole();

            $data['defaultBadge']           = 'general';
            $data['challenge_targets']      = ChallengeTarget::where("is_excluded", 0)->pluck('name', 'id')->toArray();
            $data['ongoingChallengeTarget'] = config('zevolifesettings.ongoingChallengeTarget');
            $data['exercises']              = Exercise::pluck('title', 'id')->toArray();
            $data['exerciseType']           = Exercise::pluck('type', 'id')->toArray();
            $data['uoms']                   = array();
            $data['uom_data']               = config('zevolifesettings.uom');
            $data['ga_title']               = trans('page_title.badges.create');

            return \view('admin.badge.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateBadgeRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateBadgeRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-badge') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            if ($payload['unite1'] == 'general') {
                if ($payload['badge_target'] == 4) {
                    $getBedgeData = Badge::where("challenge_target_id", $payload['badge_target'])
                        ->where("target", $payload['target_values'])
                        ->where("model_id", $payload['excercise_type'])
                        ->where("uom", $payload['unite'])
                        ->first();
                } else {
                    $getBedgeData = Badge::where("challenge_target_id", $payload['badge_target'])
                        ->where("target", $payload['target_values'])
                        ->first();
                }

                if (!empty($getBedgeData)) {
                    return redirect()->back()->withInput()->withErrors(trans('badge.validation.badge_already_exists'));
                }
            }
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($payload);

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Badge added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('badge.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.badges.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('badge.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.badges.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Badge $badge
     * @return View
     */
    public function edit(Request $request, Badge $badge)
    {
        $role = getUserRole();
        if (!access()->allow('update-badge') || $role->group != 'zevo') {
            abort(403);
        }

        if (!is_null(\Auth::user()->company->first()) && $badge->company_id != \Auth::user()->company->first()->id) {
            abort(403);
        }

        try {
            $data             = array();
            $data             = $badge->badgeEditData();
            $data['ga_title'] = trans('page_title.badges.edit');
            return \view('admin.badge.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditBadgeRequest $request ,Badge $badge
     *
     * @return RedirectResponse
     */
    public function update(EditBadgeRequest $request, Badge $badge)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-badge') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $badge->toArray());
            $data    = $badge->updateEntity($payload);

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Badge updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('badge.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.badges.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('badge.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.badges.edit', $badge->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getBadges(Request $request)
    {
        $user    = Auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('manage-badge') || ($role->group == 'reseller' && $company->parent_id == null) || ($role->group == 'reseller' && !$company->allow_app)) {
            return response()->json([
                'message' => trans('badge.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Badge $badge
     *
     * @return RedirectResponse
     */

    public function delete(Badge $badge)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-badge') || $role->group != 'zevo') {
            abort(403);
        }

        if (!is_null(\Auth::user()->company->first()) && $badge->company_id != \Auth::user()->company->first()->id) {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_badge_id' => $badge->id,'deleted_badge_name' => $badge->title]);
            $this->auditLogRepository->created("Badge deleted successfully", $logs);

            return $badge->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Group $group
     *
     * @return View
     */

    public function getDetails(Badge $badge)
    {

        if (!access()->allow('view-badge')) {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['badge']      = $badge;
            return \view('admin.badge.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Group $group
     *
     * @return RedirectResponse
     */
    public function getMembersList(Request $request, Badge $badge)
    {
        try {
            return $badge->getMembersTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function masterclassbadgeList(Request $request)
    {
        if (!access()->allow('view-badge')) {
            abort(403);
        }

        try {
            $role               = getUserRole();
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');
            $data['badgeTypes'] = config('zevolifesettings.badgeTypes');
            $data['isSA']       = ($role->group == 'zevo');
            $data['expire']     = array('all' => 'All', 'yes' => 'Yes', 'no' => 'No');
            $data['ga_title']   = trans('page_title.badges.masterclass_badge');

            return \view('admin.badge.getmasterclasslist', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getmasterclasslist(Request $request)
    {
        if (!access()->allow('view-badge')) {
            return response()->json([
                'message' => trans('badge.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all(), 'masterclass');
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('badge.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.badges.index')->with('message', $messageData);
        }
    }
}
