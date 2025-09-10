<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\FileImport;
use App\Reader\ExcelSheetReader;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

/**
 * Class FileImportsController
 *
 * @package App\Http\Controllers\Admin
 */
class FileImportsController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * variable to store the model object
     * @var FileImport
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param FileImport $model ;
     */
    public function __construct(FileImport $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('imports.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Imports');
        });
    }

    /**
     * @return View
     */
    public function index(): View
    {
        $user = auth()->user();
        $role = getUserRole($user);

        if ($role->group == 'zevo' && (!access()->allow('users-import') || !access()->allow('questions-import'))) {
            abort(403);
        }

        if ($role->group != 'zevo') {
            abort(403);
        }

        try {
            $companies = [];
            $company   = $user->company()->first();

            if ($role->group == 'reseller') {
                if ($company->is_reseller) {
                    $companies = Company::select('id', 'name')
                        ->where(function ($where) use ($company) {
                            $where
                                ->where('id', $company->id)
                                ->orWhere('parent_id', $company->id);
                        })
                        ->get()->pluck('name', 'id')->toArray();
                }
            } else {
                $companies = Company::get()->pluck('name', 'id')->toArray();
            }

            $data = [
                'user'        => $user,
                'usercomany'  => $company,
                'companies'   => $companies,
                'pagination'  => config('zevolifesettings.datatable.pagination.short'),
                'timezone'    => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format' => config('zevolifesettings.date_format.moment_default_datetime'),
                'role'        => $role->group,
                'ga_title'    => trans('page_title.imports'),
            ];
            return \view('admin.fileimport.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            if ($request->hasFile('import_file')) {
                $allowedMimeTypes = ['xlsx'];
                if (!$request->hasFile('import_file') || !in_array($request->file('import_file')->getClientOriginalExtension(), $allowedMimeTypes)) {
                    $messageData = [
                        'data'   => trans('import.message.uploading_valid_excel_file'),
                        'status' => 0,
                    ];
                    return response()->json($messageData);
                }

                $file = $request->file('import_file');

                $reader = new Xlsx();
                // Set current excel as read only because later on we need to generate new file.
                $reader->setReadDataOnly(true);
                // Set read are from ExcelSheetReader object , OPTIONAL
                //$reader->setReadFilter(new ExcelSheetReader());

                $spreadsheet = $reader->load($file->getPathName());
                $sheetnames  = $spreadsheet->getSheetNames();
                $expectedSheetName = $request->module == 'users' ? 'users' : 'choices';

                if (!in_array($expectedSheetName, $sheetnames)) {
                    $messageData = [
                        'data'   => trans('import.message.uploading_valid_excel_file'),
                        'status' => 0,
                    ];
                    return response()->json($messageData);
                }
            } else {
                $messageData = [
                    'data'   => 'File not found !',
                    'status' => 2,
                ];
                return response()->json($messageData);
            }

            $data = $this->model->storeEntity($request);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('import.message.file_uploaded_successfully'),
                    'status' => 1,
                ];
                return response()->json($messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('import.message.something_went_wrong'),
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    public function getImports(Request $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);

        if ($role->group == 'zevo' && (!access()->allow('users-import') || !access()->allow('questions-import'))) {
            return response()->json([
                'message' => trans('import.message.unauthorized_access'),
            ], 422);
        }

        if ($role->group != 'zevo') {
            return response()->json([
                'message' => trans('import.message.unauthorized_access'),
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
            return \Redirect::route('admin.imports.index')->with('message', $messageData);
        }
    }

    /**
     * @param  FileImport $fileImport
     *
     * @return View
     */

    public function delete(FileImport $fileImport)
    {
        try {
            return $fileImport->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
