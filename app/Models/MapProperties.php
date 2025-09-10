<?php
namespace App\Models;

use App\Models\ChallengeMapLibrary;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MapProperties extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'map_properties';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['map_id', 'properties', 'short_name'];

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
     * "BelongsTo" relation to `map_properties` table
     * via `map_id` field.
     *
     * @return BelongsTo
     */
    public function map(): BelongsTo
    {
        return $this->belongsTo(ChallengeMapLibrary::class, 'map_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getImage(['w' => 512, 'h' => 512]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImage(array $params): string
    {
        $media = $this->getFirstMedia('propertyimage');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('propertyimage');
        }
        return getThumbURL($params, 'challenge_map', 'propertyimage');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageNameAttribute()
    {
        $media = $this->getFirstMedia('propertyimage');
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
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $propertiesId    = $payload['property_id'];
        $latLong         = explode(',', $payload['lat_long']);
        $previousRecords = $this->select(
            \DB::raw('SUM(properties->>"$.steps") AS previous_steps'),
            \DB::raw('SUM(properties->>"$.distance") AS previous_distance')
        )
            ->where('id', '<', $propertiesId)
            ->where('map_id', $payload['map_id'])
            ->groupBy('map_id')
            ->first();

        $previous_steps    = 0;
        $previous_distance = 0;
        if (!empty($previousRecords)) {
            $previous_steps    = (int) $previousRecords->previous_steps;
            $previous_distance = (int) $previousRecords->previous_distance;
        }

        $meta = [
            "lat"               => $latLong[0],
            "lng"               => $latLong[1],
            "locationType"      => $payload['location_type'],
            "locationName"      => $payload['location_name'],
            "distance"          => ($payload['num'] > 1) ? $payload['distance_location'] : 0,
            "steps"             => ($payload['num'] > 1) ? $payload['steps_location'] : 0,
            'previous_steps'    => $previous_steps,
            'previous_distance' => $previous_distance,
        ];

        if (!empty($propertiesId)) {
            $properties             = $this->find($propertiesId);
            $properties->properties = json_encode($meta);
            $properties->update();

            if (isset($payload['propertyimage']) && !empty($payload['propertyimage'])) {
                $properties->clearMediaCollection('propertyimage');
                $name = $properties->id . '_' . \time();
                $properties->clearMediaCollection('propertyimage')
                    ->addMediaFromBase64($payload['propertyimage'])
                    ->usingName($name)
                    ->toMediaCollection('propertyimage', config('medialibrary.disk_name'));
            }

            return $properties;
        } else {
            $properties = [
                'map_id'     => $payload['map_id'],
                'properties' => json_encode($meta),
            ];
            $updated = $this->create($properties);

            if (isset($payload['propertyimage']) && !empty($payload['propertyimage'])) {
                $name = $updated->id . '_' . \time();
                $updated->clearMediaCollection('propertyimage')
                    ->addMediaFromBase64($payload['propertyimage'])
                    ->usingName($name)
                    ->toMediaCollection('propertyimage', config('medialibrary.disk_name'));
            }

            return $updated;
        }
    }

    public function storeLatLongEntity(array $payload)
    {
        $latLong = explode(',', $payload['lat_long']);
        $meta    = [
            "lat" => $latLong[0],
            "lng" => $latLong[1],
        ];

        $properties = [
            'map_id'     => $payload['map_id'],
            'properties' => json_encode($meta),
        ];

        return $this->create($properties);
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
            ChallengeMapLibrary::where('id', $this->map_id)
                ->limit(1)
                ->decrement('total_location');
            $data['deleted'] = true;
            return $data;
        }

        return $data;
    }
}
