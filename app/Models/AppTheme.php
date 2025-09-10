<?php

namespace App\Models;

use App\Models\AppSetting;
use App\Models\CompanyWiseAppSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class AppTheme extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_themes';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'name',
    ];

    /**
     * "HasMany" relation to `company_wise_app_settings` table
     * via `value` field.
     *
     * @return hasMany
     */
    public function company(): HasMany
    {
        return $this
            ->hasMany(CompanyWiseAppSetting::class, 'value', 'slug')
            ->where('key', 'app_theme');
    }

    /**
     * "HasOne" relation to `app_settings` table
     * via `company_id` field.
     *
     * @return hasMany
     */
    public function default(): HasOne
    {
        return $this
            ->hasOne(AppSetting::class, 'value', 'slug')
            ->where('key', 'app_theme');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getThemeNameAttribute()
    {
        $media = $this->getFirstMedia('theme');
        return !empty($media) ? $media->name : 'Choose File';
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTheme(): string
    {
        $media = $this->getFirstMedia('theme');
        if (!is_null($media) && $media->count() > 0) {
            return $this->getFirstMediaUrl('theme');
        }
        return "";
    }

    /**
     * Set datatable for groups list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('link', function ($record) {
                return "<a href='{$record->getTheme()}' target='__blank'>Preview</a>";
            })
            ->addColumn('actions', function ($record) {
                return view('admin.app-theme.listaction', compact('record'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['link', 'actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */
    public function getRecordList($payload)
    {
        $query = $this
            ->select('id', 'name')
            ->withCount(['company', 'default']);

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('app_themes.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * store app theme data.
     *
     * @param array payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        // store theme data
        $theme = $this->create([
            'slug' => str_replace(' ', '_', strtolower($payload['name'])),
            'name' => $payload['name'],
        ]);

        if ($theme) {
            // store json theme to storage
            if (isset($payload['theme']) && !empty($payload['theme'])) {
                $name = $theme->id . '_' . time();
                $theme
                    ->clearMediaCollection('theme')
                    ->addMediaFromRequest('theme')
                    ->usingName($payload['theme']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['theme']->getClientOriginalExtension())
                    ->toMediaCollection('theme', config('medialibrary.disk_name'));
            }

            return true;
        }
        return false;
    }

    /**
     * update app  theme data.
     *
     * @param array payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        // update theme data
        $updated = $this->update([
            'slug' => str_replace(' ', '_', strtolower($payload['name'])),
            'name' => $payload['name'],
        ]);

        if ($updated) {
            // store json theme to storage
            if (isset($payload['theme']) && !empty($payload['theme'])) {
                $name = $this->id . '_' . time();
                $this
                    ->clearMediaCollection('theme')
                    ->addMediaFromRequest('theme')
                    ->usingName($payload['theme']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['theme']->getClientOriginalExtension())
                    ->toMediaCollection('theme', config('medialibrary.disk_name'));
            }

            return true;
        }
        return false;
    }

    /**
     * Delete theme
     *
     * @return boolean
     */
    public function deleteRecord()
    {
        return $this->delete();
    }
}
