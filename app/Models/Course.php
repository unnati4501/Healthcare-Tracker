<?php

namespace App\Models;

use App\Jobs\SendCourseAddEditPushNotification;
use App\Models\CategoryTags;
use App\Models\CourseLession;
use App\Models\CourseSurvey;
use App\Models\MasterclassCsatLogs;
use App\Models\SubCategory;
use App\Models\User;
use App\Observers\CourseObserver;
use App\Traits\HasRewardPointsTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Course extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'courses';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'category_id',
        'sub_category_id',
        'title',
        'description',
        'benefits',
        'instructions',
        'deep_link_uri',
        'is_premium',
        'expertise_level',
        'random_students',
        'has_trailer',
        'trailer_type',
        'status',
        'tag_id',
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
    protected $casts = ['is_premium' => 'boolean', 'status' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(CourseObserver::class);
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
    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\Category');
    }

    /**
     * @return BelongsTo
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    /**
     * @return HasMany
     */
    public function courseWeeks(): HasMany
    {
        return $this->hasMany('App\Models\CourseWeek');
    }

    /**
     * @return HasMany
     */
    public function courseLessions(): HasMany
    {
        return $this->hasMany('App\Models\CourseLession');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courseGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'course_tag', 'course_id', 'goal_id');
    }

    /**
     * One-to-Many relations with Course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function masterclassCompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'masterclass_company', 'masterclass_id', 'company_id');
    }

    /**
     * One-to-Many relations with Course.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function masterclassteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'masterclass_team', 'masterclass_id', 'team_id');
    }

    /**
     * "BelongsToMany" relation to `masterclass_csat_user_logs` table
     * via `course_id` field.
     *
     * @return hasMany
     */
    public function csat(): BelongsToMany
    {
        return $this->belongsToMany(User::class, MasterclassCsatLogs::class, 'course_id')
            ->withPivot('company_id', 'feedback_type', 'feedback')
            ->withTimestamps();
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
     * @param Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('th_lg')
            ->width(640)
            ->height(1280)
            ->optimize()
            ->nonQueued()
            ->extractVideoFrameAtSecond(3)
            ->performOnCollections('trailer_video');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 320, 'h' => 160]);
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
        return getThumbURL($params, 'course', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrailerBackgroundAttribute()
    {
        return $this->getTrailerBackground(['w' => 320, 'h' => 160]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrailerBackgroundPortalAttribute()
    {
        return $this->getTrailerBackgroundPortal(['w' => 320, 'h' => 160]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrackVimeoAttribute()
    {
        return $this->getTrackVimeoBackground(['w' => 320, 'h' => 160]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrailerBackground(array $params): string
    {
        $media = $this->getFirstMedia('trailer_background');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('trailer_background');
        }
        return getThumbURL($params, 'course', 'trailer_background');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrailerBackgroundPortal(array $params): string
    {
        $media = $this->getFirstMedia('trailer_background_portal');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('trailer_background_portal');
        }
        return getThumbURL($params, 'course', 'trailer_background_portal');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getTrackVimeoBackground(array $params): string
    {
        $media = $this->getFirstMedia('track_vimeo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('track_vimeo');
        }
        return getThumbURL($params, 'course', 'track_vimeo');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getVideoTrailerBackground(array $params): string
    {
        $media = $this->getFirstMedia('trailer_video');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('trailer_video', ($params['conversion'] ?? ""));
        }
        return getThumbURL($params, 'course', 'trailer_video');
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
        return getThumbURL($params, 'course', 'header_image');
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

        return (in_array($part, ['logo', 'video'], true) ? $mimeTypes[$part] : $mimeTypes);
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list        = $this->getRecordList($payload);
        $hydratelist = Course::hydrate($list['record']);

        return DataTables::of($hydratelist)
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('logo', function ($record) {
                return "<div class='table-img table-img-l'><img src='{$record->logo}' /></div>";
            })
            ->addColumn('visible_to_company', function ($record) {
                $companies        = $record->masterclasscompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                $totalCompanies   = sizeof($companies);

                if ($totalCompanies > 0) {
                    return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                }
            })
            ->addColumn('totalDurarion', function ($record) {
                return (!empty($record->totalDurarion) ? timeToDecimal($record->totalDurarion) : 0) . " Minute(s)";
            })
            ->addColumn('total_members', function ($record) {
                return numberFormatShort($record->total_members);
            })
            ->addColumn('status', function ($record) {
                return view('admin.course.publishaction', compact('record'))->render();
            })
            ->addColumn('actions', function ($record) {
                return view('admin.course.listaction', compact('record'))->render();
            })
            ->addColumn('totalLikes', function ($record) {
                return !empty($record->totalLikes) ? numberFormatShort($record->totalLikes) : 0;
            })
            ->rawColumns(['logo', 'visible_to_company', 'actions', 'status'])
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
        $query = DB::table('courses AS c')
            ->leftJoin('sub_categories', 'c.sub_category_id', '=', 'sub_categories.id')
            ->leftJoin('users', 'c.creator_id', '=', 'users.id')
            ->leftJoin('course_lessions', 'c.id', '=', 'course_lessions.course_id')
            ->leftJoin('user_course', 'user_course.course_id', '=', 'c.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'c.tag_id')
            ->select(
                'c.id',
                'c.title',
                'c.updated_at',
                'c.status',
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag"),
                DB::raw("sub_categories.name as subcategory"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name) as coachName"),
                DB::raw("COUNT(DISTINCT course_lessions.id) as totalLessions"),
                DB::raw("(SELECT TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(course_lessions.duration))),'%H:%i:%s') FROM course_lessions where course_id = c.id) AS totalDurarion"),
                DB::raw('(SELECT SUM(liked = 1) FROM user_course where course_id = c.id) AS totalLikes'),
                DB::raw('(SELECT COUNT(id) FROM user_course where course_id = c.id AND pre_survey_completed = 1) AS total_members')
            )
            ->groupBy('c.id');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('c.title', 'like', '%' . $payload['recordName'] . '%');
        }
        if (in_array('recordCategory', array_keys($payload)) && !empty($payload['recordCategory'])) {
            $query->where('c.sub_category_id', $payload['recordCategory']);
        }
        if (in_array('recordCoach', array_keys($payload)) && !empty($payload['recordCoach'])) {
            $query->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['recordCoach'] . '%');
        }
        if (in_array('recordTag', array_keys($payload)) && !empty($payload['recordTag'])) {
            if (strtolower($payload['recordTag']) == 'na') {
                $query->whereNull('c.tag_id');
            } else {
                $query->where('c.tag_id', $payload['recordTag']);
            }
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('c.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get()->toArray(),
        ];
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $record = self::create([
            'category_id'     => 1,
            'sub_category_id' => (int) $payload['sub_category'],
            'title'           => $payload['title'],
            'creator_id'      => (int) $payload['health_coach'],
            'has_trailer'     => (bool) ($payload['has_trailer'] ?? 0),
            'trailer_type'    => (!empty($payload['trailer_type']) ? $payload['trailer_type'] : 0),
            'instructions'    => $payload['description'],
            'is_premium'      => false,
            'tag_id'          => (!empty($payload['tag']) ? $payload['tag'] : null),
        ]);

        $coursesBadge              = new Badge();
        $coursesBadge->creator_id  = auth()->user()->getKey();
        $coursesBadge->type        = "masterclass";
        $coursesBadge->title       = $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle');
        $coursesBadge->description = $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle');
        $coursesBadge->target      = 0;
        $coursesBadge->model_id    = $record->id;
        $coursesBadge->model_name  = "masterclass";
        $coursesBadge->save();

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio_background']) && !empty($payload['trailer_audio_background'])) {
            $name = $record->id . '_trailer_background_' . \time();
            $record
                ->clearMediaCollection('trailer_background')
                ->addMediaFromRequest('trailer_audio_background')
                ->usingName($payload['trailer_audio_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio_background']->extension())
                ->toMediaCollection('trailer_background', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio_background_portal']) && !empty($payload['trailer_audio_background_portal'])) {
            $name = $record->id . '_trailer_background_portal_' . \time();
            $record
                ->clearMediaCollection('trailer_background_portal')
                ->addMediaFromRequest('trailer_audio_background_portal')
                ->usingName($payload['trailer_audio_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio_background_portal']->extension())
                ->toMediaCollection('trailer_background_portal', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio']) && !empty($payload['trailer_audio'])) {
            $name = $record->id . '_trailer_audio_' . \time();
            $record
                ->clearMediaCollection('trailer_audio')
                ->addMediaFromRequest('trailer_audio')
                ->usingName($payload['trailer_audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio']->getClientOriginalExtension())
                ->toMediaCollection('trailer_audio', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_video']) && !empty($payload['trailer_video'])) {
            $name = $record->id . '_trailer_video_' . \time();
            $record
                ->clearMediaCollection('trailer_video')
                ->addMediaFromRequest('trailer_video')
                ->usingName($payload['trailer_video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_video']->extension())
                ->toMediaCollection('trailer_video', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_youtube']) && !empty($payload['trailer_youtube'])) {
            $videoId = getYoutubeVideoId($payload['trailer_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $record->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['trailer_youtube'], 'ytid' => $videoId];
            $record
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $record->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['trailer_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_vimeo']) && !empty($payload['trailer_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['trailer_vimeo']);

            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $customProperties = ['title' => $payload['title'], 'link' => $payload['trailer_vimeo'], 'vmid' => $videoId];
            $name             = $record->id . '_track_vimeo_' . \time();
            $record
                ->clearMediaCollection('track_vimeo')
                ->addMediaFromRequest('track_vimeo')
                ->usingName($payload['track_vimeo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_vimeo']->extension())
                ->toMediaCollection('track_vimeo', config('medialibrary.disk_name'));

            $name = $this->id . '_' . \time();
            $record
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $record->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['trailer_vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['header_image'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $record->courseGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['masterclass_company'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['masterclass_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $record->masterclasscompany()->sync($companyIds);
            $record->masterclassteam()->sync($payload['masterclass_company']);
        }

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function defaultLesstion()
    {
        return $this->courseLessions()->where('course_lessions.is_default', true)->first();
    }

    /**
     * get feed edit data.
     *
     * @param  $id
     * @return array
     */

    public function courseEditData()
    {
        $courseGoalTag        = $this->courseGoalTag;
        $masterclassteam      = $this->masterclassteam;
        $associated_comapnies = Collect(DB::select("SELECT user_team.company_id FROM user_course INNER JOIN user_team on (user_team.user_id = user_course.user_id) WHERE course_id = ? AND pre_survey_completed = 1 GROUP BY user_team.company_id", [$this->id]));
        $associated_comapnies = $associated_comapnies->pluck('company_id');
        $healthcoach          = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
            ->where(["is_coach" => 1, 'is_blocked' => 0])
            ->pluck('name', 'id')
            ->toArray();

        return [
            'record'               => $this,
            'subcategories'        => SubCategory::where('status', 1)->where("category_id", 1)->pluck('name', 'id')->toArray(),
            'lessionRecord'        => $this->defaultLesstion(),
            'healthcoach'          => array_replace([1 => 'Zevo Admin'], $healthcoach),
            'goalTags'             => Goal::pluck('title', 'id')->toArray(),
            'goal_tags'            => ((!empty($courseGoalTag)) ? $courseGoalTag->pluck('id')->toArray() : []),
            'masterclass_company'  => ((!empty($masterclassteam)) ? $masterclassteam->pluck('id')->toArray() : []),
            'associated_comapnies' => $associated_comapnies,
            'tags'                 => CategoryTags::where("category_id", 1)->pluck('name', 'id')->toArray(),
            'tag'                  => 0,
        ];
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $updated = $this->update([
            'category_id'     => 1,
            'sub_category_id' => (int) $payload['sub_category'],
            'title'           => $payload['title'],
            'has_trailer'     => (bool) ($payload['has_trailer'] ?? 0),
            'creator_id'      => (int) $payload['health_coach'],
            'instructions'    => $payload['description'],
            'is_premium'      => false,
            'tag_id'          => (!empty($payload['tag']) ? $payload['tag'] : null),
        ]);

        $badgeData = Badge::where("model_id", $this->getKey())->where("model_name", "masterclass")->first();
        if (!empty($badgeData)) {
            $badgeData->update([
                'type'        => "masterclass",
                'title'       => $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle'),
                'description' => $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle'),
                'model_id'    => $this->getKey(),
                'model_name'  => "masterclass",
            ]);
        } else {
            $badgeData              = new Badge();
            $badgeData->creator_id  = auth()->user()->getKey();
            $badgeData->type        = "masterclass";
            $badgeData->title       = $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle');
            $badgeData->description = $payload['title'] . " " . config('zevolifesettings.courseBadgeTitle');
            $badgeData->target      = 0;
            $badgeData->model_id    = $this->getKey();
            $badgeData->model_name  = "masterclass";
            $badgeData->save();
        }

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio_background']) && !empty($payload['trailer_audio_background'])) {
            $name = $this->id . '_trailer_background_' . \time();
            $this->clearMediaCollection('trailer_background')
                ->addMediaFromRequest('trailer_audio_background')
                ->usingName($payload['trailer_audio_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio_background']->extension())
                ->toMediaCollection('trailer_background', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio_background_portal']) && !empty($payload['trailer_audio_background_portal'])) {
            $name = $this->id . '_trailer_background_portal_' . \time();
            $this
                ->clearMediaCollection('trailer_background_portal')
                ->addMediaFromRequest('trailer_audio_background_portal')
                ->usingName($payload['trailer_audio_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio_background_portal']->extension())
                ->toMediaCollection('trailer_background_portal', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_audio']) && !empty($payload['trailer_audio'])) {
            $name = $this->id . '_trailer_audio_' . \time();
            $this->clearMediaCollection('trailer_audio')
                ->addMediaFromRequest('trailer_audio')
                ->usingName($payload['trailer_audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_audio']->getClientOriginalExtension())
                ->toMediaCollection('trailer_audio', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_video']) && !empty($payload['trailer_video'])) {
            $name = $this->id . '_trailer_video_' . \time();
            $this->clearMediaCollection('trailer_video')
                ->addMediaFromRequest('trailer_video')
                ->usingName($payload['trailer_video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['trailer_video']->extension())
                ->toMediaCollection('trailer_video', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_youtube']) && !empty($payload['trailer_youtube'])) {
            $videoId = getYoutubeVideoId($payload['trailer_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['trailer_youtube'], 'ytid' => $videoId];
            $this->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['trailer_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['trailer_vimeo']) && !empty($payload['trailer_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['trailer_vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $customProperties = ['title' => $payload['title'], 'link' => $payload['trailer_vimeo'], 'vmid' => $videoId];
            $name             = $this->id . '_' . \time();
            $this->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['trailer_vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['track_vimeo']) && !empty($payload['track_vimeo'])) {
            $name = $this->id . '_track_vimeo_' . \time();
            $this->clearMediaCollection('track_vimeo')
                ->addMediaFromRequest('track_vimeo')
                ->usingName($payload['track_vimeo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['track_vimeo']->getClientOriginalExtension())
                ->toMediaCollection('track_vimeo', config('medialibrary.disk_name'));
        }

        if (isset($payload['header_image']) && !empty($payload['header_image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        $this->courseGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->courseGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['masterclass_company'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['masterclass_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();

            if ($this->status) {
                $existingComapnies      = $this->masterclassteam()->pluck('teams.id')->toArray();
                $newlyAssociatedComps   = array_diff($payload['masterclass_company'], $existingComapnies);
                $removedAssociatedComps = array_diff($existingComapnies, $payload['masterclass_company']);

                // delete notifications for the users which company has been removed from visibility
                if (!empty($removedAssociatedComps)) {
                    Notification::whereIn('company_id', $companyIds)
                        ->where(function ($query) {
                            $query
                                ->where('deep_link_uri', 'LIKE', $this->deep_link_uri . '/%')
                                ->orWhere('deep_link_uri', 'LIKE', $this->deep_link_uri);
                        })
                        ->delete();

                    // delete NPS related notificaions
                    $npsDeeplinkURI = __(config('zevolifesettings.deeplink_uri.masterclass_csat'), [
                        'id' => $this->id,
                    ]);
                    Notification::where('deep_link_uri', 'LIKE', $npsDeeplinkURI . '/%')
                        ->orWhere('deep_link_uri', 'LIKE', $npsDeeplinkURI)
                        ->delete();
                }

                // dispatch job to send masterclass publish notification to newly associated company users if any
                if (!empty($newlyAssociatedComps)) {
                    \dispatch(new SendCourseAddEditPushNotification($this, "course-published", $newlyAssociatedComps));
                }
            }
            $this->masterclassteam()->sync($payload['masterclass_company']);
            $this->masterclasscompany()->sync($companyIds);
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
        $courseId = $this->getKey();

        $this->clearMediaCollection('logo');
        $this->clearMediaCollection('trailer_background');
        $this->clearMediaCollection('trailer_audio');
        $this->clearMediaCollection('trailer_video');

        if ($this->delete()) {
            Badge::where("model_id", $courseId)
                ->where("model_name", "masterclass")
                ->delete();
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getModuleTableData($payload)
    {
        $courseData = $this;
        $list       = $this->getModuleRecordList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('status', function ($record) use ($courseData) {
                return view('admin.course.week.publishaction', compact('record', 'courseData'))->render();
            })
            ->addColumn('actions', function ($record) {
                return view('admin.course.week.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getModuleRecordList($payload)
    {
        $query = \DB::table('course_weeks')->leftJoin('course_lessions', 'course_weeks.id', '=', 'course_lessions.course_week_id')
            ->select('course_weeks.*', DB::raw("count(course_lessions.course_week_id) as totalLessions"))
            ->where('course_weeks.course_id', $this->getKey())
            ->orderBy('course_weeks.id', 'ASC')
            ->groupBy('course_weeks.id');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('course_weeks.title', 'like', '%' . $payload['recordName'] . '%');
        }

        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeModuleEntity($payload)
    {

        $record = $this->courseWeeks()->create([
            'course_id'  => $this->getKey(),
            'title'      => $payload['title'],
            'is_default' => false,
        ]);

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function courseUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_course', 'course_id', 'user_id')
            ->withPivot('id', 'saved', 'liked', 'favourited', 'favourited_at', 'ratings', 'review', 'joined', 'joined_on', 'completed_on', 'completed', 'post_survey_completed', 'post_survey_completed_on', 'pre_survey_completed', 'pre_survey_completed_on', 'started_course')
            ->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->courseUserLogs()->wherePivot('liked', true)->count();
    }

    /**
     * @return integer
     */
    public function getTotalStudents(): int
    {
        $joinedStudents = $this->courseUserLogs()->wherePivot('joined', true)->count();
        $randomStudents = $this->random_students;
        $totalStudents  = (int) ($joinedStudents + $randomStudents);
        return $totalStudents ?? 0;
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

        if ($collection == 'trailer_background' && $xDeviceOs == config()->get('zevolifesettings.PORTAL')) {
            $collection = 'trailer_background_portal';
        }

        $media = $this->getFirstMedia($collection);

        if (($collection == 'trailer_background_portal' || $collection == 'trailer_background') && is_null($media) && empty($media)) {
            $collection = ($xDeviceOs == config()->get('zevolifesettings.PORTAL')) ? 'trailer_background' : 'trailer_background_portal';
            $media      = $this->getFirstMedia($collection);
        }

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'course', $collection);
        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorData(): array
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @param
     *
     * @return int
     */
    public function getTotalRatings()
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']   = $creator->getKey();
            $return['name'] = $creator->full_name;
        }

        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorDataWithAbout(): array
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']          = $creator->getKey();
            $return['name']        = $creator->full_name;
            $return['image']       = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
            $return['description'] = (!empty($creator->profile->about)) ? $creator->profile->about : "";
        }

        return $return;
    }

    /**
     * @param
     *
     * @return integer
     */
    public function getCourseCountByCreator()
    {
        return Course::where("creator_id", $this->creator_id)->where("status", true)->count();
    }

    /**
     * @param
     *
     * @return integer
     */
    public function courseAverageRatings()
    {
        $ratingsTotal = $this->courseUserLogs()->wherePivot('ratings', '>', 0)->selectRaw('count(user_id) as totalUser,sum(ratings) as ratings')->groupBy('course_id')->first();

        $ratings = 0;
        if (!empty($ratingsTotal)) {
            $ratings = round($ratingsTotal->ratings / $ratingsTotal->totalUser);
        }

        return $ratings;
    }

    /**
     * @param
     *
     * @return integer
     */
    public function courseTotalDurarion()
    {
        return $this->courseLessions()->selectRaw('SUM(TIME_TO_SEC(course_lessions.duration)) as totalDurarion')->where("course_lessions.status", true)->groupBy('course_id')->first();
    }

    /**
     * @param
     *
     * @return integer
     */
    public function courseRatingsCalculation()
    {
        return $this->courseUserLogs()->selectRaw('AVG(NULLIF(ratings ,0)) as Avgratings , count(NULLIF(ratings ,0)) as totalUserRatings , SUM(CASE WHEN ratings=5 THEN 1 ELSE 0 END) as five , SUM(CASE WHEN ratings=4 THEN 1 ELSE 0 END) as four , SUM(CASE WHEN ratings=3 THEN 1 ELSE 0 END) as three , SUM(CASE WHEN ratings=2 THEN 1 ELSE 0 END) as two , SUM(CASE WHEN ratings=1 THEN 1 ELSE 0 END) as one ')->groupBy('course_id')->first();
    }

    public function getTimeLineData($user)
    {
        $i            = 0;
        $userId       = $user->getKey();
        $timeLineData = array();

        $totalUnlockedLessionOfCourse = $user->unlockedCourseLessons()->wherePivot('course_id', $this->getKey())->count();
        $unlock                       = true;

        $userCourseLog = $user->courseLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $this->getKey())->whereNotNull('user_course.joined_on')->wherePivot('joined', true)->first();

        foreach ($this->courseWeeks as $week) {
            $lessonData = $week->courseLessions()
                ->leftJoin("user_lession", function ($join) use ($userId) {
                    $join->on('course_lessions.id', '=', 'user_lession.course_lession_id');
                    $join->where("user_lession.user_id", $userId);
                })
                ->leftJoin("unlocked_user_course_lessons", function ($join) use ($userId) {
                    $join->on('course_lessions.id', '=', 'unlocked_user_course_lessons.course_lession_id');
                    $join->where("unlocked_user_course_lessons.user_id", $userId);
                })
                ->select("course_lessions.*", DB::raw("IF(user_lession.status = 'completed','true','false') as completed"), DB::raw("IF(user_lession.status = 'started','true','false') as started"), DB::raw("CASE WHEN unlocked_user_course_lessons.user_id IS NOT NULL THEN 'false' ELSE 'true' END as isLocked"))
                ->where("course_lessions.is_default", false)
                ->where("course_lessions.status", true)
                ->orderBy("course_lessions.id", "ASC")
                ->get();

            if ($lessonData->count() > 0) {
                $timeLineData[$i]['week']        = $week->title;
                $timeLineData[$i]['lessonCount'] = $lessonData->count();

                $userCourseWeekLog = $user->courseWeekLogs()->wherePivot('course_week_id', $week->getKey())->first();

                foreach ($lessonData as $key1 => $lession) {
                    $lessonLocked = $lession->isLocked;

                    if (!empty($userCourseLog)) {
                        // perform if user joined the course
                        // check if this lession is unlocked or not

                        $userLessonLockLog = $user->unlockedCourseLessons()->where('user_id', $userId)->where('course_lession_id', $lession->id)->first();

                        if (($userCourseLog->pivot->status == 'completed' && !empty($userCourseLog->pivot->completed_on)) || (!empty($userCourseWeekLog) && $userCourseWeekLog->pivot->status == 'completed' && !empty($userCourseWeekLog->pivot->completed_at))) {
                            // user has completed the course so unlock all lesson which are not unlocked
                            if (empty($userLessonLockLog)) {
                                \DB::table("unlocked_user_course_lessons")->insert(['course_lession_id' => $lession->id, 'course_week_id' => $lession->course_week_id, 'user_id' => $userId, 'course_id' => $this->id]);

                                $lessonLocked = "false";
                            }
                        } elseif ($totalUnlockedLessionOfCourse == 0 && $unlock) {
                            // If no lession is unlocked after joining of course
                            if (empty($userLessonLockLog)) {
                                \DB::table("unlocked_user_course_lessons")->insert(['course_lession_id' => $lession->id, 'course_week_id' => $lession->course_week_id, 'user_id' => $userId, 'course_id' => $this->id]);

                                $lessonLocked = "false";

                                $unlock = false;
                            }
                        }
                    }

                    $timeLineData[$i]['lessons'][$key1]['lessonId']    = $lession->id;
                    $timeLineData[$i]['lessons'][$key1]['title']       = $lession->title;
                    $timeLineData[$i]['lessons'][$key1]['isLocked']    = ($lessonLocked == "true");
                    $timeLineData[$i]['lessons'][$key1]['isCompleted'] = ($lession->completed == "true");
                    $timeLineData[$i]['lessons'][$key1]['isRunning']   = ($lession->started == "true");
                }
                $i++;
            }
        }

        return $timeLineData;
    }

    public function publishCourse($payload)
    {
        $data = [
            'published' => true,
            'message'   => '',
        ];

        if ($payload['action'] == "publish") {
            if ($this->status) {
                $data['published'] = false;
                $data['message']   = "Masterclass has already published.";
            } else {
                $lesson_count = $this->courseLessions()->count();
                if ($lesson_count < 1) {
                    $data['published'] = false;
                    $data['message']   = "Please add at least one lesson under the Masterclass!";
                    return $data;
                } else {
                    $survey_count = $this->courseSurvey();
                    if ($survey_count->count() < 2) {
                        $data['published'] = false;
                        $data['message']   = "Please add survey under the Masterclass!";
                    } elseif ($survey_count->count() >= 2) {
                        $surveys = $survey_count->get();
                        foreach ($surveys as $survey) {
                            if ($survey->surveyQuestions()->count() <= 0) {
                                $data['published'] = false;
                                $data['message']   = "Please add question under the surveys!";
                                break;
                            }
                        }
                    }
                }

                if ($data['published']) {
                    $updated = $this->update(['status' => 1]);
                    if ($updated) {
                        $this->courseLessions()->update(['status' => 1]);
                        $this->courseSurvey()->update(['status' => 1]);
                        $data['message'] = "Masterclass has been published successfully.";

                        // dispatch job to send masterclass publish notification
                        \dispatch(new SendCourseAddEditPushNotification($this, "course-published"));
                    } else {
                        $data['published'] = false;
                        $data['message']   = "Something went wrong while publishing a masterclass!";
                    }
                }
            }
        } elseif ($payload['action'] == "unpublish") {
            if (!$this->status) {
                $data['published'] = false;
                $data['message']   = "Masterclass has already unpublished.";
            } else {
                $enrolleduserscount = $this->courseUserLogs()->count();
                if ($enrolleduserscount > 0) {
                    $data['published'] = false;
                    $data['message']   = "Masterclass can't be unpublished as users are enrolled it.";
                } elseif (empty($enrolleduserscount)) {
                    $updated = $this->update(['status' => 0]);
                    if ($updated) {
                        $this->courseLessions()->update(['status' => 0]);
                        $this->courseSurvey()->update(['status' => 0]);
                        $data['message'] = "Masterclass has been unpublished successfully.";
                    } else {
                        $data['published'] = false;
                        $data['message']   = "Something went wrong while unpublishing a masterclass!";
                    }
                }
            }
        }

        return $data;
    }

    public function totalCoursePublishLessionCount()
    {
        $lessionCount = self::join("course_weeks", "course_weeks.course_id", "=", "courses.id")
            ->leftJoin("course_lessions", "course_lessions.course_week_id", "=", "course_weeks.id")
            ->where("courses.id", $this->getKey())
            ->where("course_weeks.status", 1)
            ->where("course_lessions.is_default", false)
            ->count('course_lessions.id');

        return (int) $lessionCount;
    }

    /**
     * @return HasMany
     */
    public function courseSurvey(): HasMany
    {
        return $this->hasMany('App\Models\CourseSurvey');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getLessionTableData($payload)
    {
        $list = $this->getLessionRecordList($payload);

        return DataTables::of($list)
            ->addColumn('status', function ($record) {
                return view('admin.course.lession.publishaction', compact('record'))->render();
            })
            ->addColumn('actions', function ($record) {
                return view('admin.course.lession.listaction', compact('record'))->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getLessionRecordList($payload)
    {
        $query = CourseLession::select(
            'course_lessions.*',
            \DB::raw("TIME_FORMAT(course_lessions.duration,'%H:%i') as duration")
        )
            ->with(['course' => function ($query) {
                $query->select('courses.id', 'courses.status AS course_status');
            }])
            ->where('course_lessions.course_id', $this->getKey())
            ->orderBy('course_lessions.order_priority', 'ASC')
            ->groupBy('course_lessions.id');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('course_lessions.title', 'like', '%' . $payload['recordName'] . '%');
        }

        return $query->get();
    }

    /**
     * @param
     *
     * @return array
     */
    public function getTrailerMediaData()
    {
        $return     = [];
        $collection = "";
        if (!empty($this->trailer_type) && $this->trailer_type == 1) {
            $return['type'] = "AUDIO";
            $collection     = "trailer_audio";
        } elseif (!empty($this->trailer_type) && $this->trailer_type == 2) {
            $return['type'] = "VIDEO";
            $collection     = "trailer_video";
        } elseif (!empty($this->trailer_type) && $this->trailer_type == 3) {
            $return['type'] = "YOUTUBE";
            $collection     = "track";
        } else {
            $return['type'] = "VIMEO";
            $collection     = "track";
        }

        $media = $this->getFirstMedia($collection);

        $xDeviceOs    = strtolower(request()->header('X-Device-Os', ""));
        $width        = 1280;
        $height       = 640;
        $playerWidth  = 640;
        $playerHeight = 1280;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $width        = 600;
            $height       = 400;
            $playerWidth  = 600;
            $playerHeight = 400;
        }

        if (!empty($this->trailer_type) && $this->trailer_type == 1) {
            $return['backgroundImage']        = $this->getMediaData('trailer_background', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePreview'] = $this->getMediaData('trailer_background', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePlayer']  = $this->getMediaData('trailer_background', ['w' => $playerWidth, 'h' => $playerHeight, 'zc' => 3]);
            $return['mediaURL']               = \url($media->getUrl());
        } elseif (!empty($this->trailer_type) && $this->trailer_type == 2) {
            $return['backgroundImage']        = $this->getMediaData('trailer_video', ['w' => $width, 'h' => $height, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['backgroundImagePreview'] = $this->getMediaData('trailer_video', ['w' => $width, 'h' => $height, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['backgroundImagePlayer']  = $this->getMediaData('trailer_video', ['w' => $playerWidth, 'h' => $playerHeight, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['mediaURL']               = \url($media->getUrl());
        } elseif (!empty($this->trailer_type) && $this->trailer_type == 3) {
            $return['backgroundImage']        = $this->getCourseImageData('track', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePreview'] = $this->getCourseImageData('track', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePlayer']  = $this->getCourseImageData('track', ['w' => $playerWidth, 'h' => $playerHeight, 'zc' => 3]);

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $return['mediaURL'] = config('zevolifesettings.youtubeembedurl') . $media->getCustomProperty('ytid');
            } else {
                $return['mediaURL'] = config('zevolifesettings.youtubeappurl') . $media->getCustomProperty('ytid');
            }
        } elseif (!empty($this->trailer_type) && $this->trailer_type == 4) {
            $return['backgroundImage']        = $this->getCourseImageData('track_vimeo', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePreview'] = $this->getCourseImageData('track_vimeo', ['w' => $width, 'h' => $height, 'zc' => 3]);
            $return['backgroundImagePlayer']  = $this->getCourseImageData('track_vimeo', ['w' => $playerWidth, 'h' => $playerHeight, 'zc' => 3]);

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $return['mediaURL'] = config('zevolifesettings.vimeoembedurl') . $media->getCustomProperty('vmid');
            } else {
                $return['mediaURL'] = config('zevolifesettings.vimeoappurl') . $media->getCustomProperty('vmid');
            }
        }

        return $return;
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeLessionEntity($payload)
    {
        $lesson = $this->courseLessions()->create([
            'course_id'      => $this->id,
            'title'          => $payload['title'],
            'type'           => (int) $payload['lesson_type'],
            'description'    => (!empty($payload['description']) ? $payload['description'] : null),
            'duration'       => convertToHoursMins($payload['duration'], false, '%02d:%02d:00'),
            'auto_progress'  => (isset($payload['auto_progress']) && $payload),
            'order_priority' => ($this->courseLessions()->max('order_priority') + 1),
            'status'         => false,
        ]);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $lesson->id . '_' . \time();
            $lesson
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background']) && !empty($payload['audio_background'])) {
            $name = $lesson->id . '_' . \time();
            $lesson
                ->clearMediaCollection('audio_background')
                ->addMediaFromRequest('audio_background')
                ->usingName($payload['audio_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background']->extension())
                ->toMediaCollection('audio_background', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background_portal']) && !empty($payload['audio_background_portal'])) {
            $name = $lesson->id . '_' . \time();
            $lesson
                ->clearMediaCollection('audio_background_portal')
                ->addMediaFromRequest('audio_background_portal')
                ->usingName($payload['audio_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background_portal']->extension())
                ->toMediaCollection('audio_background_portal', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio']) && !empty($payload['audio'])) {
            $name = $lesson->id . '_' . \time();
            $lesson
                ->clearMediaCollection('audio')
                ->addMediaFromRequest('audio')
                ->usingName($payload['audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio']->getClientOriginalExtension())
                ->toMediaCollection('audio', config('medialibrary.disk_name'));
        }

        if (isset($payload['video']) && !empty($payload['video'])) {
            $name = $lesson->id . '_' . \time();
            $lesson
                ->clearMediaCollection('video')
                ->addMediaFromRequest('video')
                ->usingName($payload['video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['video']->extension())
                ->toMediaCollection('video', config('medialibrary.disk_name'));
        }

        if (isset($payload['youtube']) && !empty($payload['youtube'])) {
            $videoId          = getYoutubeVideoId($payload['youtube']);
            $name             = $lesson->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['youtube'], 'ytid' => $videoId];
            $lesson
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $lesson->getAllowedMediaMimeTypes('image')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('youtube', config('medialibrary.disk_name'));
        }

        if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $lesson->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['vimeo'], 'vmid' => $videoId];
            $lesson
                ->clearMediaCollection('vimeo')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('vimeo', config('medialibrary.disk_name'));
        }

        if ($lesson) {
            return true;
        }

        return false;
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getSurveyTableData($payload)
    {
        $list = $this->getSurveyRecordList($payload);

        return DataTables::of($list)
            ->addColumn('type', function ($record) {
                return ucfirst($record->type);
            })
            ->addColumn('status', function ($record) {
                return view('admin.course.survey.publishaction', compact('record'))->render();
            })
            ->addColumn('actions', function ($record) {
                return view('admin.course.survey.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getSurveyRecordList($payload)
    {
        return $this->courseSurvey()
            ->with(["surveyCourse" => function ($query) {
                $query->select('courses.id', 'courses.status AS courses_status');
            }]);
    }

    /**
     * To add an default surveys(Pre/Post) of course
     *
     * @return boolean
     */
    public function addDefaultSurveys()
    {
        $created     = false;
        $survey_type = config('zevolifesettings.masterclass_survey_type');
        foreach ($survey_type as $type => $value) {
            $created = $this->courseSurvey()->create([
                'course_id' => $this->id,
                'type'      => $type,
                'title'     => "{$value} Survey",
                'status'    => 0,
            ]);
        }

        return $created;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteSurveys()
    {
        $deleted = false;
        if ($this->courseSurvey()->delete()) {
            $deleted = true;
        }
        return $deleted;
    }

    public function reorderingLesson($positions)
    {
        $updated = false;
        foreach ($positions as $key => $position) {
            $updated = $this
                ->courseLessions()
                ->where([
                    'course_lessions.id'             => (int) $key,
                    'course_lessions.order_priority' => (int) $position['oldPosition'],
                ])
                ->update([
                    'course_lessions.order_priority' => (int) $position['newPosition'],
                ]);
        }
        return $updated;
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCourseImageData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'course_lession', $collection);
        return $return;
    }
}
