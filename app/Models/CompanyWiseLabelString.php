<?php

namespace App\Models;

use App\Models\Company;
use DataTables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CompanyWiseLabelString extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_wise_label_string';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'module',
        'field_name',
        'label_name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getLabelstringsTableData()
    {
        $user    = auth()->user();
        $company = $user->company()->select('companies.id')->first();
        $list    = $this->getLabelStringList();
        return DataTables::of($list)
            ->addColumn('label_name', function ($record) use ($company) {
                if (in_array($record->field_name, ['location_logo', 'department_logo'])) {
                    $logo = $company->getMediaData($record->field_name, ['w' => 30, 'h' => 30, 'zc' => 3, 'ct' => 1]);
                    return "<div class='table-img table-img-l'><img class='bg-gray' src='" . $logo['url'] . "' /></div>";
                }
                return $record->label_name;
            })
            ->rawColumns(['label_name'])
            ->make(true);
    }

    /**
     * get label string list for data table list.
     *
     * @param payload
     * @return roleList
     */
    public function getLabelStringList()
    {
        $user    = auth()->user();
        $company = $user->company()->first();

        return $this
            ->select('*')
            ->where('company_id', $company->id)
            ->get();
    }

    public function storeEntity($payload, Company $company)
    {
        if (isset($payload['remove_location_logo']) && $payload['remove_location_logo'] == 1) {
            $company->clearMediaCollection('location_logo');
            $company->companyWiseLabelString()
                ->where('module', 'onboarding')
                ->where('field_name', 'location_logo')
                ->delete();
            unset($payload['remove_location_logo']);
        }

        if (isset($payload['remove_department_logo']) && $payload['remove_department_logo'] == 1) {
            $company->clearMediaCollection('department_logo');
            $company->companyWiseLabelString()
                ->where('module', 'onboarding')
                ->where('field_name', 'department_logo')
                ->delete();
            unset($payload['remove_department_logo']);
        }

        foreach ($payload as $key => $valuearray) {
            foreach ($valuearray as $subkey => $subvalue) {
                $label_name = (isset($subvalue)) ? $subvalue : config('zevolifesettings.company_label_string.' . $key . '.' . $subkey . '.default_value');
                if ($subvalue instanceof UploadedFile) {
                    $label_name = $subvalue->getClientOriginalName();
                    $name       = "{$company->id}_{$subkey}_" . \time();
                    $company
                        ->clearMediaCollection($subkey)
                        ->addMedia($subvalue)
                        ->usingName($label_name)
                        ->usingFileName($name . '.' . $subvalue->extension())
                        ->preservingOriginal()
                        ->toMediaCollection($subkey, config('medialibrary.disk_name'));
                }

                $this->updateOrCreate([
                    'company_id' => $company->id,
                    'module'     => $key,
                    'field_name' => $subkey,
                ], [
                    'label_name' => $label_name,
                ]);
            }
        }

        return true;
    }

    public function setdefault(Company $company)
    {
        $insertArray  = [];
        $defaultValue = config('zevolifesettings.company_label_string');
        unset($defaultValue['onboarding']['location_logo']);
        unset($defaultValue['onboarding']['department_logo']);

        $company->clearMediaCollection('location_logo');
        $company->clearMediaCollection('department_logo');
        $company->companyWiseLabelString()->delete();

        foreach ($defaultValue as $key => $valuearray) {
            foreach ($valuearray as $subkey => $subvalue) {
                $insertArray[] = [
                    'module'     => $key,
                    'field_name' => $subkey,
                    'label_name' => $subvalue['default_value'],
                ];
            }
        }

        $company->companyWiseLabelString()->createMany($insertArray);
        return true;
    }
}
