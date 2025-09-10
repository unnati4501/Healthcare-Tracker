<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ZevoMeditationCategory extends Model implements HasMedia
{

    use InteractsWithMedia;

    protected $connection = 'mysql-zevohealth';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inspire_albums';

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
        return $this->hasMany('App\Models\ZevoMeditationTrack');
    }

    /**
     * @param Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $imageConversions = config('zevolifesettings.imageConversions.zevo_meditation_category');
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
    public function getLogoAttribute(): string
    {
        $returnString = '';
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Album%')->where('collection_name', 'cover')->first();
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
        $getTrack     = \DB::connection('mysql-zevohealth')->table('media')->where('model_id', $this->id)->where('model_type', 'like', '%Album%')->where('collection_name', 'cover')->first();
        if (!empty($getTrack)) {
            $returnString = $getTrack->file_name;
        }

        return $returnString;
    }
}
