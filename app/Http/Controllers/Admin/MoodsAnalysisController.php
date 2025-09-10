<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Mood;
use App\Models\MoodTag;
use App\Models\MoodTagUser;
use App\Models\MoodUser;
use App\Models\User;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Class MoodsAnalysisController
 *
 * @package App\Http\Controllers\Admin
 */
class MoodsAnalysisController extends Controller
{
    /**
     * variables to store the model object
     */
    protected $mood;
    protected $moodTag;
    protected $moodUser;
    protected $moodTagUser;
    protected $company;
    protected $user;

    /**
     * contructor to initialize model object
     * @param Mood $mood, MoodTag $moodTag, MoodUser $moodUser, MoodTagUser $moodTagUser, Company $company, User $user
     */
    public function __construct(Mood $mood, MoodTag $moodTag, MoodUser $moodUser, MoodTagUser $moodTagUser, Company $company, User $user)
    {
        $this->mood        = $mood;
        $this->moodTag     = $moodTag;
        $this->moodUser    = $moodUser;
        $this->moodTagUser = $moodTagUser;
        $this->company     = $company;
        $this->user        = $user;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of moods module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('moodAnalysis.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.analysis.title.dashboard'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $loggedInuser = Auth()->user();
        $role         = getUserRole();
        $userCompany  = $loggedInuser->company()->first();
        if (!access()->allow('view-moods-analysis') || ($role->group == 'reseller' && $userCompany->parent_id == null)) {
            abort(403);
        }
        if ($role->group == 'reseller' && $userCompany->parent_id != null && !$userCompany->allow_app) {
            abort(403);
        }
        try {
            $role = getUserRole();
            $data = array();

            $data['companies']   = $this->company->pluck('name', 'id')->toArray();
            $data['departments'] = [];

            if ($role->group == 'company' || $role->group == 'reseller') {
                $data['departments'] = Auth::user()->company->first()->departments->pluck('name', 'id');
            }
            $data['ga_title'] = trans('page_title.moodAnalysis');
            return \view('admin.moodsAnalysis.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.analysis.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodAnalysis.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return json
     */
    public function getUsersData(Request $request)
    {
        $loggedInuser   = Auth()->user();
        $role           = getUserRole();
        $userCompany    = $loggedInuser->company()->first();
        if (!access()->allow('view-moods-analysis') || ($role->group == 'reseller' && $userCompany->parent_id == null)) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        if ($role->group == 'reseller' && $userCompany->parent_id != null && !$userCompany->allow_app) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        try {
            $payload = $request->all();
            $duration = (!empty($payload['duration']) && is_numeric($payload['duration'])) ?  $payload['duration'] : 7;

            $whereConditions = [
                'users.is_blocked'     => 0,
                'users.can_access_app' => 1,
            ];
            if (isset($payload['company'])) {
                $whereConditions['user_team.company_id'] = $payload['company'];

                $departments = Department::where('company_id', $payload['company'])
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            if (isset($departments) && isset($payload['department']) && in_array($payload['department'], $departments)) {
                $whereConditions['user_team.department_id'] = $payload['department'];
            }

            $totalUsers = $this->user
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->where($whereConditions)
                ->get()
                ->count();

            $activeUsers = $this->moodUser
                ->leftJoin('users', 'mood_user.user_id', '=', 'users.id')
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->where($whereConditions)
                ->where('mood_user.created_at', '>=', Carbon::now()->subDays($duration)->toDateTimeString())
                ->groupBy('mood_user.user_id')
                ->get()
                ->count();

            $passiveUsers = $totalUsers - $activeUsers;

            $data = [
                'totalUsers'   => $totalUsers,
                'activeUsers'  => $activeUsers,
                'passiveUsers' => $passiveUsers,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('moods.analysis.messages.something_wrong_try_again'));
        }
    }

    /**
     * @param Request $request
     * @return json
     */
    public function getMoodsData(Request $request)
    {
        $loggedInuser   = Auth()->user();
        $role           = getUserRole();
        $userCompany    = $loggedInuser->company()->first();
        if (!access()->allow('view-moods-analysis') || ($role->group == 'reseller' && $userCompany->parent_id == null)) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        if ($role->group == 'reseller' && $userCompany->parent_id != null && !$userCompany->allow_app) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        try {
            $payload = $request->all();
            $duration = (!empty($payload['duration']) && is_numeric($payload['duration'])) ?  $payload['duration'] : 7;
            $whereConditions = [];

            $whereConditions = [
                'users.is_blocked'     => 0,
                'users.can_access_app' => 1,
            ];

            if (isset($payload['company'])) {
                $whereConditions['user_team.company_id'] = $payload['company'];
            }

            if (isset($payload['company']) && isset($payload['department'])) {
                $whereConditions['user_team.department_id'] = $payload['department'];
            }

            $activeUsers = $this->moodUser
                ->leftJoin('users', 'mood_user.user_id', '=', 'users.id')
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->where($whereConditions)
                ->where('mood_user.created_at', '>=', Carbon::now()->subDays($duration)->toDateTimeString())
                ->get()
                ->count();

            $moodUserData = $this->moodUser
                ->leftJoin('moods', 'mood_user.mood_id', '=', 'moods.id')
                ->leftJoin('users', 'mood_user.user_id', '=', 'users.id')
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->select('moods.title as key', DB::raw("COUNT('mood_user.mood_id') as value"))
                ->where($whereConditions)
                ->where('mood_user.created_at', '>=', Carbon::now()->subDays($duration)->toDateTimeString())
                ->groupBy('mood_user.mood_id')
                ->get()
                ->pluck('value', 'key')
                ->toArray();

            $moods = $this->mood
                ->get()
                ->pluck('title')
                ->toArray();

            $data = [];
            foreach ($moods as $value) {
                if (array_key_exists($value, $moodUserData)) {
                    $data[] = [
                        'key'   => $value,
                        'value' => (float) number_format(($moodUserData[$value] * 100 / $activeUsers), 1, '.', ''),
                    ];
                } else {
                    $data[] = [
                        'key'   => $value,
                        'value' => 0,
                    ];
                }
            }

            $graphData['labels'] = array_column($data, 'key');
            $graphData['data']   = array_column($data, 'value');

            return response()->json($graphData);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('moods.analysis.messages.something_wrong_try_again'));
        }
    }

    /**
     * @param Request $request
     * @return json
     */
    public function getTagsData(Request $request)
    {
        $loggedInuser   = Auth()->user();
        $role           = getUserRole();
        $userCompany    = $loggedInuser->company()->first();
        if (!access()->allow('view-moods-analysis') || ($role->group == 'reseller' && $userCompany->parent_id == null)) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        if ($role->group == 'reseller' && $userCompany->parent_id != null && !$userCompany->allow_app) {
            return response()->json([
                'message' => trans('moods.analysis.messages.unauthorized_access'),
            ], 422);
        }
        try {
            $payload = $request->all();
            $duration = (!empty($payload['duration']) && is_numeric($payload['duration'])) ?  $payload['duration'] : 7;

            $whereConditions = [
                'users.is_blocked'     => 0,
                'users.can_access_app' => 1,
            ];

            if (isset($payload['company'])) {
                $whereConditions['user_team.company_id'] = $payload['company'];
            }

            if (isset($payload['company']) && isset($payload['department'])) {
                $whereConditions['user_team.department_id'] = $payload['department'];
            }

            $payload['activeUsers'] = $this->moodUser
                ->leftJoin('users', 'mood_user.user_id', '=', 'users.id')
                ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->where($whereConditions)
                ->where('mood_user.created_at', '>=', Carbon::now()->subDays($duration)->toDateTimeString())
                ->get()
                ->count();

            $data = $this->getTagDataSets($payload, $whereConditions);

            $graphData['labels'] = array_column($data, 'key');
            $graphData['data']   = array_column($data, 'value');

            if (isset($payload['mood'])) {
                $whereConditions['moods.title'] = $payload['mood'];

                $stackedData = $this->getTagDataSets($payload, $whereConditions);

                $graphData['stackedData'] = array_column($stackedData, 'value');
            }

            return response()->json($graphData);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('moods.analysis.messages.something_wrong_try_again'));
        }
    }

    /**
     * @param $payload, $whereConditions
     * @return array
     */
    public function getTagDataSets($payload, $whereConditions)
    {
        $duration = (!empty($payload['duration']) && is_numeric($payload['duration'])) ?  $payload['duration'] : 7;
        $tagUserData = $this->moodTagUser
            ->leftJoin('mood_tags', 'mood_tag_user.tag_id', '=', 'mood_tags.id')
            ->leftJoin('moods', 'mood_tag_user.mood_id', '=', 'moods.id')
            ->leftJoin('users', 'mood_tag_user.user_id', '=', 'users.id')
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->select('mood_tags.tag as key', DB::raw("COUNT('mood_tag_user.tag_id') as value"))
            ->where($whereConditions)
            ->where('mood_tag_user.created_at', '>=', Carbon::now()->subDays($duration)->toDateTimeString())
            ->groupBy('mood_tag_user.tag_id')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $tags = $this->moodTag
            ->get()
            ->pluck('tag')
            ->toArray();

        $data = [];
        foreach ($tags as $value) {
            if (array_key_exists($value, $tagUserData)) {
                $data[] = [
                    'key'   => $value,
                    'value' => (float) number_format(($tagUserData[$value] * 100 / $payload['activeUsers']), 1, '.', ''),
                ];
            } else {
                $data[] = [
                    'key'   => $value,
                    'value' => 0,
                ];
            }
        }

        return $data;
    }
}
