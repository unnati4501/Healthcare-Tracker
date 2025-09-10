<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EditLabelStringRequest;
use App\Models\CompanyWiseLabelString;
use Breadcrumbs;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class LabelsettingsController
 *
 * @package App\Http\Controllers\Admin
 */
class LabelsettingsController extends Controller
{
    /**
     * variable to store the model object
     * @var CompanyWiseLabelString
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param CompanyWiseLabelString $model ;
     */
    public function __construct(CompanyWiseLabelString $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('labelsettings.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Label Settings');
        });
        Breadcrumbs::for('labelsettings.changelabel', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Label Settings', route('admin.labelsettings.index'));
            $trail->push('Update Labels');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('label-setting')) {
            abort(403);
        }
        try {
            $data = [
                'pagination' => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'   => trans('page_title.labelstrings.labelstrings_list'),
            ];
            return \view('admin.labelsettings.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return view
     */
    public function changeLabel(Request $request)
    {
        if (!access()->allow('label-setting')) {
            abort(403);
        }
        try {
            $user               = auth()->user();
            $company            = $user->company()->select('companies.id')->first();
            $labelStrings       = $this->model->where('company_id', $company->id)->pluck('label_name', 'field_name')->toArray();
            $defaultLabelString = config('zevolifesettings.company_label_string', []);
            $companyLabelString = [];

            // iterate default labels loop and check is label's custom value is set then user custom value else default value
            foreach ($defaultLabelString as $groups) {
                foreach ($groups as $labelValue) {
                    if ($labelKey == 'location_logo' || $labelKey == 'department_logo') {
                        if (isset($labelStrings[$labelKey])) {
                            $logo                          = $company->getMediaData($labelKey, ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            $companyLabelString[$labelKey] = [
                                'src'    => $logo['url'],
                                'label'  => $labelStrings[$labelKey],
                                'remove' => true,
                            ];
                        } else {
                            $companyLabelString[$labelKey] = [
                                'src'    => $labelValue['default_value'],
                                'label'  => trans('labelsettings.form.placeholder.choose_file'),
                                'remove' => false,
                            ];
                        }
                    } else {
                        $companyLabelString[$labelKey] = ($labelStrings[$labelKey] ?? $labelValue['default_value']);
                    }
                }
            }

            $data = [
                'companyLabelString' => $companyLabelString,
                'ga_title'           => trans('page_title.labelstrings.create'),
            ];

            return \view('admin.labelsettings.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditLabelStringRequest $request, CompanyWiseLabelString $companywiselabelstring
     *
     * @return RedirectResponse
     */
    public function update(EditLabelStringRequest $request, CompanyWiseLabelString $companywiselabelstring)
    {
        try {
            \DB::beginTransaction();
            $user    = auth()->user();
            $company = $user->company()->select('companies.id')->first();
            $payload = $request->all();

            unset($payload['_token']);

            $data = $this->model->storeEntity($payload, $company);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Labels has been updated successfully.",
                    'status' => 1,
                ];
                return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.labelsettings.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
        }
    }

    public function getlabelstrings(Request $request)
    {
        if (!access()->allow('label-setting')) {
            abort(403);
        }
        try {
            return $this->model->getLabelstringsTableData();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
        }
    }

    public function setdefault(Request $request, CompanyWiseLabelString $companywiselabelstring)
    {
        try {
            \DB::beginTransaction();
            $user      = auth()->user();
            $company = $user->company()->select('companies.id')->first();

            $data = $this->model->setdefault($company);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Labels has been updated successfully.",
                    'status' => 1,
                ];
                return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.labelsettings.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.labelsettings.index')->with('message', $messageData);
        }
    }
}
