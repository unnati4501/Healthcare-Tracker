<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePersonalChallengeRequest;
use App\Http\Requests\Admin\EditPersonalChallengeRequest;
use App\Models\PersonalChallenge;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class PersonalChallengeController
 *
 * @package App\Http\Controllers\Admin
 */
class PersonalChallengeController extends Controller
{
    /**
     * variable to store the model object
     * @var PersonalChallenge
     */
    protected $model;

    /**
     * variable to store user object
     * @var user
     */
    protected $user;

    /**
     * contructor to initialize model object
     * @param PersonalChallenge $model ;
     */
    public function __construct(PersonalChallenge $model)
    {
        $this->model = $model;

        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of personal challenge module
     */
    private function bindBreadcrumbs()
    {

        $this->middleware(function ($request, $next) {
            $this->user        = Auth::user();
            $isChallengeAccess = getCompanyPlanAccess($this->user, 'my-challenges');
            Breadcrumbs::for('personalChallenges.index', function ($trail) use ($isChallengeAccess) {
                $trail->push('Home', route('dashboard'));
                if ($isChallengeAccess) {
                    $trail->push(trans('personalChallenge.title.manage'));
                } else {
                    $trail->push(trans('personalChallenge.title.manage_goal'));
                }
            });
            Breadcrumbs::for('personalChallenges.create', function ($trail) use ($isChallengeAccess) {
                $trail->push('Home', route('dashboard'));
                if ($isChallengeAccess) {
                    $trail->push(trans('personalChallenge.title.manage'), route('admin.personalChallenges.index'));
                    $trail->push(trans('personalChallenge.title.add'));
                } else {
                    $trail->push(trans('personalChallenge.title.manage_goal'), route('admin.personalChallenges.index'));
                    $trail->push(trans('personalChallenge.title.add_goal'));
                }
            });
            Breadcrumbs::for('personalChallenges.edit', function ($trail) use ($isChallengeAccess) {
                $trail->push('Home', route('dashboard'));
                if ($isChallengeAccess) {
                    $trail->push(trans('personalChallenge.title.manage'), route('admin.personalChallenges.index'));
                    $trail->push(trans('personalChallenge.title.edit'));
                } else {
                    $trail->push(trans('personalChallenge.title.manage_goal'), route('admin.personalChallenges.index'));
                    $trail->push(trans('personalChallenge.title.edit_goal'));
                }
            });
            return $next($request);
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $loggedInuser   = auth()->user();
        $role           = getUserRole();
        $company        = $loggedInuser->company->first();
        if (!access()->allow('manage-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        try {
            $data                   = array();
            $data['mailTitle']      = trans('personalChallenge.title.manage');
            $data['ga_title']       = trans('page_title.personalChallenges.list');
            $data['challenge_type'] = trans('personalChallenge.filter.challenge_type');
            if (!getCompanyPlanAccess($loggedInuser, 'my-challenges')) {
                $data['mailTitle']      = trans('personalChallenge.title.manage_goal');
                $data['ga_title']       = trans('page_title.personalChallenges.list_goal');
                $data['challenge_type'] = trans('personalChallenge.filter.goal_type');
            }
            $data['pagination']                      = config('zevolifesettings.datatable.pagination.short');
            $data['challengeTypeData']               = config('zevolifesettings.personalChallengeTypes');
            $data['personalRoutineChallengeSubType'] = config('zevolifesettings.personalRoutineChallengeSubType');
            $data['personalFitnessChallengeSubType'] = config('zevolifesettings.personalFitnessChallengeSubType');
            $data['personalHabitChallengeSubType']   = config('zevolifesettings.personalHabitChallengeSubType');
            $challengeType                           = request()->get('challengeType');
            $challengeSubType                        = [];
            if ($challengeType) {
                if ($challengeType == 'routine') {
                    $challengeSubType = $data['personalRoutineChallengeSubType'];
                } elseif ($challengeType == 'habit') {
                    $challengeSubType = $data['personalHabitChallengeSubType'];
                } else {
                    $challengeSubType = $data['personalFitnessChallengeSubType'];
                }
            }

            $data['challengeSubType'] = $challengeSubType;
            $data['recursive']        = [1 => 'Yes', 0 => 'No'];

            $data['planAccess'] = getCompanyPlanAccess($loggedInuser, 'my-challenges');
            return \view('admin.personalChallenge.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $loggedInuser    = auth()->user();
        $role            = getUserRole();
        $company         = $loggedInuser->company->first();
        if (!access()->allow('create-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        try {
            $data                    = array();
            $data['ga_title']        = trans('page_title.personalChallenges.create');
            $data['mailTitle']       = trans('personalChallenge.title.add');
            $data['placeholderName'] = trans('personalChallenge.form.placeholders.name');
            $data['planAccess']      = getCompanyPlanAccess($loggedInuser, 'my-challenges');
            if (!$data['planAccess']) {
                $data['ga_title']        = trans('page_title.personalChallenges.create_goal');
                $data['mailTitle']       = trans('personalChallenge.title.add_goal');
                $data['placeholderName'] = trans('personalChallenge.form.placeholders.name_goal');
            }
            return \view('admin.personalChallenge.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreatePersonalChallengeRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreatePersonalChallengeRequest $request)
    {
        $loggedInuser       = auth()->user();
        $role               = getUserRole();
        $company            = $loggedInuser->company->first();
        $planAccess         = getCompanyPlanAccess($loggedInuser, 'my-challenges');
        if (!access()->allow('create-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => ($planAccess) ? trans('personalChallenge.messages.added') : trans('personalChallenge.messages.added_goal'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.personalChallenges.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , PersonalChallenge $personalChallenge
     * @return View
     */
    public function edit(Request $request, PersonalChallenge $personalChallenge)
    {
        $loggedInuser   = auth()->user();
        $role           = getUserRole();
        $company        = $loggedInuser->company->first();
        if (!access()->allow('update-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        if (!empty($company) && $personalChallenge->company_id != $company->id) {
            abort(403);
        }

        if (empty($company) && $personalChallenge->company_id != null) {
            abort(403);
        }

        try {
            $data                    = array();
            $data                    = $personalChallenge->getUpdateData();
            $data['ga_title']        = trans('page_title.personalChallenges.edit');
            $data['mailTitle']       = trans('personalChallenge.title.edit');
            $data['placeholderName'] = trans('personalChallenge.form.placeholders.name');
            $data['planAccess']      = getCompanyPlanAccess($loggedInuser, 'my-challenges');
            if (!$data['planAccess']) {
                $data['ga_title']        = trans('page_title.personalChallenges.edit_goal');
                $data['mailTitle']       = trans('personalChallenge.title.edit_goal');
                $data['placeholderName'] = trans('personalChallenge.form.placeholders.name_goal');
            }
            return \view('admin.personalChallenge.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditPersonalChallengeRequest $request, PersonalChallenge $personalChallenge
     *
     * @return RedirectResponse
     */
    public function update(EditPersonalChallengeRequest $request, PersonalChallenge $personalChallenge)
    {
        $loggedInuser       = auth()->user();
        $role               = getUserRole();
        $company            = $loggedInuser->company->first();
        $planAccess         = getCompanyPlanAccess($loggedInuser, 'my-challenges');
        if (!access()->allow('update-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data    = $personalChallenge->updateEntity($payload);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => ($planAccess) ? trans('personalChallenge.messages.updated') : trans('personalChallenge.messages.updated_goal'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.personalChallenges.edit', $personalChallenge->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.personalChallenges.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getChallenges(Request $request)
    {
        $loggedInuser   = auth()->user();
        $role           = getUserRole();
        $company        = $loggedInuser->company->first();
        if (!access()->allow('manage-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            return response()->json([
                'message' => trans('personalChallenge.messages.unauthorized'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  PersonalChallenge $personalChallenge
     *
     * @return RedirectResponse
     */
    public function delete(PersonalChallenge $personalChallenge)
    {
        $loggedInuser    = auth()->user();
        $role           = getUserRole();
        $company        = $loggedInuser->company->first();
        if (!access()->allow('delete-personal-challenge') || ($role->group == 'reseller' && !$company->allow_app)) {
            abort(403);
        }

        if (!empty($company) && $personalChallenge->company_id != $company->id) {
            abort(403);
        }

        if (empty($company) && $personalChallenge->company_id != null) {
            abort(403);
        }

        try {
            return $personalChallenge->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('personalChallenge.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
