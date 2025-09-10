<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateLocationRequest;
use App\Http\Requests\Admin\EditLocationRequest;
use App\Http\Requests\Admin\NpsReportExportRequest;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\Country;
use App\Models\Timezone;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class LocationController
 *
 * @package App\Http\Controllers\Admin
 */
class LocationController extends Controller
{
    /**
     * variable to store the model object
     * @var Company
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Company $model ;
     */
    public function __construct(CompanyLocation $model, AuditLogRepository $auditLogRepository)
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
        Breadcrumbs::for('location.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Locations');
        });
        Breadcrumbs::for('location.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Locations', route('admin.locations.index'));
            $trail->push('Add Location');
        });
        Breadcrumbs::for('location.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Locations', route('admin.locations.index'));
            $trail->push('Edit Location');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('manage-location')) {
            abort(403);
        }
        try {
            $company  = $user->company()->first();
            $timezone = [];
            $companies= [];
            $country  = $request->get('country', null);

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
            }

            if (!empty($country)) {
                $country  = Country::select('countries.id', 'countries.sortname')->where('id', $country)->first();
                $timezone = Timezone::select('timezones.id', 'timezones.name')
                    ->where('country_code', "{$country->sortname}")
                    ->get()
                    ->pluck('name', 'name')
                    ->toArray();
            }

            $data = [
                'role'                   => $role,
                'companiesDetails'       => $company,
                'company_id'             => !is_null($company) ? $company->id : null,
                'timezone'               => $timezone,
                'companies'              => $companies,
                'pagination'             => config('zevolifesettings.datatable.pagination.long'),
                'countries'              => Country::pluck('name', 'id')->toArray(),
                'ga_title'               => trans('page_title.locations.locations_list'),
                'company_col_visibility' => (is_null($company) || ($company && $company->is_reseller)),
                'loginemail'             => ($user->email ?? ""),
                'date_format'            => config('zevolifesettings.date_format.moment_default_date'),
                
            ];

            return \view('admin.locations.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @return View
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if (!access()->allow('create-location')) {
            abort(403);
        }
        try {
            $role      = getUserRole($user);
            $company   = $user->company()->first();
            $companies = [];

            if ($role->group == 'zevo') {
                $companies = Company::pluck('name', 'id')->toArray();
            } elseif ($role->group == 'reseller' && $company->is_reseller) {
                $companies = Company::where('parent_id', $company->id)
                    ->whereNotNull('parent_id')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $companies = array_replace([$company->id => $company->name], $companies);
            }

            $data = [
                'countries'    => Country::pluck('name', 'id')->toArray(),
                'company'      => $companies,
                'role'         => $role,
                'user_company' => $company,
                'ga_title'     => trans('page_title.locations.create'),
            ];
            return \view('admin.locations.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateLocationRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateLocationRequest $request)
    {
        $user   = auth()->user();
        if (!access()->allow('create-location')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $validator = Validator::make($payload, [
                'location_name' => [function ($attribute, $value, $fail) use ($payload) {
                    $duplicationCheck = $this->model->duplicationCheck($payload);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors(trans('location.validation.already_taken_name'));
            }

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($payload);

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Location added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('location.message.added'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.locations.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('location.message.something_wrong'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.locations.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit(Request $request, CompanyLocation $location)
    {
        $user = auth()->user();
        if (!access()->allow('update-location')) {
            abort(403);
        }

        try {
            $user_company = $user->company()->first();
            $role         = getUserRole($user);

            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    if ($user_company->id != $location->company_id) {
                        return view('errors.401');
                    }
                } elseif ($role->group == 'reseller') {
                    if ($user_company->is_reseller) {
                        $allcompanies = Company::where('parent_id', $user_company->id)->orWhere('id', $user_company->id)->get()->pluck('id')->toArray();
                        if (!in_array($location->company->id, $allcompanies)) {
                            return view('errors.401');
                        }
                    } elseif (!$user_company->is_reseller && $location->company_id != $user_company->id) {
                        return view('errors.401');
                    }
                }
            }

            $company = $location->company;
            $data    = [
                'id'           => $location->id,
                'role'         => $role,
                'user_company' => $user_company,
                'locationData' => $location,
                'countries'    => Country::pluck('name', 'id')->toArray(),
                'company'      => [$company->id => $company->name],
                'ga_title'     => trans('page_title.locations.edit'),
            ];
            return \view('admin.locations.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditLocationRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditLocationRequest $request, CompanyLocation $location)
    {
        $user   = auth()->user();
        if (!access()->allow('update-location')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $validator = Validator::make($payload, [
                'location_name' => [function ($attribute, $value, $fail) use ($payload, $location) {
                    $duplicationCheck = $this->model->duplicationCheck($payload, $location->id);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors(trans('location.validation.already_taken_name'));
            }

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $location->toArray());
            $data = $location->updateEntity($payload);

            $updatedUsersData   = array_merge($userLogData, $payload);
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Location updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('location.message.updated'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.locations.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('location.message.something_wrong'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.locations.edit', $location->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getLocations(Request $request)
    {
        if (!access()->allow('manage-location')) {
            return response()->json([
                'message' => trans('location.message.unauthorized_access'),
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
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(CompanyLocation $location)
    {
        $user   = auth()->user();
        if (!access()->allow('delete-location')) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_location_id' => $location->id,'deleted_location_name' => $location->name]);
            $this->auditLogRepository->created("Location deleted successfully", $logs);

            return $location->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportLocations(NpsReportExportRequest $request)
    {
        if (!access()->allow('manage-location')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportLocationDataEntity($request->all());
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
            return \Redirect::route('admin.locations.index')->with('message', $messageData);
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
