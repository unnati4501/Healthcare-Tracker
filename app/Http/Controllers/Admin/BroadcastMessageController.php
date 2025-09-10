<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBoradcastMessageRequest;
use App\Http\Requests\Admin\EditBoradcastMessageRequest;
use App\Models\BroadcastMessage;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Breadcrumbs;

class BroadcastMessageController extends Controller
{

    /**
     * variable to store the model object
     * @var BroadcastMessage $model
     */
    protected $model;

    /**
     * variable to store the group model object
     * @var BroadcastMessage $model
     */
    protected $groupModel;

    /**
     * contructor to initialize model object
     * @param BroadcastMessage $model
     */
    public function __construct(BroadcastMessage $model, Group $groupModel)
    {
        $this->model      = $model;
        $this->groupModel = $groupModel;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('broadcast.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Group Broadcast');
        });
        Breadcrumbs::for('broadcast.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Group Broadcast', route('admin.broadcast-message.index'));
            $trail->push('Add Broadcast Message');
        });
        Breadcrumbs::for('broadcast.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Group Broadcast', route('admin.broadcast-message.index'));
            $trail->push('Edit Broadcast Message');
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (!access()->allow('manage-broadcast-message')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $timezone   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $role       = getUserRole($user);
            $groupsType = [];

            if ($role->group == 'zevo') {
                $groupsType = [
                    'inter_company' => 'Intercompany Challenge',
                    'masterclass'   => 'Masterclass',
                ];
            } elseif ($role->group == 'company') {
                $groupsType = [
                    'inter_company' => 'Intercompany Challenge',
                    'team'          => 'Team challenge',
                    'company_goal'  => 'Company Goal',
                    'individual'    => 'Individual challenge',
                    'masterclass'   => 'Masterclass',
                    'public'        => 'Public Group',
                    'private'       => 'Private Group',
                ];
            } elseif ($role->group == 'reseller') {
                $company = $user->company()
                    ->select('companies.id', 'companies.allow_app')
                    ->first();
                if ($company->allow_app) {
                    $groupsType = [
                        'inter_company' => 'Intercompany Challenge',
                        'team'          => 'Team challenge',
                        'company_goal'  => 'Company Goal',
                        'individual'    => 'Individual challenge',
                        'masterclass'   => 'Masterclass',
                        'public'        => 'Public Group',
                        'private'       => 'Private Group',
                    ];
                } else {
                    return view('errors.401');
                }
            }

            $data = [
                'timezone'        => $timezone,
                'format'          => config('zevolifesettings.date_format.moment_default_datetime'),
                'broadcastStatus' => config('zevolifesettings.broadcast_status_type'),
                'groupsType'      => $groupsType,
                'pagination'      => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'        => trans('page_title.broadcast-message.index'),
            ];

            return \view('admin.broadcast-message.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get all the broadcasts
     *
     * @return JSON
     */
    public function getBroadcasts(Request $request)
    {
        if (!access()->allow('manage-broadcast-message')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Get groups by group type
     *
     * @return mixed string|exception
     */
    public function getGroups(Request $request)
    {
        if (!access()->allow('manage-broadcast-message')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }
        try {
            return $this->model->getGroups($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        if (!access()->allow('create-broadcast-message')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $role       = getUserRole($user);
            $groupsType = [];

            if ($role->group == 'zevo') {
                $groupsType = [
                    'inter_company' => 'Intercompany Challenge',
                    'masterclass'   => 'Masterclass',
                ];
            } elseif ($role->group == 'company') {
                $groupsType = [
                    'inter_company' => 'Intercompany Challenge',
                    'team'          => 'Team challenge',
                    'company_goal'  => 'Company Goal',
                    'individual'    => 'Individual challenge',
                    'masterclass'   => 'Masterclass',
                    'public'        => 'Public Group',
                    'private'       => 'Private Group',
                ];
            } elseif ($role->group == 'reseller') {
                $company = $user->company()
                    ->select('companies.id', 'companies.allow_app')
                    ->first();
                if ($company->allow_app) {
                    $groupsType = [
                        'inter_company' => 'Intercompany Challenge',
                        'team'          => 'Team challenge',
                        'company_goal'  => 'Company Goal',
                        'individual'    => 'Individual challenge',
                        'masterclass'   => 'Masterclass',
                        'public'        => 'Public Group',
                        'private'       => 'Private Group',
                    ];
                } else {
                    return view('errors.401');
                }
            }

            $data = [
                'groupsType'          => $groupsType,
                'scheduledVisibility' => 'hide',
                'ga_title'            => trans('page_title.broadcast-message.create'),
            ];

            return \view('admin.broadcast-message.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::route('admin.broadcast-message.index')->with('message', [
                'data'   => trans('broadcast.message.something_wrong'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateBoradcastMessageRequest  $request
     * @return JSON
     */
    public function store(CreateBoradcastMessageRequest $request)
    {
        if (!access()->allow('create-broadcast-message')) {
            return response()->json([
                'message' => trans('broadcast.message.unauthorized_access'),
            ], 401);
        }

        try {
            \DB::beginTransaction();
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone);

            if (is_null($request->instant_broadcast)) {
                if ($now > Carbon::parse($request->schedule_date_time, $appTimezone)) {
                    return response()->json([
                        'message' => trans('broadcast.message.schedule_broadcast_message'),
                    ], 422);
                }
            }

            $data = $this->model->storeEntity($request->all());
            if ($data && is_array($data)) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => $data['message'],
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('broadcast.message.something_wrong_try_again'),
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('broadcast.message.something_wrong'),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BroadcastMessage  $broadcast
     * @return \Illuminate\Http\Response
     */
    public function edit(BroadcastMessage $broadcast)
    {
        if (!access()->allow('edit-broadcast-message')) {
            abort(403);
        }

        try {
            $user        = auth()->user();
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now         = now($appTimezone);
            $company     = $user->company()->select('companies.id')->first();
            $companyId   = (!is_null($company) ? $company->id : null);

            if ($broadcast->company_id != $companyId) {
                return view('errors.401');
            }

            if ($broadcast->type == 'instant') {
                return view('errors.401');
            } elseif ($broadcast->type == 'scheduled' && $broadcast->status == 1) {
                $scheduledAt = Carbon::parse("{$broadcast->scheduled_at}", $appTimezone)->toDateTimeString();
                $diff        = $now->diffInMinutes($scheduledAt, false);
                if ($diff <= 15) {
                    return \Redirect::route('admin.broadcast-message.index')->with('message', [
                        'data'   => trans('broadcast.message.edit_broadcast_sometime'),
                        'status' => 0,
                    ]);
                }
            }

            $groupsType = config('zevolifesettings.broadcast_group_type');
            $group      = $broadcast->group()->select('groups.id', 'groups.title')->first();

            $data = [
                'groupsType'          => [$broadcast->group_type => $groupsType[$broadcast->group_type]],
                'group'               => [$group->id => $group->title],
                'broadcast'           => $broadcast,
                'scheduledVisibility' => (($broadcast->type == 'scheduled') ? '' : 'hide'),
                'scheduled_at'        => $broadcast->scheduled_at->setTimezone($timezone),
                'ga_title'            => trans('page_title.broadcast-message.edit'),
            ];

            return \view('admin.broadcast-message.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::route('admin.broadcast-message.index')->with('message', [
                'data'   => trans('broadcast.message.something_wrong'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  EditBoradcastMessageRequest  $request
     * @param  \App\Models\BroadcastMessage  $broadcastMessage
     * @return \Illuminate\Http\Response
     */
    public function update(EditBoradcastMessageRequest $request, BroadcastMessage $broadcast)
    {
        if (!access()->allow('edit-broadcast-message')) {
            return response()->json([
                'message' => trans('broadcast.message.unauthorized_access'),
            ], 401);
        }

        try {
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone);

            if ($broadcast->type == 'scheduled' && $broadcast->status == 1) {
                $scheduledAt = Carbon::parse("{$broadcast->scheduled_at}", $appTimezone)->toDateTimeString();
                $diff        = $now->diffInMinutes($scheduledAt, false);
                if ($diff <= 15) {
                    return response()->json([
                        'message' => trans('broadcast.message.edit_broadcast_sometime'),
                    ], 422);
                }
            }

            \DB::beginTransaction();
            $data = $broadcast->updateEntity($request->all());
            if ($data && is_array($data)) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => $data['message'],
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('broadcast.message.something_wrong_try_again'),
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('broadcast.message.something_wrong'),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BroadcastMessage  $broadcastMessage
     * @return \Illuminate\Http\Response
     */
    public function delete(BroadcastMessage $broadcast)
    {
        if (!access()->allow('delete-broadcast-message')) {
            return response()->json([
                'message' => trans('broadcast.message.unauthorized_access'),
            ], 401);
        }

        try {
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone);

            if (!is_null($broadcast->scheduled_at)) {
                $scheduledAt = Carbon::parse("{$broadcast->scheduled_at}", $appTimezone)->toDateTimeString();
                $diff        = $now->diffInMinutes($scheduledAt, false);
                if ($diff <= 15 && $broadcast->status == 1) {
                    return ['deleted' => 'time_limit'];
                }
            }

            return $broadcast->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'message' => trans('broadcast.message.something_wrong_try_again'),
            ], 500);
        }
    }
}