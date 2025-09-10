<?php

namespace App\Models;

use App\Models\Challenge;
use App\Models\MapProperties;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class ChallengeMapLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'map_library';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'total_location',
        'total_distance',
        'total_steps',
        'status',
        'created_at',
        'updated_at',
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
    protected $dates = ['created_at', 'updated_at'];

    /**
     * One-to-Many relations with Map Library.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mapcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'map_company', 'map_id', 'company_id');
    }

    /**
     * "BelongsTo" relation to `map_properties` table
     * via `map_id` field.
     *
     * @return BelongsTo
     */
    public function mapProperties(): BelongsTo
    {
        return $this->belongsTo(MapProperties::class, 'id', 'map_id');
    }

    /**
     * "BelongsTo" relation to `challenge` table
     * via `map_id` field.
     *
     * @return BelongsTo
     */
    public function mapChallenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class, 'id', 'map_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getImage(['w' => 1280, 'h' => 640]);
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
        return getThumbURL($params, 'challenge_map', 'image');
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
        $return['url'] = getThumbURL($param, 'challenge_map', $collection);
        return $return;
    }

    /**
     * Get data table data for challenge map listing.
     * @param $payload
     * @return mixed
     * @throws \Exception
     */
    public function getTableData($payload)
    {
        $timezone = config('app.timezone');
        $list     = $this->getMapLibraryList($payload);
        $user     = auth()->user();
        $role     = getUserRole($user);
        $now      = now($timezone)->toDateTimeString();
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('image', function ($record) {
                return '<div class="table-img table-img-l"><img src="' . $record->image . '"/></div>';
            })
            ->addColumn('locations', function ($record) use ($role) {
                if ($role->group == 'company') {
                    $locationDetails = $record->mapProperties()->select('map_properties.id', \DB::raw('properties->>"$.locationName" AS location_name'))->get()->toArray();

                    return "<a href='javascript:void(0);' title='View Location Name' class='preview_map_locations' data-rowdata='" . base64_encode(json_encode($locationDetails)) . "' data-cid='" . $record->id . "'> " . $record->total_location . "</a>";
                }
                return $record->total_location;
            })
            ->addColumn('description', function ($record) {
                $stringLen = strlen($record->description);
                if ($stringLen > 40) {
                    return mb_strimwidth($record->description, 0, 40) . ' <a href="javascript:void(0);" title="View description" class="preview_description" data-rowdata="' . $record->description . '" data-cid="' . $record->id . '">Read more</a>';
                }
                return $record->description;
            })
            ->addColumn('status', function ($record) use ($now) {
                $attechCount = $record->mapChallenge()
                    ->where(function ($q) use ($now) {
                        $q->where('challenges.start_date', '>=', $now)
                            ->orWhere('challenges.start_date', '<=', $now);
                    })
                    ->where('challenges.end_date', '>=', $now)
                    ->where('challenges.finished', '=', false)
                    ->where('challenges.cancelled', '=', false)
                    ->count();

                if ($attechCount > 0) {
                    $status = '<span style="color:green">Active</span>';
                } else {
                    $status = '<span style="color:red">Inactive</span>';
                }
                return $status;
            })
            ->addColumn('actions', function ($record) use ($now) {
                $attechCount = $record->mapChallenge()->count();
                $activeCount = $record->mapChallenge()
                    ->where(function ($q) use ($now) {
                        $q->where('challenges.start_date', '>=', $now)
                            ->orWhere('challenges.start_date', '<=', $now);
                    })
                    ->where('challenges.end_date', '>=', $now)
                    ->where('challenges.finished', '=', false)
                    ->where('challenges.cancelled', '=', false)
                    ->count();
                return view('admin.challenge_map_library.listaction', compact('record', 'attechCount', 'activeCount'))->render();
            })
            ->rawColumns(['image', 'locations', 'description', 'actions', 'status'])
            ->make(true);
    }

    /**
     * Get images from challenge map library
     *
     * @method GET
     * @param array $payload
     * @return mixed
     */
    public function getMapLibraryList($payload = [])
    {
        $query = $this
            ->select(
                'map_library.id',
                'map_library.name',
                'map_library.description',
                'map_library.total_location',
                'map_library.total_distance',
                'map_library.total_steps',
                'map_library.status'
            )
            ->whereNotIn('status', [3]);

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            if ($column == 'locations') {
                $query->orderBy('total_location', $order);
            } else {
                $query->orderBy($column, $order);
            }
        } else {
            $query->orderByDesc('map_library.updated_at');
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
            'name'           => $payload['name'],
            'description'    => $payload['description'],
            'total_location' => $payload['total_locations'],
            'total_distance' => isset($payload['total_distance']) ? $payload['total_distance'] : 0,
            'total_steps'    => isset($payload['total_steps']) ? $payload['total_steps'] : 0,
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

            if (!empty($payload['members_selected'])) {
                $record->mapcompany()->sync($payload['members_selected']);
            }

            if (!empty($payload['location'])) {
                $locations = $payload['location'];

                foreach ($locations as $location) {
                    $latLong = explode(',', $location);
                    $meta    = [
                        "lat" => $latLong[0],
                        "lng" => $latLong[1],
                    ];
                    $properties = [
                        'map_id'     => $record->id,
                        'properties' => json_encode($meta),
                    ];

                    $record->mapProperties()->create($properties);
                }
            }

            return $record;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        $data = [
            'name'        => $payload['name'],
            'description' => $payload['description'],
        ];

        $updated = $this->update($data);

        if (!empty($payload['image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('image')
                ->addMediaFromRequest('image')
                ->usingName($payload['image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['image']->extension())
                ->toMediaCollection('image', config('medialibrary.disk_name'));
        }

        if (!empty($payload['members_selected'])) {
            $this->mapcompany()->sync($payload['members_selected']);
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
        $data = ['deleted' => false];

        if ($this->delete()) {
            $data['deleted'] = true;
            return $data;
        }

        return $data;
    }

    /**
     * archive record by record id.
     *
     * @param $id
     * @return array
     */
    public function archiveRecord()
    {
        $data = [
            'status'     => 3,
            'updated_at' => \now(config('app.timezone'))->toDateTimeString(),
        ];

        $updated = $this->update($data);

        if ($updated) {
            $data['archived'] = true;
            return $data;
        }

        return $data;
    }

    /**
     * get group edit data.
     *
     * @param  $id
     * @return array
     */
    public function mapEditData()
    {
        $mapProperties = MapProperties::where('map_id', $this->id)
            ->select('properties', 'id')
            ->get();

        $i             = 1;
        $totalDistance = 0;
        $totalSteps    = 0;
        $propertyArray = [];
        foreach ($mapProperties as $value) {
            $property = json_decode($value->properties);
            $distance = isset($property->distance) ? $property->distance : 0;
            $steps    = isset($property->steps) ? $property->steps : 0;
            $lat      = isset($property->lat) ? $property->lat : 0;
            $lng      = isset($property->lng) ? $property->lng : 0;
            $totalDistance += $distance;
            $totalSteps += $steps;
            $propertyArray[] = [
                'key'           => $i,
                'id'            => $value->id,
                'lat'           => $lat,
                'lng'           => $lng,
                'latLong'       => $lat . ',' . $lng,
                'locationName'  => isset($property->locationName) ? $property->locationName : '',
                'locationType'  => isset($property->locationType) ? $property->locationType : '',
                'distance'      => $distance,
                'steps'         => $steps,
                'propertyImage' => $value,
            ];
            $i++;
        }

        $data                  = array();
        $data['record']        = $this;
        $data['locationsType'] = [
            1 => 'Main Type',
            2 => 'Sub Type',
        ];
        $data['mapProperties'] = $propertyArray;
        $data['totalDistance'] = $totalDistance;
        $data['totalSteps']    = $totalSteps;
        $data['mapCompanies']  = $this->mapcompany->pluck('id')->toArray();
        $data['edit']          = true;

        return $data;
    }
}
