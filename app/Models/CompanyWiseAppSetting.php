<?php

namespace App\Models;

use App\Models\AppTheme;
use DataTables;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CompanyWiseAppSetting extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_wise_app_settings';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'key',
        'value',
        'type',
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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageUrlAttribute()
    {
        return $this->getImageUrl($this->key, ['w' => 160, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLgImageUrlAttribute()
    {
        return $this->getImageUrl($this->key, ['w' => 640, 'h' => 1280]);
    }

    /**
     * @param string $key
     * @param array $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageUrl($key, array $params): string
    {
        $media = $this->getFirstMedia($key);
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl($key);
        }
        return getThumbURL($params, 'company_wise_app_settings', $key);
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'company_wise_app_settings', $collection);
        return $return;
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * store and update company wise app settings data.
     *
     * @param payload
     * @return boolean
     */
    public function storeUpdateEntity($payload, $company)
    {
        $appSettingsField = config('zevolifesettings.company_wise_app_settings');
        // Extra hidden value remove from array
        unset($payload['proengsoft_jsvalidation']);
        foreach ($payload as $key => $value) {
            $appData = $this->where('company_id', $company)->where("key", $key)->first();
            if ($appSettingsField[$key]['type'] != 'file') {
                if (!empty($appData)) {
                    $appData->update(['value' => $value]);
                } else {
                    if (array_key_exists($key, $appSettingsField)) {
                        $type = $appSettingsField[$key]['type'];
                    }
                    $appDataInput = [
                        'company_id' => $company,
                        'key'        => $key,
                        'value'      => $value,
                        'type'       => $type,
                    ];
                    $this->create($appDataInput);
                }
            } else {
                if (!empty($appData)) {
                    $appData->update(['value' => $value->getClientOriginalName()]);
                    if (isset($payload[$key]) && !empty($payload[$key])) {
                        $name = $appData->id . '_' . \time();
                        $appData->clearMediaCollection($key)->addMediaFromRequest($key)
                            ->usingName($payload[$key]->getClientOriginalName())
                            ->usingFileName($name . '.' . $payload[$key]->extension())
                            ->toMediaCollection($key, config('medialibrary.disk_name'));
                    }
                } else {
                    if (array_key_exists($key, $appSettingsField)) {
                        $type = $appSettingsField[$key]['type'];
                    }
                    $appDataInput = [
                        'company_id' => $company,
                        'key'        => $key,
                        'value'      => $value->getClientOriginalName(),
                        'type'       => $type,
                    ];
                    $appData = $this->create($appDataInput);

                    if (isset($payload[$key]) && !empty($payload[$key])) {
                        $name = $appData->id . '_' . \time();
                        $appData->clearMediaCollection($key)->addMediaFromRequest($key)
                            ->usingName($payload[$key]->getClientOriginalName())
                            ->usingFileName($name . '.' . $payload[$key]->extension())
                            ->toMediaCollection($key, config('medialibrary.disk_name'));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Set datatable for app listing.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list         = $this->getList($payload);
        $app_settings = config('zevolifesettings.company_wise_app_settings');
        $appThemes    = AppTheme::all()->pluck('name', 'slug')->toArray();
        return DataTables::of($list)
            ->addIndexColumn()
            ->addColumn('key', function ($settingsData) use ($app_settings) {
                if (array_key_exists($settingsData->key, $app_settings)) {
                    return $app_settings[$settingsData->key]['display'];
                } else {
                    return $settingsData->key;
                }
            })
            ->addColumn('value', function ($settingsData) use ($appThemes) {
                if ($settingsData->key == 'app_theme') {
                    return $appThemes[$settingsData->value] ?? "";
                } elseif ($settingsData->type == "file") {
                    $src = (!empty($settingsData->image_url) ? $settingsData->image_url : asset('assets/dist/img/boxed-bg.png'));
                    return "<div class='table-img table-img-l'><img src='{$src}' /></div>";
                } elseif ($settingsData->key == "splash_message") {
                    return !empty($settingsData->value) ? $settingsData->value : "-";
                } else {
                    return $settingsData->value;
                }
            })
            ->addColumn('updated_at', function ($settingsData) {
                return $settingsData->updated_at;
            })
            ->rawColumns(['value'])
            ->make(true);
    }

    /**
     * get appSettings list for data table list.
     *
     * @param payload
     * @return appSettingsList
     */

    public function getList($payload)
    {
        $query = $this->where('company_id', $payload['company'])->orderBy('updated_at', 'DESC');
        return $query->get();
    }
}
