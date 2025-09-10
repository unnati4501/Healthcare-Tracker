<?php

namespace App\Models;

use App\Jobs\SendTrackPushNotification;
use App\Models\CategoryTags;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Observers\TrackObserver;
use App\Traits\HasRewardPointsTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class MeditationTrack extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'meditation_tracks';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'coach_id',
        'tag_id',
        'type',
        'title',
        'duration',
        'is_premium',
        'deep_link_uri',
        'view_count',
        'audio_type',
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
    protected $casts = [
        'is_premium' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(TrackObserver::class);
    }

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * @return BelongsTo
     */
    public function tracksubcategory(): BelongsTo
    {
        return $this->BelongsTo('App\Models\SubCategory', 'sub_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function trackcoach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meditationGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'meditation_tracks_tag', 'meditation_track_id', 'goal_id');
    }

    /**
     * One-to-Many relations with Meditation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meditationcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'meditation_tracks_company', 'meditation_track_id', 'company_id');
    }

    /**
     * One-to-Many relations with Meditation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meditationteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'meditation_tracks_team', 'meditation_track_id', 'team_id');
    }

    /**
     * "BelongsTo" relation to `category_tags` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(CategoryTags::class, 'tag_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundUrlAttribute(): string
    {
        return $this->getBackgroundUrl(['w' => 160, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundPortalUrlAttribute(): string
    {
        return $this->getBackgroundPortalUrl(['w' => 160, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundUrlNameAttribute(): string
    {
        return $this->getFirstMedia('background')->name;
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundUrl(array $params): string
    {
        $media = $this->getFirstMedia('background');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('background');
        }
        return getThumbURL($params, 'meditation_tracks', 'background');
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundPortalUrl(array $params): string
    {
        $media = $this->getFirstMedia('background_portal');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('background_portal');
        }
        return getThumbURL($params, 'meditation_tracks', 'background_portal');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCoverUrlAttribute(): string
    {
        return $this->getCoverUrl(['w' => 160, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCoverUrlNameAttribute(): string
    {
        return $this->getFirstMedia('cover')->name;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCoverUrl(array $params): string
    {
        $media = $this->getFirstMedia('cover');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('cover');
        }
        return getThumbURL($params, 'meditation_tracks', 'cover');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrackUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('track');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrackUrlNameAttribute(): string
    {
        return $this->getFirstMedia('track')->name;
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImageAttribute(): string
    {
        return $this->getHeaderImage(['w' => 800, 'h' => 800]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImageNameAttribute(): string
    {
        return $this->getFirstMedia('header_image')->name ?? '';
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImage(array $params): string
    {
        $media = $this->getFirstMedia('header_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('header_image');
        }
        return getThumbURL($params, 'meditation_tracks', 'header_image');
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
            ->performOnCollections('track');
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
     * Set datatable for groups list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list     = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('companiesName', function ($record) {
                $companies        = $record->meditationcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                $totalCompanies   = sizeof($companies);

                if ($totalCompanies > 0) {
                    return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                }
            })
            ->addColumn('cover', function ($record) {
                $label = (($record->type == 1) ? trans('meditationtrack.buttons.listen') : trans('meditationtrack.buttons.watch'));
                switch ($record->type) {
                    case '3':
                        $url = $record->getFirstMedia('track')->getCustomProperty('ytid');
                        break;
                    case '4':
                        $url = $record->getFirstMedia('track')->getCustomProperty('vmid');
                        break;
                    default:
                        $url = $record->track_url;
                        break;
                }

                return "<div class='table-img table-img-l'><img src='{$record->cover_url}'/></div>
                <a class='mt-1 play-meditation-media' data-toggle='modal' data-type='{$record->type}' data-source='{$url}' data-title='{$record->title}' href='javascript:void(0);'>
                    <i class='far fa-play mr-2'>
                    </i>
                    {$label}
                </a>";
            })
            ->addColumn('totalLikes', function ($record) {
                return !empty($record->totalLikes) ? numberFormatShort($record->totalLikes) : 0;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.meditationtrack.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'cover', 'track', 'companiesName'])
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
        $query = $this
            ->select(
                'meditation_tracks.*',
                DB::raw('sum(user_meditation_track_logs.view_count) as view_count'),
                DB::raw('SUM(user_meditation_track_logs.liked = 1) AS totalLikes'),
                DB::raw('sub_categories.name AS subcategory_name'),
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS coach_name"),
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag")
            )
            ->leftJoin('users', 'users.id', '=', 'meditation_tracks.coach_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
            ->leftJoin('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', '=', 'meditation_tracks.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'meditation_tracks.tag_id')
            ->where(['sub_categories.status' => 1])
            ->groupBy('meditation_tracks.id');

        if (in_array('trackName', array_keys($payload)) && !empty($payload['trackName'])) {
            $query->where('title', 'like', '%' . $payload['trackName'] . '%');
        }

        if (in_array('coach', array_keys($payload)) && !empty($payload['coach'])) {
            $query->where('coach_id', '=', $payload['coach']);
        }

        if (in_array('subcategory', array_keys($payload)) && !empty($payload['subcategory'])) {
            $query->where('sub_category_id', '=', $payload['subcategory']);
        }

        if (in_array('type', array_keys($payload)) && !empty($payload['type'])) {
            $query->where('type', '=', $payload['type']);
        }

        if (in_array('tag', array_keys($payload)) && !empty($payload['tag'])) {
            if (strtolower($payload['tag']) == 'na') {
                $query->whereNull('meditation_tracks.tag_id');
            } else {
                $query->where('meditation_tracks.tag_id', $payload['tag']);
            }
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $sortcolumn = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order      = $payload['order'][0]['dir'];
            if ($sortcolumn == 'companiesName') {
                $column = "id";
            } else {
                $column = $sortcolumn;
            }
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('meditation_tracks.updated_at', 'DESC');
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
        $role = getUserRole();

        $data = [
            'type'            => $payload['track_type'],
            'title'           => $payload['name'],
            'sub_category_id' => $payload['track_subcategory'],
            'coach_id'        => $payload['health_coach'],
            'duration'        => $payload['duration'],
            'is_premium'      => ($payload['is_premium'] ?? 0),
            'audio_type'      => ($payload['track_type'] == 1 && !empty($payload['audio_type'])) ? $payload['audio_type'] : 0,
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $track = self::create($data);

        $track->deep_link_uri = "zevolife://zevo/meditation-track/" . $track->getKey() . '/' . $track->sub_category_id;
        $track->save();

        if (!empty($payload['track_cover'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('cover')
                ->addMediaFromRequest('track_cover')
                ->usingName($payload['track_cover']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_cover']->extension())
                ->toMediaCollection('cover', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_background'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('background')
                ->addMediaFromRequest('track_background')
                ->usingName($payload['track_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_background_portal'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('background_portal')
                ->addMediaFromRequest('track_background_portal')
                ->usingName($payload['track_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_background_portal']->extension())
                ->toMediaCollection('background_portal', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_audio'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('track')
                ->addMediaFromRequest('track_audio')
                ->usingName($payload['track_audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_audio']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_video'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('track')
                ->addMediaFromRequest('track_video')
                ->usingName($payload['track_video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_video']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['header_image'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (isset($payload['track_youtube']) && !empty($payload['track_youtube'])) {
            $videoId = getYoutubeVideoId($payload['track_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $track->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['track_youtube'], 'ytid' => $videoId];
            $track
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $track->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['track_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['track_vimeo']) && !empty($payload['track_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['track_vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $track->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['track_vimeo'], 'vmid' => $videoId];
            $track
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $track->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['track_vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $track->meditationGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['meditation_company'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['meditation_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $track->meditationcompany()->sync($companyIds);
            $track->meditationteam()->sync($payload['meditation_company']);
        }

        if ($track) {
            // dispatch job to send push notification to all user when meditation created
            \dispatch(new SendTrackPushNotification($track, "track-created"));

            return true;
        } else {
            return false;
        }
    }

    /**
     * get group edit data.
     *
     * @param  $id
     * @return array
     */

    public function trackEditData()
    {
        $data                = array();
        $data['data']        = $this;
        $data['subcategory'] = SubCategory::where('category_id', 4)->pluck('name', 'id')->toArray();
        $healthcoach         = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
            ->where(["is_coach" => 1, 'is_blocked' => 0])
            ->pluck('name', 'id')
            ->toArray();
        $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $healthcoach);
        $data['goalTags']    = Goal::pluck('title', 'id')->toArray();

        $meditation_companys = array();

        if (!empty($this->meditationteam)) {
            $meditation_companys = $this->meditationteam->pluck('id')->toArray();
        }

        $data['meditation_companys'] = $meditation_companys;

        $goal_tags = array();
        if (!empty($this->meditationGoalTag)) {
            $goal_tags = $this->meditationGoalTag->pluck('id')->toArray();
        }
        $data['goal_tags'] = $goal_tags;
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
        $role = getUserRole();

        $data = [
            'title'           => $payload['name'],
            'sub_category_id' => $payload['track_subcategory'],
            'coach_id'        => $payload['health_coach'],
            'duration'        => $payload['duration'],
            'is_premium'      => ($payload['is_premium'] ?? 0),
            'audio_type'      => ($this->type == 1 && !empty($payload['audio_type'])) ? $payload['audio_type'] : 0,
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $updated = $this->update($data);

        if (!empty($payload['track_cover'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('cover')
                ->addMediaFromRequest('track_cover')
                ->usingName($payload['track_cover']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_cover']->extension())
                ->toMediaCollection('cover', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_background'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('background')
                ->addMediaFromRequest('track_background')
                ->usingName($payload['track_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_background_portal'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('background_portal')
                ->addMediaFromRequest('track_background_portal')
                ->usingName($payload['track_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_background_portal']->extension())
                ->toMediaCollection('background_portal', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_audio'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('track')
                ->addMediaFromRequest('track_audio')
                ->usingName($payload['track_audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_audio']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['track_video'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('track')
                ->addMediaFromRequest('track_video')
                ->usingName($payload['track_video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_video']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['header_image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (isset($payload['track_youtube']) && !empty($payload['track_youtube'])) {
            $videoId = getYoutubeVideoId($payload['track_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['track_youtube'], 'ytid' => $videoId];
            $this
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $this->getAllowedMediaMimeTypes('image')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['track_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['track_vimeo']) && !empty($payload['track_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['track_vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['track_vimeo'], 'vmid' => $videoId];
            $this->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['track_vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        $this->meditationGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->meditationGoalTag()->sync($payload['goal_tag']);
        }
        if (!empty($payload['meditation_company'])) {
            $companyIds             = TeamLocation::whereIn('team_id', $payload['meditation_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $existingComapnies      = $this->meditationteam()->pluck('teams.id')->toArray();
            $newlyAssociatedComps   = array_diff($payload['meditation_company'], $existingComapnies);
            $removedAssociatedComps = array_diff($existingComapnies, $payload['meditation_company']);

            // delete notifications for the users which company has been removed from visibility
            if (!empty($removedAssociatedComps)) {
                // Get user id list from companies ids
                $userIds = User::select('users.id')
                    ->join("user_team", "user_team.user_id", "=", "users.id")
                    ->whereIn("user_team.team_id", $removedAssociatedComps)
                    ->get()
                    ->pluck('id')
                    ->toArray();

                Notification::Join('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                    ->whereIn('notification_user.user_id', $userIds)
                    ->where(function ($query) {
                        $query
                            ->where('notifications.tag', 'meditation')
                            ->where('notifications.deep_link_uri', $this->deep_link_uri);
                    })
                    ->delete();
            }

            $this->meditationcompany()->sync($companyIds);
            $this->meditationteam()->sync($payload['meditation_company']);
        }

        // dispatch job to send meditation notification to newly associated company users if any
        if (!empty($newlyAssociatedComps)) {
            \dispatch(new SendTrackPushNotification($this, "track-created", $newlyAssociatedComps));
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

        $xDeviceOs = strtolower(Request()->header('X-Device-Os', ""));

        if ($collection == 'background' && $xDeviceOs == config()->get('zevolifesettings.PORTAL')) {
            $collection = 'background_portal';
        }

        $media = $this->getFirstMedia($collection);

        if (($collection == 'background_portal' || $collection == 'background') && is_null($media) && empty($media)) {
            $collection = ($xDeviceOs == config()->get('zevolifesettings.PORTAL')) ? 'background' : 'background_portal';
            $media      = $this->getFirstMedia($collection);
        }

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'meditation_tracks', $collection);
        return $return;
    }

    public function getSubCategoryData(): array
    {
        $return      = [];
        $subcategory = $this->tracksubcategory;

        $return['id']   = $subcategory->id;
        $return['name'] = $subcategory->name;

        return $return;
    }

    /**
     * @return BelongsToMany
     */
    public function trackUserLogs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_meditation_track_logs', 'meditation_track_id', 'user_id')->withPivot('id', 'saved', 'liked', 'favourited', 'favourited_at', 'view_count')->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->trackUserLogs()->wherePivot('liked', true)->count();
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCoachData(): array
    {
        $return  = [];
        $creator = User::find($this->coach_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @return BelongsToMany
     */
    public function trackIncompletedUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_incompleted_tracks', 'meditation_track_id', 'user_id')->withPivot('id', 'duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function trackListenedUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_listened_tracks', 'meditation_track_id', 'user_id')->withPivot('id', 'duration_listened')->withTimestamps();
    }
}
