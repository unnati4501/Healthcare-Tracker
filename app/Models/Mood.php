<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Mood extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moods';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'media'];

    /**
     * "has many" relation to `mood_user` table
     * via `mood_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moodUser()
    {
        return $this->hasMany(\App\Models\MoodUser::class, 'mood_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMoodLogoAttribute()
    {
        return $this->getLogo(['w' => 100, 'h' => 100]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getMediaData('logo', ['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMoodLogoNameAttribute()
    {
        return $this->getFirstMedia('logo')->name;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'moods', 'logo');
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
        $return['url'] = getThumbURL($param, 'moods', $collection);
        return $return;
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
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('logo', function ($record) {
                return '<div class="table-img table-img-l"><img src="' . $record->mood_logo . '" width="70" /></div>';
            })
            ->addColumn('title', function ($record) {
                return $record->title;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.moods.listaction', compact('record'))->render();
            })
            ->rawColumns(['logo', 'actions'])
            ->make(true);
    }

    /**
     * get records list for datatable.
     *
     * @param payload
     * @return array
     */
    public function getRecordList($payload)
    {
        return self::select(
            'moods.id',
            'moods.title',
            'moods.updated_at'
        )
            ->orderBy('moods.updated_at', 'DESC')
            ->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $moodsInput = [
            'logo'  => $payload['logo']->getClientOriginalName(),
            'title' => $payload['title'],
        ];

        $record = self::create($moodsInput);

        if ($record) {
            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $record->id . '_' . \time();
                $record->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($payload['logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            return true;
        }

        return false;
    }

    /**
     * For pre-populating data in edit moods page.
     *
     * @param none
     * @return array
     */
    public function getUpdateData()
    {
        $data = array();

        $data['record'] = $this;

        return $data;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        $moodsInput = [
            'title' => $payload['title'],
        ];

        if (isset($payload['logo'])) {
            $moodsInput['logo'] = $payload['logo']->getClientOriginalName();
        }

        $record = $this->update($moodsInput);

        if ($record) {
            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $this->id . '_' . \time();
                $this->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($payload['logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

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
        $this->clearMediaCollection('logo');
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
