<?php

namespace App\Models;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserExercise extends Pivot implements HasMedia
{

    use SoftDeletes, InteractsWithMedia;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_exercise';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'exercise_id',
        'tracker',
        'exercise_key',
        'is_manual',
        'duration',
        'distance',
        'calories',
        'start_date',
        'end_date',
        'deleted_at',
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
    protected $dates = ['start_date', 'end_date', 'deleted_at'];

    /**
     * "BelongsTo" relation to `user_exercise` table
     * via `exercise_id` field.
     *
     * @return BelongsTo
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
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
        $return['url'] = getThumbURL($param, 'user_exercise', $collection);
        return $return;
    }

    /**
     * @param string $size
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getRouteMediaData(string $collection, array $param): array
    {
        $return = ['width' => 0, 'height' => 0, 'url' => ''];
        $media  = $this->getFirstMedia($collection);
        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection);
            $return       = [
                'width'  => $param['w'],
                'height' => $param['h'],
                'url'    => getThumbURL($param, 'user_exercise', $collection),
            ];
        }
        return $return;
    }
}
