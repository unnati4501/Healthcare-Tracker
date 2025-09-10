<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class MeditationCategory extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'meditation_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];

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
     * @return HasMany
     */
    public function tracks(): HasMany
    {
        return $this->hasMany('App\Models\MeditationTrack');
    }

    /**
     * @param Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $imageConversions = config('zevolifesettings.imageConversions.meditation_category');
        foreach ($imageConversions as $conversion => $size) {
            $this->addMediaConversion($conversion)
                ->width($size['width'])
                ->height($size['height'])
                ->optimize()
                // ->keepOriginalImageFormat()
                ->nonQueued()
                ->performOnCollections('logo');
        }
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo('th_sm');
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(string $size = 'th_sm'): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            if ($media->hasGeneratedConversion($size)) {
                $logo = $this->getFirstMediaUrl('logo', $size);
            } else {
                $logo = $this->getFirstMediaUrl('logo');
                if (empty($logo)) {
                    $logo = getDefaultFallbackImageURL('meditation_category', 'logo');
                }
            }
        } else {
            $logo = getDefaultFallbackImageURL('meditation_category', 'logo');
        }
        return $logo;
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('total_track', function ($record) {
                return $record->tracks->count();
            })
            ->addColumn('logo', function ($record) {
                return '<img class="tbl-user-img img-circle elevation-2" src="' . $record->logo . '" width="70" />';
            })
            ->addColumn('actions', function ($record) {
                return view('admin.meditationcategory.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'logo'])
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
        $query = self::orderBy('updated_at', 'DESC');

        if (in_array('categoryName', array_keys($payload)) && !empty($payload['categoryName'])) {
            $query->where('title', 'like', '%' . $payload['categoryName'] . '%');
        }

        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity(array $payload)
    {
        $meditationcatInput = [
            'title' => $payload['name'],
        ];

        $meditationcat = self::create($meditationcatInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $meditationcat->id . '_' . \time();
            $meditationcat->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($name)
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }
        return $meditationcat;
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $data = [
            'title' => $payload['name'],
        ];

        $updated = $this->update($data);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($name)
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($updated) {
            return true;
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
        if ($this->tracks->count() > 0) {
            return array('deleted' => 'error');
        }
        if ($this->delete()) {
            $this->clearMediaCollection('logo');
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
