<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class CourseLession extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'course_lessions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'course_week_id',
        'title',
        'description',
        'is_default',
        'duration',
        'status',
        'type',
        'auto_progress',
        'order_priority',
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
    protected $casts = ['is_default' => 'boolean', 'status' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course');
    }

    /**
     * @return BelongsTo
     */
    public function courseWeek(): BelongsTo
    {
        return $this->belongsTo('App\Models\CourseWeek');
    }

    /**
     * @param Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('th_lg')
            ->width(640)
            ->height(1280)
            ->optimize()
            ->nonQueued()
            ->extractVideoFrameAtSecond(3)
            ->performOnCollections('video');
    }

    /**
     * @param null|string $part
     *
     * @return array
     */
    public function getAllowedMediaMimeTypes(? string $part) : array
    {
        $mimeTypes = [
            'logo'  => [
                'image/jpeg',
                'image/jpg',
                'image/png',
            ],
            'video' => [
                'video/mp4',
                'video/webm',
            ],
        ];

        return \in_array($part, ['logo', 'video'], true)
        ? $mimeTypes[$part]
        : $mimeTypes;
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute(): string
    {
        return $this->getLogo(['w' => 800, 'h' => 800]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute(): string
    {
        return $this->getFirstMedia('logo')->name ?? '';
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
        return getThumbURL($params, 'course_lessions', 'logo');
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

        $return['url'] = getThumbURL($param, 'course_lessions', $collection);
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
                return $record->logo;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.courses.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getRecordList($payload)
    {
        $query = self::leftJoin('courses', 'course_lessions.course_id', '=', 'courses.id')
            ->select('courses.id', 'courses.title', 'course_lessions.*')
            ->orderBy('course_lessions.updated_at', 'DESC');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('course_lessions.title', 'like', '%' . $payload['recordName'] . '%');
        }

        return $query->get();
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        $this->clearMediaCollection('audio_background');
        $this->clearMediaCollection('audio');
        $this->clearMediaCollection('video');
        $this->clearMediaCollection('youtube');
        if ($this->delete()) {
            self::where('course_id', $this->course_id)
                ->where('order_priority', '>', $this->order_priority)
                ->decrement('order_priority', 1);
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * delete media record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteMediaRecord($type)
    {
        if (!empty($type)) {
            if ($type == 'youtube') {
                $this->clearMediaCollection('youtube');
                return array('deleted' => 'true');
            }
            if ($type == 'video') {
                $this->clearMediaCollection('video');
                return array('deleted' => 'true');
            }
        }
        return array('deleted' => 'error');
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateLessionEntity($payload)
    {
        $updated = $this->update([
            'title'         => $payload['title'],
            'description'   => (!empty($payload['description']) ? $payload['description'] : null),
            'duration'      => convertToHoursMins($payload['duration'], false, '%02d:%02d:00'),
            'auto_progress' => (isset($payload['auto_progress']) && $payload),
        ]);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background']) && !empty($payload['audio_background'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('audio_background')
                ->addMediaFromRequest('audio_background')
                ->usingName($payload['audio_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background']->extension())
                ->toMediaCollection('audio_background', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background_portal']) && !empty($payload['audio_background_portal'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('audio_background_portal')
                ->addMediaFromRequest('audio_background_portal')
                ->usingName($payload['audio_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background_portal']->extension())
                ->toMediaCollection('audio_background_portal', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio']) && !empty($payload['audio'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('audio')
                ->addMediaFromRequest('audio')
                ->usingName($payload['audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio']->getClientOriginalExtension())
                ->toMediaCollection('audio', config('medialibrary.disk_name'));
        }

        if (isset($payload['video']) && !empty($payload['video'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('video')
                ->addMediaFromRequest('video')
                ->usingName($payload['video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['video']->extension())
                ->toMediaCollection('video', config('medialibrary.disk_name'));
        }

        if (isset($payload['youtube']) && !empty($payload['youtube'])) {
            $existingmedia = $this->getFirstMedia('youtube');
            if (is_null($existingmedia) || (isset($existingmedia->name) && $existingmedia->name != $payload['youtube'])) {
                $videoId          = getYoutubeVideoId($payload['youtube']);
                $name             = $this->id . '_' . \time();
                $customProperties = ['title' => $payload['title'], 'link' => $payload['youtube'], 'ytid' => $videoId];
                $this
                    ->clearMediaCollection('youtube')
                    ->addMediaFromUrl(
                        getYoutubeVideoCover($videoId, 'hqdefault'),
                        $this->getAllowedMediaMimeTypes('image')
                    )
                    ->withCustomProperties($customProperties)
                    ->usingName($payload['youtube'])
                    ->usingFileName($name . '.png')
                    ->toMediaCollection('youtube', config('medialibrary.disk_name'));
            }
        }

        if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['vimeo'], 'vmid' => $videoId];
            $this->clearMediaCollection('vimeo')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('vimeo', config('medialibrary.disk_name'));
        }

        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function courseUserLessonLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_lession', 'course_lession_id', 'user_id')->withPivot('course_id', 'status', 'completed_at')->withTimestamps();
    }

    public function courseUserLessonData($userId)
    {
        return $this->courseUserLessonLogs()->wherePivot("user_id", $userId)->first();
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLessonData(array $params): string
    {
        $media = $this->getFirstMedia($params['conversion']);
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl($params['conversion']);
        }
        return getThumbURL($params, 'course_survey_question', $params['conversion']);
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLessonImageData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];

        $xDeviceOs = strtolower(Request()->header('X-Device-Os', ""));
        if ($collection == 'audio_background' && $xDeviceOs == config()->get('zevolifesettings.PORTAL')) {
            $collection = 'audio_background_portal';
        }

        $media = $this->getFirstMedia($collection);

        if (($collection == 'audio_background_portal' || $collection == 'audio_background') && is_null($media) && empty($media)) {
            $collection = ($xDeviceOs == config()->get('zevolifesettings.PORTAL')) ? 'audio_background' : 'audio_background_portal';
            $media      = $this->getFirstMedia($collection);
        }

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'course_lession', $collection);
        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getLessonMediaData()
    {
        $return     = [];
        $collection = "";
        if (!empty($this->type) && $this->type == 1) {
            $return['type'] = "AUDIO";
            $collection     = "audio";
        } elseif (!empty($this->type) && $this->type == 2) {
            $return['type'] = "VIDEO";
            $collection     = "video";
        } elseif (!empty($this->type) && $this->type == 3) {
            $return['type'] = "YOUTUBE";
            $collection     = "youtube";
        } elseif (!empty($this->type) && $this->type == 5) {
            $return['type'] = "VIMEO";
            $collection     = "vimeo";
        }
        $media          = $this->getFirstMedia($collection);
        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
        $w              = 640;
        $h              = 1280;
        $youtubeBaseURL = config('zevolifesettings.youtubeappurl');
        $vimeoURL       = config('zevolifesettings.vimeoappurl');
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w              = 600;
            $h              = 400;
            $youtubeBaseURL = config('zevolifesettings.youtubeembedurl');
            $vimeoURL       = config('zevolifesettings.vimeoembedurl');
        }

        if (!empty($this->type) && $this->type == 1) {
            $return['backgroundImage'] = $this->getLessonImageData('audio_background', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $return['mediaURL']        = \url($media->getUrl());
        } elseif (!empty($this->type) && $this->type == 2) {
            $return['backgroundImage'] = $this->getLessonImageData('video', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['mediaURL']        = \url($media->getUrl());
        } elseif (!empty($this->type) && $this->type == 3) {
            $return['backgroundImage'] = $this->getLessonImageData('youtube', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $link                      = $youtubeBaseURL . $media->getCustomProperty('ytid');
            $return['mediaURL']        = $link;
        } elseif (!empty($this->type) && $this->type == 5) {
            $return['backgroundImage'] = $this->getLessonImageData('vimeo', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $link                      = $vimeoURL . $media->getCustomProperty('vmid');
            $return['mediaURL']        = $link;
        }
        return $return;
    }

    public function publishLession()
    {
        $updated = $this->update(['status' => 1]);
        if ($updated) {
            $data['published'] = true;
            $data['message']   = "Lesson has been published successfully.";
        } else {
            $data['published'] = false;
            $data['message']   = "Something went wrong while publishing a lesson!";
        }
        return $data;
    }
}
