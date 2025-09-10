<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Exercise extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'exercises';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'type', 'show_map', 'calories'];

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
    protected $casts = ['title' => 'string', 'description' => 'string', 'type' => 'string', 'show_map' => 'boolean', 'calories' => 'integer'];

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
        return $this->getLogo(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
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
        return getThumbURL($params, 'exercise', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundAttribute()
    {
        return $this->getBackground(['w' => 320, 'h' => 160]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundNameAttribute()
    {
        return $this->getFirstMedia('background')->name;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackground(array $params): string
    {
        $media = $this->getFirstMedia('background');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('background');
        }
        return getThumbURL($params, 'exercise', 'background');
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
            ->addColumn('description', function ($record) {
                return (strlen(strip_tags(htmlspecialchars_decode($record->description))) > 60) ? substr(strip_tags(htmlspecialchars_decode($record->description)), 0, 60) . "..." : strip_tags(htmlspecialchars_decode($record->description));
            })
            ->addColumn('logo', function ($record) {
                return '<div class="table-img table-img-l"><img src="' . $record->logo . '" alt=""></div>';
            })
            ->addColumn('actions', function ($record) {
                return view('admin.exercise.listaction', compact('record'))->render();
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

        if (in_array('exerciseName', array_keys($payload)) && !empty($payload['exerciseName'])) {
            $query->where('title', 'like', '%' . $payload['exerciseName'] . '%');
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
        $ecerciseInput = [
            'type'        => $payload['type'],
            'title'       => $payload['name'],
            'description' => $payload['description'],
            'calories'    => $payload['calories'],
            'show_map'    => (!empty($payload['show_map']) && $payload['show_map'] == 'yes') ? 1 : 0,
        ];

        $exercise = self::create($ecerciseInput);

        if (!empty($payload['members_selected'])) {
            foreach ($payload['members_selected'] as $value) {
                $trackerExerciseId = (int) $value;
                $exercise->trackerExercises()->attach($trackerExerciseId);
            }
        }

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $exercise->id . '_' . \time();
            $exercise->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['background']) && !empty($payload['background'])) {
            $name = $exercise->id . '_' . \time();
            $exercise->clearMediaCollection('background')->addMediaFromRequest('background')
                ->usingName($payload['background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
        }

        return $exercise;
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
            'type'        => $payload['type'],
            'title'       => $payload['name'],
            'description' => $payload['description'],
            'calories'    => $payload['calories'],
            'show_map'    => (!empty($payload['show_map']) && $payload['show_map'] == 'yes') ? 1 : 0,
        ];

        $updated = $this->update($data);

        if (!empty($payload['members_selected'])) {
            foreach ($payload['members_selected'] as $value) {
                $trackerExerciseId = (int) $value;
                $this->trackerExercises()->attach($trackerExerciseId);
            }
        }

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['background']) && !empty($payload['background'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('background')->addMediaFromRequest('background')
                ->usingName($payload['background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
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
        if ($this->delete()) {
            $this->clearMediaCollection('logo');
            $this->clearMediaCollection('background');
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * @return BelongsToMany
     */
    public function trackerExercises(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\TrackerExercise', 'exercise_mapping', 'exercise_id', 'tracker_exercise_id');
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
            $param['src'] = $this->getFirstMediaUrl($collection);
        }
        $return['url'] = getThumbURL($param, 'exercise', $collection);
        return $return;
    }
}
