<?php

namespace App\Models;

use App\Models\AppTheme;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class AppSetting extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_settings';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'value'];

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
    protected $dates = [];

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 160, 'h' => 320, 'ct' => 1]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        return $this->getFirstMedia('splash_image_url')->name;
    }

    public function getLgLogoAttribute()
    {
        return $this->getLogo(['w' => 640, 'h' => 1280, 'ct' => 1]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('splash_image_url');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('splash_image_url');
        }
        return getThumbURL($params, 'app_setting', 'splash_image_url');
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
        $return['url'] = getThumbURL($param, 'app_setting', $collection);
        return $return;
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
        $app_settings = config('zevolifesettings.app_settings');
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
                } elseif ($settingsData->type == "radio") {
                    if ($settingsData->value == 1) {
                        return "Yes";
                    } else {
                        return "No";
                    }
                } elseif ($settingsData->type == "file") {
                    if (!empty($settingsData->logo)) {
                        return '<div class="table-img table-img-l"><img src="' . $settingsData->logo . '" alt=""></div>';
                    } else {
                        return '<div class="table-img table-img-l"><img src="' . asset('assets/dist/img/boxed-bg.png') . '" alt=""></div>';
                    }
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
        $query = AppSetting::orderBy('updated_at', 'DESC');

        return $query->get();
    }

    /**
     * get all appSettings data without media.
     *
     * @param
     * @return appSettings data
     */

    public function getAllSettings()
    {
        return AppSetting::select('key', 'value')
            ->where('key', '!=', 'splash_image_url')
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * get all appSettings data with media only.
     *
     * @param
     * @return appSettings data
     */

    public function getAllMediaSettings()
    {
        return AppSetting::where('key', 'splash_image_url')
            ->first();
    }

    /**
     * store and update App settings data.
     *
     * @param payload
     * @return boolean
     */

    public function storeUpdateEntity($payload)
    {
        $appSettingsField = config('zevolifesettings.app_settings');
        foreach ($payload as $key => $value) {
            if (isset($value)) {
                $appData = AppSetting::where("key", $key)->first();
                if ($appSettingsField[$key]['type'] != 'file') {
                    if (!empty($appData)) {
                        $appData->value = $value;
                    } else {
                        $appData        = new AppSetting();
                        $appData->key   = $key;
                        $appData->value = $value;
                        if (array_key_exists($key, $appSettingsField)) {
                            $appData->type = $appSettingsField[$key]['type'];
                        }
                    }
                    $appData->save();
                } else {
                    if (empty($appData)) {
                        $appData = new AppSetting();
                    }
                    $appData->key = $key;
                    if (array_key_exists($key, $appSettingsField)) {
                        $appData->type = $appSettingsField[$key]['type'];
                    }
                    $appData->save();

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
}
