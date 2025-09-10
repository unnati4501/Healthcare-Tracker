<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ZevoMeditationTrack extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $connection = 'mysql-zevohealth';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inspire_tracks';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inspire_album_id',
        'title',
        'duration',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function trackcategory(): BelongsTo
    {
        return $this->BelongsTo('App\Models\ZevoMeditationCategory', 'inspire_album_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute(): string
    {
        $returnString = '';
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Track%')->where('collection_name', 'cover')->first();
        if (!empty($getTrack)) {
            if ($getTrack->disk == config('medialibrary.disk_name')) {
                $returnString = 'https://zevohealthappstorage.sfo2.digitaloceanspaces.com/' . env('ZEVOHEALTH_MEDIA_FOLDER') . '/' . $getTrack->id . '/' . $getTrack->file_name;
            } else {
                $returnString = 'https://zevohealthappstorage.sfo2.digitaloceanspaces.com/' . env('ZEVOHEALTH_MEDIA_LOCAL_FOLDER') . '/' . $getTrack->id . '/' . $getTrack->file_name;
            }
        }

        return $returnString;
    }

    public function getLogofileAttribute(): string
    {
        $returnString = '';
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Track%')->where('collection_name', 'cover')->first();
        if (!empty($getTrack)) {
            $returnString = $getTrack->file_name;
        }

        return $returnString;
    }

    public function getTrackAttribute()
    {
        $returnString = '';
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Track%')->where('collection_name', 'track')->first();
        if (!empty($getTrack)) {
            if ($getTrack->disk == config('medialibrary.disk_name')) {
                $returnString = 'https://zevohealthappstorage.sfo2.digitaloceanspaces.com/' . env('ZEVOHEALTH_MEDIA_FOLDER') . '/' . $getTrack->id . '/' . $getTrack->file_name;
            } else {
                $returnString = 'https://zevohealthappstorage.sfo2.digitaloceanspaces.com/' . env('ZEVOHEALTH_MEDIA_LOCAL_FOLDER') . '/' . $getTrack->id . '/' . $getTrack->file_name;
            }
        }

        return $returnString;
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrackfileAttribute(): string
    {
        $returnString = '';
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Track%')->where('collection_name', 'track')->first();
        if (!empty($getTrack)) {
            $returnString = $getTrack->file_name;
        }

        return $returnString;
    }

    /**
     * @param Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $imageConversions = config('zevolifesettings.imageConversions.zevo_meditation_track');
        foreach ($imageConversions as $conversion => $size) {
            $this->addMediaConversion($conversion)
                ->width($size['width'])
                ->height($size['height'])
                ->optimize()
                // ->keepOriginalImageFormat()
                ->nonQueued()
                ->performOnCollections('cover');
            $this->addMediaConversion($conversion)
                ->width($size['width'])
                ->height($size['height'])
                ->optimize()
                // ->keepOriginalImageFormat()
                ->nonQueued()
                ->performOnCollections('background');
        }
    }
}
