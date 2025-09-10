<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateRecipeRequest;
use App\Http\Requests\Admin\EditRecipeRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\Goal;
use App\Models\Recipe;
use App\Models\RecipeType;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class RecipeController
 *
 * @package App\Http\Controllers\Admin
 */
class RecipeController extends Controller
{
    /**
     * Recipe model object
     * @var Recipe
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * RecipeType model object
     * @var RecipeType
     */
    protected $recipeType;

    public function __construct(Recipe $model, RecipeType $recipeType, AuditLogRepository $auditLogRepository)
    {
        $this->model                = $model;
        $this->recipeType           = $recipeType;
        $this->auditLogRepository   = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of recipe module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('recipe.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Recipes');
        });
        Breadcrumbs::for('recipe.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Recipes', route('admin.recipe.index'));
            $trail->push('Add Recipe');
        });
        Breadcrumbs::for('recipe.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Recipes', route('admin.recipe.index'));
            $trail->push('Edit Recipe');
        });
        Breadcrumbs::for('recipe.view', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Recipes', route('admin.recipe.index'));
            $trail->push('Recipe Details');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $user                           = auth()->user();
        $role                           = getUserRole($user);
        $checkPlanAccessForReseller     = getDTAccessForParentsChildCompany($user, 'explore');
        if (!access()->allow('manage-recipe') || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $approved                     = $unapproved                     = 0;
            $data                         = array();
            $role                         = getUserRole();
            $data['timezone']             = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format']          = config('zevolifesettings.date_format.meditation_recepie_support_createdtime');
            $data['recipeSubCategories']  = SubCategory::where(['category_id' => 5, 'status' => 1])->pluck('name', 'id');
            $data['pagination']           = config('zevolifesettings.datatable.pagination.long');
            $data['approved']             = $approved;
            $data['unapproved']           = $unapproved;
            $data['statusColVisibility']  = false;
            $data['companyColVisibility'] = true;
            $data['recipeTypes']          = $this->recipeType->select('type_name', 'id')
                ->where('status', '1')
                ->get()->pluck('type_name', 'id')->toArray();

            if ($role->group == 'company') {
                $companyId = auth()->user()->company()->first()->id;

                $counts = Recipe::select(DB::raw('IFNULL(SUM(status = 1), 0) AS approved'), DB::raw('IFNULL(SUM(status = 0), 0) AS unapproved'))
                    ->whereRaw('id IN (SELECT recipe_id FROM recipe_company WHERE company_id = ?)', [$companyId])
                    ->whereRaw('(recipe.company_id = ? OR recipe.company_id IS NULL)', [$companyId])
                    ->first();

                $data['approved']             = $counts['approved'];
                $data['unapproved']           = $counts['unapproved'];
                $data['statusColVisibility']  = true;
                $data['companyColVisibility'] = false;
            } elseif ($role->group == 'reseller') {
                $companyDetails = auth()->user()->company()->first();
                $companyId      = $companyDetails->id;

                $counts = Recipe::select(DB::raw('IFNULL(SUM(status = 1), 0) AS approved'), DB::raw('IFNULL(SUM(status = 0), 0) AS unapproved'))
                    ->whereRaw('id IN (SELECT recipe_id FROM recipe_company WHERE company_id = ?)', [$companyId])
                    ->where(function ($query) use ($companyId, $companyDetails) {
                        $query->Where('recipe.company_id', $companyId);
                        $query->orWhere('recipe.company_id', null);
                        $query->orWhere('recipe.company_id', $companyDetails->parent_id);
                    })
                    ->first();

                $data['approved']   = $counts['approved'];
                $data['unapproved'] = $counts['unapproved'];

                $data['statusColVisibility']  = ($companyDetails->parent_id != null);
                $data['companyColVisibility'] = ($companyDetails->parent_id == null);

                if ($companyDetails->parent_id == null) {
                    $companies = Company::where('parent_id', $companyDetails->id)
                        ->whereNotNull('parent_id')
                        ->get()
                        ->pluck('name', 'id')
                        ->toArray();
                    $companies       = array_replace([$companyDetails->id => $companyDetails->name], $companies);
                    $data['company'] = array_replace(['zevo' => 'Zevo'], $companies);
                }
            } else {
                $data['company'] = array_replace(['zevo' => 'Zevo'], Company::all()->pluck('name', 'id')->toArray());
            }

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $tags         = CategoryTags::where("category_id", 5)->pluck('name', 'id')->toArray();
                $data['tags'] = array_replace(['NA' => 'NA'], $tags);
            }

            $data['ga_title'] = trans('page_title.recipe.recipe_list');
            return \view('admin.recipe.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return JSON
     */
    public function getRecipes(Request $request)
    {
        if (!access()->allow('manage-recipe')) {
            abort(403);
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
     *
     * @return Array
     */
    public function getFormStaticData(): array
    {
        $data         = [];
        $user         = auth()->user();
        $role         = getUserRole($user);
        $chefs        = [];
        $companyData  = $user->company()->first();
        $data['isSA'] = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));

        if ($role->group == "zevo") {
            $chefsUsers = User::with(['roles'])
                ->whereHas('roles', function ($query) {
                    $query->where('roles.group', 'zevo');
                })
                ->where('is_coach', 1)
                ->select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS username"))
                ->get()
                ->pluck('username', 'id')->toArray();
            $chefs = array_replace([1 => 'Zevo Admin'], $chefsUsers);
        } elseif ($role->group == "reseller") {
            $companyId = (\Auth::user()->company->first()->id ?? null);
            $chefs     = User::with(['roles', 'company'])
                ->whereHas('company', function ($query) use ($companyId) {
                    $query->where('user_team.company_id', $companyId);
                })
                ->select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS username"))
                ->get()
                ->pluck('username', 'id');
        } elseif ($role->group == "company") {
            $companyId    = (\Auth::user()->company->first()->id ?? null);
            $companyUsers = User::with(['roles', 'company'])
                ->whereHas('roles', function ($query) {
                    $query->where('roles.group', 'company');
                })
                ->whereHas('company', function ($query) use ($companyId) {
                    $query->where('user_team.company_id', $companyId);
                })
                ->select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS username"))
                ->get()
                ->pluck('username', 'id')->toArray();

            $chefsOwnUser = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                ->where(["is_coach" => 1, 'is_blocked' => 0])
                ->pluck('name', 'id')
                ->toArray();

            $chefs = array_replace($chefsOwnUser, $companyUsers);
        }

        $data['company']             = $this->getAllCompaniesGroupType($role->group, $companyData);
        $data['chefs']               = $chefs;
        $data['recipeSubCategories'] = SubCategory::where(['category_id' => 5, 'status' => 1])->pluck('name', 'id');
        $data['nutritions']          = config('zevolifesettings.nutritions');
        $data['goalTags']            = Goal::pluck('title', 'id')->toArray();
        $data['roleGroup']           = $role->group;
        if ($role->group == 'zevo') {
            $data['tags'] = CategoryTags::where("category_id", 5)->pluck('name', 'id')->toArray();
        }
        $data['recipeTypes'] = $this->recipeType->select('type_name', 'id')
            ->where('status', '1')
            ->get()->pluck('type_name', 'id')->toArray();
        return $data;
    }

    /**
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('create-recipe')) {
            abort(403);
        }

        try {
            $data = $this->getFormStaticData();

            $data['ga_title'] = trans('page_title.recipe.create');
            return \view('admin.recipe.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateRecipeRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateRecipeRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('create-recipe')) {
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
            $this->auditLogRepository->created("Recipe added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.recipe.data_add_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.recipe.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.recipe.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function edit(Request $request, Recipe $recipe)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->select('companies.id')->first();

        if (!access()->allow('update-recipe')) {
            abort(403);
        }

        if ($role->group == "zevo" && !is_null($recipe->company_id)) {
            abort(403);
        }

        if ($role->group == "company" && $recipe->company_id != $company->id) {
            abort(403);
        }

        if ($role->group == "reseller" && $recipe->company_id != $company->id) {
            abort(403);
        }

        try {
            $data                                    = $this->getFormStaticData();
            $data['recordData']                      = $recipe;
            $data['chef_disabled_flag']              = ($recipe->creator()->first()->roles()->where(['default' => 1, 'slug' => 'user'])->count() >= 1);
            $data['recordData']->chefData            = $recipe->getChefData();
            $data['recordData']->ingredients         = json_decode($recipe->ingredients, true);
            $data['recordData']->nutritions          = json_decode($recipe->nutritions);
            $data['recordData']['recipesubcategory'] = array_values($recipe->recipesubcategories->pluck('id')->toArray());

            $goal_tags = array();
            if (!empty($recipe->recipeGoalTag)) {
                $goal_tags = $recipe->recipeGoalTag->pluck('id')->toArray();
            }
            $recipe_companys = array();

            if (!empty($recipe->recipeteam)) {
                $recipe_companys = $recipe->recipeteam->pluck('id')->toArray();
            }

            $data['recipe_companys'] = $recipe_companys;

            $data['goal_tags'] = $goal_tags;

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $data['tags'] = CategoryTags::where("category_id", 5)->pluck('name', 'id')->toArray();
            }

            $data['recipeTypes'] = $this->recipeType->select('type_name', 'id')
                ->where('status', '1')
                ->get()->pluck('type_name', 'id')->toArray();

            $data['ga_title'] = trans('page_title.recipe.edit');
            return \view('admin.recipe.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditRecipeRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditRecipeRequest $request, Recipe $recipe)
    {
        $user = auth()->user();
        if (!access()->allow('update-recipe')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $recipe->toArray());
            $data = $recipe->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Recipe updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.recipe.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.recipe.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.recipe.edit', $recipe->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Recipe $recipe)
    {
        $user = auth()->user();
        if (!access()->allow('delete-recipe')) {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_recipe_id' => $recipe->id,'deleted_recipe_id' => $recipe->title]);
            $this->auditLogRepository->created("Recipe deleted successfully", $logs);

            return $recipe->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function getDetails(Recipe $recipe)
    {
        $user    = auth::user();
        $role    = getUserRole($user);
        $company = $user->company()->select('companies.id', 'companies.parent_id')->first();

        if (!access()->allow('view-recipe')) {
            abort(403);
        }

        if ($role->group == 'zevo' && $recipe->status != 1) {
            abort(403);
        }

        if ($role->group == "company" && !is_null($recipe->company_id) && $recipe->company_id != $company->id) {
            abort(403);
        }

        if ($role->group == 'reseller') {
            $companyId = $company->id;

            if ($company->parent_id != null) {
                // RCA ELSE RSA ( Permission to access ZEVO/RCA/RSA )
                if (!is_null($recipe->company_id) && $recipe->company_id != $company->parent_id && $recipe->company_id != $companyId) {
                    abort(403);
                }
            } else {
                $subcompany = company::where('parent_id', $companyId)->orWhere('id', $companyId)->pluck('id')->toArray();
                array_push($subcompany, null);

                if (!in_array($recipe->company_id, $subcompany)) {
                    abort(403);
                } elseif (!$recipe->status) {
                    abort(403);
                }
            }
        }

        try {
            $timezone = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $data     = [
                'user'       => $user,
                'role'       => $role,
                'companyId'  => (!empty($company) ? $company->id : null),
                'recordData' => $recipe,
                'timezone'   => $timezone,
                'ga_title'   => trans('page_title.recipe.details'),
            ];
            $data['recordData']->postDateTime        = Carbon::parse($recipe->created_at)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_datetime'));
            $data['recordData']->cookingTimeFormated = convertToHoursMins(timeToDecimal($recipe->cooking_time));
            $data['recordData']->chefData            = $recipe->getChefData();
            $data['recordData']->recipeSubCategories = $recipe->recipesubcategories->pluck('name');
            $data['recordData']->ingredients         = json_decode($recipe->ingredients, true);
            $data['recordData']->nutritions          = json_decode($recipe->nutritions);
            $data['recordData']->type                = $recipe->type()->select('id', 'type_name')->first();

            return \view('admin.recipe.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */
    public function approve(Recipe $recipe)
    {
        try {
            return $recipe->approveRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.recipe.index')->with('message', $messageData);
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
}
