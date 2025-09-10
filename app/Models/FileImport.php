<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class FileImport extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file_imports';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'module',
        'token',
        'uploaded_file',
        'validated_file',
        'in_process',
        'is_processed',
        'is_imported_successfully',
        'process_started_at',
        'process_finished_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'in_process'               => 'boolean',
        'is_processed'             => 'boolean',
        'is_imported_successfully' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['process_started_at', 'process_finished_at'];

    /**
     * @return BelongsToMany
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);

        return DataTables::of($list)
            ->addColumn('company', function ($record) {
                if (is_null($record->company_id)) {
                    return;
                }
                return $record->company_name;
            })
            ->addColumn('uploaded_file_link', function ($record) {
                return getFileLink($record->getKey(), $record->uploaded_file, $record->module, 1, 1);
            })
            ->addColumn('validated_file_link', function ($record) {
                return (!empty($record->validated_file)) ? getFileLink($record->getKey(), $record->validated_file, $record->module, 1, 1) : "";
            })
            ->addColumn('in_process', function ($record) {
                if ($record->in_process) {
                    return '<i class="fal fa-check-circle text-success fa-lg"></i>';
                } else {
                    return '<i class="fal fa-times-circle text-danger fa-lg"></i>';
                }
            })
            ->addColumn('is_processed', function ($record) {
                if ($record->is_processed) {
                    return '<i class="fal fa-check-circle text-success fa-lg"></i>';
                } else {
                    return '<i class="fal fa-times-circle text-danger fa-lg"></i>';
                }
            })
            ->addColumn('is_imported_successfully', function ($record) {
                if ($record->is_imported_successfully) {
                    return '<i class="fal fa-check-circle text-success fa-lg"></i>';
                } else {
                    return '<i class="fal fa-times-circle text-danger fa-lg"></i>';
                }
            })
            ->addColumn('actions', function ($record) {
                return view('admin.fileimport.listaction', compact('record'))->render();
            })
            ->rawColumns(['in_process', 'is_processed', 'is_imported_successfully', 'actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */
    public function getRecordList($payload)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company->first();
        $module  = ($payload['module'] ?? 'users');

        $query = $this
            ->select('file_imports.*', 'companies.name AS company_name')
            ->leftjoin('companies', function ($join) {
                $join->on('companies.id', '=', 'file_imports.company_id');
            })
            ->where('module', $module)
            ->orderByDesc('file_imports.updated_at');

        if ($module == 'users') {
            if ($role->group == 'reseller') {
                if ($company->is_reseller) {
                    $query
                        ->where(function ($where) use ($company) {
                            $where
                                ->where('company_id', $company->id)
                                ->orWhere('companies.parent_id', $company->id);
                        });
                } else {
                    $query->where('company_id', $company->id);
                }
            } elseif ($role->group == 'company') {
                $query->where('company_id', $company->id);
            }
        }

        return $query->get();
    }

/**
 * store record data.
 *
 * @param payload
 * @return boolean
 */

    public function storeEntity($payload)
    {
        if ($payload->hasFile('import_file')) {
            // get uploaded file from request
            $uploadedFile = $payload->file('import_file');

            //get file extension
            $extension = $uploadedFile->getClientOriginalExtension();

            //filename to store
            $token           = 'zvh_' . time();
            $filenametostore = $payload['module'] . '_import_file_' . $token . '.' . $extension;

            // create record in table to display in grid
            $record = FileImport::create([
                'company_id'    => $payload['module'] == 'users' ? $payload->company : null,
                'module'        => $payload['module'],
                'token'         => $token,
                'uploaded_file' => $filenametostore,
            ]);

            // get link to upload file on space
            $link = getFileLink($record->getKey(), $filenametostore, $payload['module'], 1);

            //Upload File to server storage space
            $fileContent = file_get_contents($uploadedFile->getPathName());

            Storage::disk(config('medialibrary.disk_name'))->put($link, $fileContent, 'public');

            if ($record) {
                return true;
            }
        }

        return false;
    }

/**
 * delete record by record id.
 *
 * @param $id
 * @return array
 */

    public function deleteRecord()
    {
        $link = getFileLink($this->getKey(), '', 'user', 1);
        Storage::disk(config('medialibrary.disk_name'))->deleteDirectory($link . '/');

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
