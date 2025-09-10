<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class ChallengeImageLibrary extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenge_image_library';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['target_type', 'is_deleted'];

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
        'is_deleted' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return HasMany
     */
    public function challenges(): HasMany
    {
        return $this->hasMany('App\Models\Challenge', 'library_image_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getImage(['w' => 320, 'h' => 160]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImage(array $params): string
    {
        $media = $this->getFirstMedia('image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('image');
        }
        return getThumbURL($params, 'challenge_library', 'image');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageNameAttribute()
    {
        $media = $this->getFirstMedia('image');
        return !empty($media->name) ? $media->name : "";
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
        $return['url'] = getThumbURL($param, 'challenge_library', $collection);
        return $return;
    }

    /**
     * Get data table data for challenge images listing.
     * @param $payload
     * @return mixed
     * @throws \Exception
     */
    public function getTableData($payload)
    {
        $list        = $this->getImagesList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('image', function ($record) {
                return '<div class="table-img table-img-l"><img src="' . $record->image . '"/></div>';
            })
            ->addColumn('actions', function ($record) {
                return view('admin.challenge_image_library.listaction', compact('record'))->render();
            })
            ->rawColumns(['image', 'actions'])
            ->make(true);
    }

    /**
     * Get images from challenge image library
     *
     * @method GET
     * @param array $payload
     * @return mixed
     */
    public function getImagesList($payload = [])
    {
        $query = $this
            ->select(
                'challenge_image_library.id',
                'challenge_image_library_target_type.target'
            )
            ->join('challenge_image_library_target_type', function ($join) {
                $join->on('challenge_image_library_target_type.id', '=', 'challenge_image_library.target_type');
            });

        if (in_array('target_type', array_keys($payload)) && !empty($payload['target_type'])) {
            $query->where('challenge_image_library.target_type', $payload['target_type']);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('challenge_image_library.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $record = $this->create([
            'target_type' => $payload['target_type'],
        ]);

        if ($record) {
            if (isset($payload['image']) && !empty($payload['image'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('image')
                    ->addMediaFromRequest('image')
                    ->usingName($payload['image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['image']->extension())
                    ->toMediaCollection('image', config('medialibrary.disk_name'));
            }

            return true;
        }

        return false;
    }

    /**
     * store records in bulk.
     *
     * @param payload
     * @return boolean
     */
    public function storeBulkEntity(array $payload)
    {
        foreach ($payload['images'] as $image) {
            $record = $this->create([
                'target_type' => $payload['upload_target_type'],
            ]);

            if ($record) {
                if (isset($image) && !empty($image)) {
                    $name = $record->id . '_' . \time();
                    $record
                        ->clearMediaCollection('image')
                        ->addMedia($image)
                        ->usingName($image->getClientOriginalName())
                        ->usingFileName($name . '.' . $image->extension())
                        ->toMediaCollection('image', config('medialibrary.disk_name'));
                }
                $record = null;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        if (isset($payload['image']) && !empty($payload['image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('image')
                ->addMediaFromRequest('image')
                ->usingName($payload['image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['image']->extension())
                ->toMediaCollection('image', config('medialibrary.disk_name'));
        }

        return true;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */
    public function deleteRecord()
    {
        $isUsed  = $this->challenges->count();
        $deleted = false;

        if ($isUsed > 0) {
            $deleted = $this->delete();
        } else {
            $deleted = $this->forceDelete();
        }

        if ($deleted) {
            return array('deleted' => true);
        }
        return array('deleted' => error);
    }
}
