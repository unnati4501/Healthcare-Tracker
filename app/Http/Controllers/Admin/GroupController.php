<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateGroupRequest;
use App\Http\Requests\Admin\EditGroupRequest;
use App\Models\Company;
use App\Models\Group;
use App\Models\SubCategory;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class GroupController
 *
 * @package App\Http\Controllers\Admin
 */
class GroupController extends Controller
{
    /**
     * variable to store the model object
     * @var Group
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param Group $model ;
     */
    public function __construct(Group $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('group.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Groups');
        });
        Breadcrumbs::for('group.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Groups', route('admin.groups.index'));
            $trail->push('Add Group');
        });
        Breadcrumbs::for('group.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Groups', route('admin.groups.index'));
            $trail->push('Edit Group');
        });
        Breadcrumbs::for('group.view', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Groups', route('admin.groups.index'));
            $trail->push('Group Detail');
        });
        Breadcrumbs::for('group.reportabuse', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Groups', route('admin.groups.index'));
            $trail->push('Report Abuse');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();

        if (!access()->allow('manage-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }
        try {
            $data                  = array();
            $data['pagination']    = config('zevolifesettings.datatable.pagination.long');
            $data['subCategories'] = SubCategory::where(['category_id' => 3])
                ->where('status', 1)
                ->where('is_excluded', 0)
                ->orderBy('id', 'ASC')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $data['otherGroupSubCategories'] = SubCategory::where(['category_id' => 3])
                ->where('status', 1)
                ->where('is_excluded', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $data['groupTypes'] = [
                'all'     => 'All',
                'public'  => 'Public',
                'private' => 'Private',
            ];
            $data['ga_title'] = trans('page_title.groups.groups_list');
            return \view('admin.group.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('create-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }
        try {
            $data                = array();
            $data['categories']  = $this->model->subcategories();
            $data['companyData'] = $this->model->getTeamMembersData();
            $data['userId']      = $user->id;

            if (!is_null(\Auth::user()->company->first())) {
                foreach ($data['companyData'] as $value) {
                    if ($value['id'] == \Auth::user()->company->first()->id) {
                        $data['departmentData'] = $value['departments'];
                    }
                }
            }
            $data['ga_title'] = trans('page_title.groups.create');
            return \view('admin.group.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateGroupRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateGroupRequest $request)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('create-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data    = $this->model->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('group.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.groups.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('group.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.groups.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Group $group
     * @return View
     */
    public function edit(Request $request, Group $group)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('update-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }

        if (!is_null($company) && $group->company_id != $company->id && $group->company_id != null) {
            abort(403);
        }

        try {
            $data           = array();
            $data           = $group->groupEditData();
            $data['userId'] = $user->id;

            if (!is_null(\Auth::user()->company->first())) {
                foreach ($data['companyData'] as $value) {
                    if ($value['id'] == \Auth::user()->company->first()->id) {
                        $data['departmentData'] = $value['departments'];
                    }
                }
            }

            if (!is_null($group->company_id)) {
                foreach ($data['companyData'] as $value) {
                    if ($value['id'] == $group->company_id) {
                        $data['departmentData'] = $value['departments'];
                    }
                }
            }

            $data['string'] = '#mainGroups';
            if (in_array($group->subcategory->short_name, ['masterclass', 'challenge'])) {
                $data['string'] = '#otherGroups';
            }
            $data['ga_title'] = trans('page_title.groups.edit');

            return \view('admin.group.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditGroupRequest $request ,Group $group
     *
     * @return RedirectResponse
     */
    public function update(EditGroupRequest $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $string  = isset($payload['string']) ? $payload['string'] : null;

            $data = $group->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('group.message.data_update_success'),
                    'status' => 1,
                ];

                $url = route('admin.groups.index') . $string;
                return redirect($url)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('group.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.groups.edit', $group->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getGroups(Request $request)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();

        if (!access()->allow('manage-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            return response()->json([
                'message' => trans('group.message.unauthorized_access'),
            ], 422);
        } elseif (!$company->allow_app) {
            return response()->json([
                'message' => trans('group.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Group $group
     *
     * @return RedirectResponse
     */

    public function delete(Group $group)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('delete-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }

        if (!is_null($company) && $group->company_id != $company->id && $group->company_id != null) {
            abort(403);
        }

        try {
            return $group->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Group $group
     *
     * @return View
     */

    public function getDetails(Group $group)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('view-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }

        if (!is_null($company) && $group->company_id != $company->id && $group->company_id != null) {
            abort(403);
        }

        try {
            $data                = array();
            $data['groupData']   = $group;
            $data['description'] = trim(html_entity_decode(strip_tags($group->description)), " \t\n\r\0\x0B\xC2\xA0");
            $data['pagination']  = config('zevolifesettings.datatable.pagination.long');
            $data['members']     = $group->members()
                ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                ->where('user_team.company_id', auth()->user()->company->first()->id)
                ->count();

            $data['string'] = '#mainGroups';
            if (in_array($group->subcategory->short_name, ['masterclass', 'challenge'])) {
                $data['string'] = '#otherGroups';
            }
            $data['ga_title'] = trans('page_title.groups.details');
            return \view('admin.group.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Group $group
     *
     * @return RedirectResponse
     */
    public function getMembersList(Request $request, Group $group)
    {
        try {
            return $group->getMembersTableData();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Group $group
     *
     * @return View
     */

    public function reportAbuse(Group $group)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('manage-group') || $role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        } elseif (!$company->allow_app) {
            abort(403);
        }

        if (!is_null($company) && $group->company_id != $company->id && $group->company_id != null) {
            abort(403);
        }

        try {
            $data               = array();
            $data['groupData']  = $group;
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');

            return \view('admin.group.reportabuse', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request ,Group $group
     *
     * @return RedirectResponse
     */

    public function getReportAbuseList(Request $request, Group $group)
    {
        try {
            return $group->getReportAbuseTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('group.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.groups.index')->with('message', $messageData);
        }
    }
}
