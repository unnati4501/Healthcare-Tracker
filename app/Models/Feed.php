<?php

namespace App\Models;

use App\Models\Company;
use App\Models\TeamLocation;
use App\Models\User;
use App\Observers\FeedObserver;
use App\Traits\HasRewardPointsTrait;
use Carbon\Carbon;
use Carbon\CategoryTags;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Feed extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'feeds';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by',
        'creator_id',
        'company_id',
        'category_id',
        'sub_category_id',
        'expertise_level',
        'type',
        'is_stick',
        'is_cloned',
        'cloned_from',
        'title',
        'subtitle',
        'description',
        'start_date',
        'end_date',
        'timezone',
        'deep_link_uri',
        'view_count',
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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(FeedObserver::class);
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
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function feedcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'feed_company', 'feed_id', 'company_id');
    }

    /**
     * One-to-Many relations with feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function feedteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'feed_team', 'feed_id', 'team_id');
    }

    /**
     * @return HasMany
     */
    public function expertiseLevels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Category', 'feed_expertise_level', 'feed_id', 'category_id')->withPivot('expertise_level')->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function trackcoach(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    /**
     * @return BelongsTo
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function feedGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'feed_tag', 'feed_id', 'goal_id');
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
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 80, 'h' => 160]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('featured_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('featured_image');
        }

        if ($media->mime_type == 'image/gif') {
            return $this->getFirstMediaUrl('featured_image');
        }
        return getThumbURL($params, 'feed', 'featured_image');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAudioBackgroundAttribute()
    {
        return $this->getAudioBackground(['w' => 1280, 'h' => 640, 'zc' => 1]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAudioBackground(array $params): string
    {
        $user = auth()->user();
        $role = getUserRole($user);

        if ($role->group == 'zevo' || $role->group == 'company') {
            $media = $this->getFirstMedia('audio_background');
            if (empty($media)) {
                $media = $this->getFirstMedia('audio_background_portal');
                if (!is_null($media) && $media->count() > 0) {
                    $params['src'] = $this->getFirstMediaUrl('audio_background_portal');
                }
                return getThumbURL($params, 'feed', 'audio_background_portal');
            }
            if (!is_null($media) && $media->count() > 0) {
                $params['src'] = $this->getFirstMediaUrl('audio_background');
            }
            return getThumbURL($params, 'feed', 'audio_background');
        } else {
            $media = $this->getFirstMedia('audio_background_portal');
            if (empty($media)) {
                $media = $this->getFirstMedia('audio_background');
                if (!is_null($media) && $media->count() > 0) {
                    $params['src'] = $this->getFirstMediaUrl('audio_background');
                }
                return getThumbURL($params, 'feed', 'audio_background');
            }
            if (!is_null($media) && $media->count() > 0) {
                $params['src'] = $this->getFirstMediaUrl('audio_background_portal');
            }
            return getThumbURL($params, 'feed', 'audio_background_portal');
        }
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getVideoBackgroundAttribute()
    {
        return $this->getVideoBackground(['w' => 1280, 'h' => 640, 'zc' => 1, 'conversion' => 'th_lg']);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getVideoBackground(array $params): string
    {
        $media = $this->getFirstMedia('video');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('video', ($params['conversion'] ?? ''));
        }
        return getThumbURL($params, 'feed', 'audio_background');
    }

    /**
     * Accessor for Link attribute.
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaCoverAttribute(): string
    {
        return $this->getFirstMediaUrl($this->getAttributeValue('type'));
    }

    /**
     * Media library conversions.
     * Create cover for uploaded video.
     *
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
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);
        $user = auth()->user();

        $role              = getUserRole();
        $allow_view_access = access()->allow('view-story');
        $childcompany      = [];
        $companyId         = "";
        if ($role->group != 'zevo') {
            $companyId    = $user->company->first()->id;
            $childcompany = company::where('parent_id', $companyId)->pluck('id')->toArray();
        }

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('start_date', function ($record) {
                return $record->start_date;
            })
            ->addColumn('end_date', function ($record) {
                return $record->end_date;
            })
            ->addColumn('title', function ($record) use ($allow_view_access) {
                if ($allow_view_access) {
                    return "<a href='" . route('admin.feeds.details', $record->id) . "' title='{$record->title}'>{$record->title}</a>";
                } else {
                    return $record->title;
                }
            })
            ->addColumn('companyName', function ($record) {
                return !empty($record->companyName) ? $record->companyName : 'Zevo';
            })
            ->addColumn('companiesName', function ($record) use ($role, $user) {
                $companyData = $user->company()->get()->first();
                if (($role->group == 'zevo') || ($role->group == 'reseller' && !empty($companyData) && $companyData->is_reseller)) {
                    if ($role->group == 'zevo') {
                        $companies = $record->feedcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                    } elseif ($role->group == 'reseller') {
                        $companies = $record->feedcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' ELSE 'Child' END ) AS group_type"))
                            ->where(function ($query) use ($companyData) {
                                $query->where('companies.id', $companyData->id)->orwhere('companies.parent_id', $companyData->id);
                            })
                            ->groupBy('name')
                            ->distinct()->get()->toArray();
                    }
                    $totalCompanies   = sizeof($companies);
                    if ($totalCompanies > 0) {
                        return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                    }
                }
                return "";
            })
            ->addColumn('logo', function ($record) {
                return '<div class="table-img table-img-l"><img src="' . $record->logo . '" alt=""></div>';
            })
            ->addColumn('totalLikes', function ($record) {
                return !empty($record->totalLikes) ? numberFormatShort($record->totalLikes) : 0;
            })
            ->addColumn('is_stick', function ($record) use ($role, $companyId, $childcompany) {
                return view('admin.feed.sticky', compact('record', 'role', 'companyId', 'childcompany'))->render();
            })
            ->addColumn('view_count', function ($record) {
                return $record->view_count;
            })
            ->addColumn('actions', function ($record) use ($user, $role, $childcompany) {
                $isShowButton   = (in_array($record->company_id, $childcompany)) ? true : false;
                $creatorUser    = User::find($record->created_by);
                $storyCreatedBy = getUserRole($creatorUser);
                return view('admin.feed.listaction', compact('record', 'user', 'role', 'isShowButton', 'storyCreatedBy'))->render();
            })
            ->rawColumns(['title', 'companiesName', 'logo', 'actions', 'is_stick'])
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
        $user     = auth()->user();
        $role     = getUserRole();
        $company  = $user->company->first();
        $query    = $this
            ->select(
                'feeds.*',
                'companies.name AS companyName',
                'feeds.company_id AS company_id',
                DB::raw('SUM(feed_user.liked = 1) AS totalLikes'),
                "sub_categories.name as subcategory",
                DB::raw("CONCAT(first_name, ' ', last_name) AS health_coach"),
                "companies.parent_id",
                DB::raw('IFNULL(sum(feed_user.view_count),0) AS view_count'),
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag")
            );

        if ($role->group == 'zevo') {
            $query->addSelect(DB::raw("CASE
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 0
                            ELSE 1
                           END AS is_stick_count"));
        } elseif ($role->group == 'company') {
            $query->selectRaw("CASE
                WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                ELSE 2
                END AS is_stick_count",[
                    $company->id
                ]);
        } elseif ($role->group == 'reseller') {
            if ($company->parent_id != null) {
                $query->selectRaw("CASE
                    WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                    WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                    WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                    ELSE 3
                    END AS is_stick_count",[
                        $company->id
                    ]);
            } else {
                $query->selectRaw("CASE
                    WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                    WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                    ELSE 2
                    END AS is_stick_count",[
                        $company->id
                    ]);
            }
        }

        $query->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
            ->leftJoin('users', 'users.id', '=', 'feeds.creator_id')
            ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
            ->leftJoin('sub_categories', 'feeds.sub_category_id', '=', 'sub_categories.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'feeds.tag_id')
            ->groupBy('feeds.id');

        if ($role->group == 'zevo') {
            if (in_array('feedCompany', array_keys($payload)) && !empty($payload['feedCompany'])) {
                $query->where('feeds.company_id', ($payload['feedCompany'] == 'zevo' ? null : $payload['feedCompany']));
            }
            $query->where('is_cloned', false);
        } elseif ($role->group == 'company') {
            $query->whereRaw('(FIND_IN_SET(?, (SELECT GROUP_CONCAT(`feed_company`.`company_id`) from `feed_company` where `feed_id` = `feeds`.`id`)))', [$company->id]);
            $query->whereRaw('`feeds`.`id` NOT IN (SELECT `feeds`.`cloned_from` from `feeds`)');
        } elseif ($role->group == 'reseller') {
            if ($company->parent_id == null) {
                if (in_array('feedCompany', array_keys($payload)) && !empty($payload['feedCompany'])) {
                    $query->where('feeds.company_id', ($payload['feedCompany'] == 'zevo' ? null : $payload['feedCompany']));
                } else {
                    $subcompany = company::where('parent_id', $company->id)->pluck('id')->toArray();

                    if ($subcompany) {
                        array_push($subcompany, $company->id);
                        $query->whereRaw('`feeds`.`id` IN (SELECT `feed_company`.`feed_id` as feed_id from `feed_company` where company_id IN ?)', [implode(',', $subcompany)]);
                    } else {
                        $query->whereRaw('(FIND_IN_SET(?, (SELECT GROUP_CONCAT(`feed_company`.`company_id`) from `feed_company` where `feed_id` = `feeds`.`id`)))', [$company->id]);
                    }
                }
            } else {
                $query->whereRaw('(FIND_IN_SET(?, (SELECT GROUP_CONCAT(`feed_company`.`company_id`) from `feed_company` where `feed_id` = `feeds`.`id`)))', [$company->id]);
            }
            $query->where('is_cloned', false);
        }

        if (in_array('feedName', array_keys($payload)) && !empty($payload['feedName'])) {
            $query->where('feeds.title', 'like', '%' . $payload['feedName'] . '%');
        }

        if (in_array('recordCategory', array_keys($payload)) && !empty($payload['recordCategory'])) {
            $query->where('feeds.sub_category_id', $payload['recordCategory']);
        }

        if (in_array('type', array_keys($payload)) && !empty($payload['type'])) {
            $query->where('feeds.type', '=', $payload['type']);
        }

        if (in_array('sheduled_content', array_keys($payload)) && !empty($payload['sheduled_content']) && $payload['sheduled_content'] == "scheduled") {
            $query = $query
                ->where(function (Builder $q) {
                    return $q->where(DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))
                        ->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $q) {
                    return $q->where(DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))
                        ->orWhere('feeds.end_date', null);
                });
        }

        if (in_array('tag', array_keys($payload)) && !empty($payload['tag'])) {
            if (strtolower($payload['tag']) == 'na') {
                $query->whereNull('feeds.tag_id');
            } else {
                $query->where('feeds.tag_id', $payload['tag']);
            }
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            switch ($column) {
                case 'companyName':
                    $columnName = 'feeds.company_id';
                    break;
                case 'companiesName':
                    $columnName = 'feeds.company_id';
                    break;
                default:
                    $columnName = $column;
                    break;
            }
            $query->orderBy($columnName, $order);
        } else {
            $query->orderBy("is_stick_count", 'ASC');
            $query->orderBy("updated_at", 'DESC');
            $query->orderByDesc('feeds.id');
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

    public function storeEntity($payload)
    {
        $user      = auth()->user();
        $role      = getUserRole($user);
        $company   = $user->company->first();
        $companyId = (($role->group == 'company' || $role->group == 'reseller') ? $company->id : null);

        if (!empty($payload['start_date'])) {
            if (!empty($payload['timezone'])) {
                $start_date = Carbon::parse($payload['start_date'], $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse($payload['start_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($payload['timezone'])) {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"), $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        if (!empty($payload['end_date'])) {
            if (!empty($payload['timezone'])) {
                $end_date = Carbon::parse($payload['end_date'], $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse($payload['end_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($payload['timezone'])) {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"), $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        $data = [
            'created_by'      => $user->id,
            'creator_id'      => $payload['health_coach'],
            'type'            => $payload['feed_type'],
            'title'           => $payload['name'],
            'subtitle'        => $payload['subtitle'],
            'description'     => (!empty($payload['description']) ? $payload['description'] : null),
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'company_id'      => $companyId,
            'category_id'     => 2,
            'sub_category_id' => $payload['sub_category'],
            'timezone'        => (!empty($payload['timezone']) ? $payload['timezone'] : 'UTC'),
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $feed = self::create($data);

        if (isset($payload['featured_image']) && !empty($payload['featured_image'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('featured_image')
                ->addMediaFromRequest('featured_image')
                ->usingName($payload['featured_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['featured_image']->extension())
                ->toMediaCollection('featured_image', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background']) && !empty($payload['audio_background'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('audio_background')
                ->addMediaFromRequest('audio_background')
                ->usingName($payload['audio_background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background']->extension())
                ->toMediaCollection('audio_background', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio_background_portal']) && !empty($payload['audio_background_portal'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('audio_background_portal')
                ->addMediaFromRequest('audio_background_portal')
                ->usingName($payload['audio_background_portal']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio_background_portal']->extension())
                ->toMediaCollection('audio_background_portal', config('medialibrary.disk_name'));
        }

        if (isset($payload['header_image']) && !empty($payload['header_image'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (isset($payload['audio']) && !empty($payload['audio'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('audio')
                ->addMediaFromRequest('audio')
                ->usingName($payload['audio']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['audio']->getClientOriginalExtension())
                ->toMediaCollection('audio', config('medialibrary.disk_name'));
        }

        if (isset($payload['video']) && !empty($payload['video'])) {
            $name = $feed->id . '_' . \time();
            $feed
                ->clearMediaCollection('video')
                ->addMediaFromRequest('video')
                ->usingName($payload['video']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['video']->extension())
                ->toMediaCollection('video', config('medialibrary.disk_name'));
        }

        if (isset($payload['youtube']) && !empty($payload['youtube'])) {
            $videoId = getYoutubeVideoId($payload['youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $feed->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['youtube'], 'ytid' => $videoId];
            $feed
                ->clearMediaCollection('youtube')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $feed->getAllowedMediaMimeTypes('image')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['youtube'])
                ->usingFileName($name . '.jpg')
                ->toMediaCollection('youtube', config('medialibrary.disk_name'));
        }

        if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $feed->id . '_' . \time();
            $customProperties = ['title' => $payload['name'], 'link' => $payload['vimeo'], 'vmid' => $videoId];
            $feed
                ->clearMediaCollection('vimeo')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $feed->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('vimeo', config('medialibrary.disk_name'));
        }

        $feed_companyInput = [];
        if ($role->group == 'zevo') {
            // feed_company now convert to team ids
            $companyIds = TeamLocation::whereIn('team_id', $payload['feed_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $feed_companyInput[] = [
                    'feed_id'    => $feed->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
            $feed->feedteam()->sync($payload['feed_company']);
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $feed_companyInput = [];
            // feed_company now convert to team ids
            $companyIds = TeamLocation::whereIn('team_id', $payload['feed_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $feed_companyInput[] = [
                    'feed_id'    => $feed->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
            $feed->feedteam()->sync($payload['feed_company']);
        } elseif ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            $teamIds             = TeamLocation::where('company_id', $companyId)->select('team_id')->distinct()->get()->pluck('team_id')->toArray();
            $feed_companyInput[] = [
                'feed_id'    => $feed->id,
                'company_id' => $companyId,
                'created_at' => Carbon::now(),
            ];
            $feed->feedteam()->sync($teamIds);
        }

        $feed->feedcompany()->sync($feed_companyInput);

        if (!empty($payload['goal_tag'])) {
            $feed->feedGoalTag()->sync($payload['goal_tag']);
        }

        return $feed ? true : false;
    }

    /**
     * get feed edit data.
     *
     * @param  $id
     * @return array
     */

    public function feedEditData()
    {
        $data     = array();
        $role     = getUserRole();

        $data['company']              = Company::pluck('name', 'id')->toArray();
        $data['subcategories']        = SubCategory::where('status', 1)->where("category_id", 2)->pluck('name', 'id')->toArray();
        $data['id']                   = $this->id;
        $data['feedData']             = $this;
        $data['feedData']->start_date = Carbon::parse($data['feedData']->start_date)->setTimezone($this->timezone)->toDateTimeString();
        $data['feedData']->end_date   = Carbon::parse($data['feedData']->end_date)->setTimezone($this->timezone)->toDateTimeString();
        $data['isCloned']             = $data['feedData']->is_cloned;
   
        $feed_companys = array();

        if (!empty($this->feedteam)) {
            $feed_companys = $this->feedteam->pluck('id')->toArray();
        }

        $data['feed_companys'] = $feed_companys;

        $data['goalTags'] = Goal::pluck('title', 'id')->toArray();
        $goal_tags        = array();
        if (!empty($this->feedGoalTag)) {
            $goal_tags = $this->feedGoalTag->pluck('id')->toArray();
        }
        $data['goal_tags'] = $goal_tags;

        if ($role->group == 'zevo') {
            if ($this->company_id != null) {
                $trackcoach = $this->trackcoach;

                $data['healthcoach'] = [
                    $trackcoach->id => $trackcoach->full_name,
                ];
            } else {
                $healthcoach = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                    ->where(["is_coach" => 1, 'is_blocked' => 0])
                    ->pluck('name', 'id')
                    ->toArray();

                $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $healthcoach);
            }
        } elseif ($role->group == 'company') {
            $companyData = Auth::user()->company->first();

            $companyUsers = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                ->join('user_team', function ($join) use ($companyData) {
                    $join->on('user_team.user_id', '=', 'users.id')
                        ->where('user_team.company_id', $companyData->id);
                })
                ->where(['users.is_blocked' => 0])
                ->pluck('users.name', 'users.id')
                ->toArray();

            $healthcoach = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                ->where(["is_coach" => 1, 'is_blocked' => 0])
                ->pluck('name', 'id')
                ->toArray();

            $data['healthcoach'] = array_replace($healthcoach, $companyUsers);
        } elseif ($role->group == 'reseller') {
            $data['healthcoach'] = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                ->join('user_team', function ($join) {
                    $join->on('user_team.user_id', '=', 'users.id')
                        ->where('user_team.company_id', $this->company_id);
                })
                ->where(['users.is_blocked' => 0])
                ->pluck('users.name', 'users.id')
                ->toArray();
        }

        return $data;
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $user      = auth()->user();
        $role      = getUserRole($user);
        $company   = $user->company->first();

        if (!empty($payload['start_date'])) {
            if (!empty($payload['timezone'])) {
                $start_date = Carbon::parse($payload['start_date'], $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse($payload['start_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($payload['timezone'])) {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"), $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        if (!empty($payload['end_date'])) {
            if (!empty($payload['timezone'])) {
                $end_date = Carbon::parse($payload['end_date'], $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse($payload['end_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($payload['timezone'])) {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"), $payload['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        if ($role->group == 'company' && (isset($payload['isCloned']) && $payload['isCloned'] == 1)) {
            $data = [
                'start_date'      => $start_date,
                'end_date'        => $end_date,
            ];
            $updated = $this->update($data);
        }else{
            $data = [
                'creator_id'      => $payload['health_coach'],
                'title'           => $payload['name'],
                'subtitle'        => $payload['subtitle'],
                'description'     => (!empty($payload['description']) ? $payload['description'] : null),
                'start_date'      => $start_date,
                'end_date'        => $end_date,
                'category_id'     => 2,
                'sub_category_id' => $payload['sub_category'],
                'timezone'        => (!empty($payload['timezone']) ? $payload['timezone'] : 'UTC'),
            ];
    
            if ($role->group == 'zevo') {
                $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
            }
    
            $updated = $this->update($data);
    
            if (isset($payload['featured_image']) && !empty($payload['featured_image'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('featured_image')
                    ->addMediaFromRequest('featured_image')
                    ->usingName($payload['featured_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['featured_image']->extension())
                    ->toMediaCollection('featured_image', config('medialibrary.disk_name'));
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
    
            if (isset($payload['header_image']) && !empty($payload['header_image'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('header_image')
                    ->addMediaFromRequest('header_image')
                    ->usingName($payload['header_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['header_image']->extension())
                    ->toMediaCollection('header_image', config('medialibrary.disk_name'));
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
                $videoId = getYoutubeVideoId($payload['youtube']);
                if (empty($videoId)) {
                    throw ValidationException::withMessages([
                        'error' => trans('labels.common_title.something_wrong_try_again'),
                    ]);
                }
                $name             = $this->id . '_' . \time();
                $customProperties = ['title' => $payload['name'], 'link' => $payload['youtube'], 'ytid' => $videoId];
                $this
                    ->clearMediaCollection('youtube')
                    ->addMediaFromUrl(
                        getYoutubeVideoCover($videoId, 'hqdefault'),
                        $this->getAllowedMediaMimeTypes('image')
                    )
                    ->withCustomProperties($customProperties)
                    ->usingName($payload['youtube'])
                    ->usingFileName($name . '.jpg')
                    ->toMediaCollection('youtube', config('medialibrary.disk_name'));
            }
    
            if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
                $videoId = getIdFromVimeoURL($payload['vimeo']);
                if (empty($videoId)) {
                    throw ValidationException::withMessages([
                        'error' => trans('labels.common_title.something_wrong_try_again'),
                    ]);
                }
                $name             = $this->id . '_' . \time();
                $customProperties = ['title' => $payload['name'], 'link' => $payload['vimeo'], 'vmid' => $videoId];
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
    
            $feed_companyInput = [];
            if ($role->group == 'zevo') {
                $companyIds = TeamLocation::whereIn('team_id', $payload['feed_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
                foreach ($companyIds as $value) {
                    $feed_companyInput[$value] = [
                        'feed_id'    => $this->id,
                        'company_id' => $value,
                        'created_at' => Carbon::now(),
                    ];
                }
                $this->feedteam()->sync($payload['feed_company']);
            } elseif ($role->group == 'reseller' && $company->parent_id == null) {
                $companyIds = TeamLocation::whereIn('team_id', $payload['feed_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
                foreach ($companyIds as $key => $value) {
                    $feed_companyInput[] = [
                        'feed_id'    => $this->id,
                        'company_id' => $value,
                        'created_at' => Carbon::now(),
                    ];
                }
                $this->feedteam()->sync($payload['feed_company']);
            }

            // Unset the stick flag if company is removed by ZSA for stikcy story
            if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)){
                $existingCompanies = $this->feedcompany()->select('company_id')->get()->pluck('company_id')->toArray();
                $removedCompanies = array_diff($existingCompanies, $companyIds);
                if (!empty($removedCompanies)) {
                    $feeds = $this->select('id')->whereIn('company_id', $removedCompanies)->where('id', $this->id)->get()->pluck('id')->toArray();
                    if(!empty($feeds)){
                        $this->update(['is_stick' => false]);
                    }
                }
            }

            if ($feed_companyInput) {
                $this->feedcompany()->sync($feed_companyInput);
            }
    
            $this->feedGoalTag()->detach();
            if (!empty($payload['goal_tag'])) {
                $this->feedGoalTag()->sync($payload['goal_tag']);
            }
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
        $this->clearMediaCollection('featured_image');
        $this->clearMediaCollection('audio');
        $this->clearMediaCollection('audio_background');
        $this->clearMediaCollection('audio_background_portal');
        $this->clearMediaCollection('video');
        $this->clearMediaCollection('youtube');

        if ($this->delete()) {
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

        if (empty($media) && $collection == 'header_image') {
            $return['url'] = config('zevolifesettings.story_content_fallback_image_url');
        } else {
            if ($media->mime_type == 'image/gif') {
                $return['url'] = $this->getFirstMediaUrl($collection);
            } else {
                $return['url'] = getThumbURL($param, 'feed', $collection);
            }
        }

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
     * @return BelongsToMany
     */
    public function feedUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'feed_user', 'feed_id', 'user_id')->withPivot('saved', 'liked', 'favourited', 'favourited_at', 'view_count')->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->feedUserLogs()->wherePivot('liked', true)->count();
    }

    /**
     * @param $action (stick/unstick)
     *
     * @return boolean
     */
    public function stickUnstick($action)
    {
        return $this->update(['is_stick' => (($action == 'stick') ? 1 : 0)]);
    }

    /**
     * @param
     *
     * @return array
     */
    public function getMoreStories()
    {
        $user     = auth()->user();
        $team     = $user->teams()->first();
        $company  = $user->company()->first();
        $role     = getUserRole($user);
        $records  = $this
            ->select(
                'feeds.id',
                'feeds.title',
                'feeds.type',
                 DB::raw("'new' as tag"),
            );
        if ($role->group == 'company' && is_null($company->parent_id) && !$company->is_reseller) {
            $records->selectRaw("CASE
                WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                ELSE 2
                END AS is_stick_count",[
                    $company->id
                ]);
        } else {
            if ($company->parent_id == null && $company->is_reseller) {
                $records->selectRaw("CASE
                    WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                    WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                    ELSE 2
                    END AS is_stick_count",[
                        $company->id
                    ]);
            } elseif (!is_null($company->parent_id)) {
                $records->selectRaw("CASE
                    WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                    WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                    WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                    ELSE 3
                    END AS is_stick_count",[
                        $company->id
                    ]);
            } else {
                $records->selectRaw("CASE
                    WHEN feeds.company_id = ? AND feeds.is_stick != 0 then 0
                    WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                    ELSE 2
                    END AS is_stick_count",[
                        $company->id
                    ]);
            }
        }
        $records->join('feed_team', function ($join) use ($team) {
            $join->on('feeds.id', '=', 'feed_team.feed_id')
                ->where('feed_team.team_id', '=', $team->getKey());
        });
        $records->leftJoin('feed_user', 'feed_user.feed_id', '=', 'feeds.id')
            ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
            })
            ->where(function (Builder $query) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
            })
            ->where(function (Builder $query) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
            });
        $records = $records->where('feeds.creator_id', $this->creator_id)
            ->whereNotIn('feeds.id', [$this->id])
            ->orderBy('is_stick_count', 'ASC')
            ->orderBy('feeds.updated_at', 'DESC')
            ->orderBy('feeds.id', 'DESC')
            ->groupBy('feeds.id')
            ->limit(5)
            ->get();

        return $records;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getFeedMediaData()
    {
        $return     = [];
        $collection = "";
        $feedTypes  = [
            1 => [
                'type'       => 'AUDIO',
                'collection' => 'audio',
            ],
            2 => [
                'type'       => 'VIDEO',
                'collection' => 'video',
            ],
            3 => [
                'type'       => 'YOUTUBE',
                'collection' => 'youtube',
            ],
            5 => [
                'type'       => 'VIMEO',
                'collection' => 'vimeo',
            ],
        ];

        $return['type'] = $feedTypes[$this->type]['type'];
        $collection     = $feedTypes[$this->type]['collection'];
        $media          = $this->getFirstMedia($collection);
        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
        $w              = 640;
        $h              = 1280;
        $youtubeBaseUrl = config('zevolifesettings.youtubeappurl');
        $vimeoBaseUrl   = config('zevolifesettings.vimeoappurl');
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w              = 600;
            $h              = 400;
            $youtubeBaseUrl = config('zevolifesettings.youtubeembedurl');
            $vimeoBaseUrl   = config('zevolifesettings.vimeoembedurl');
        }

        if ($this->type == 1) {
            $return['backgroundImage'] = $this->getMediaData('audio_background', ['w' => $w, 'h' => $h, 'zc' => 1]);
            $return['mediaURL']        = \url($media->getUrl());
        } elseif ($this->type == 2) {
            $return['backgroundImage'] = $this->getMediaData('video', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 1]);
            $return['mediaURL']        = \url($media->getUrl());
        } elseif ($this->type == 3) {
            $return['backgroundImage'] = $this->getMediaData('youtube', ['w' => $w, 'h' => $h, 'zc' => 1]);
            $return['mediaURL']        = $youtubeBaseUrl . $media->getCustomProperty('ytid');
        } elseif ($this->type == 5) {
            $return['backgroundImage'] = $this->getMediaData('vimeo', ['w' => $w, 'h' => $h, 'zc' => 1]);
            $return['mediaURL']        = $vimeoBaseUrl . $media->getCustomProperty('vmid');
        }

        return $return;
    }

    /**
     * Clone the stories for ZCA user role
     * @param feed, 
     * @param payload
     * @return boolean
     */
    public function cloneEntity($feed, $payload){
        $user      = auth()->user();
        $role      = getUserRole($user);
        $company   = $user->company->first();
        $companyId = (($role->group == 'company' || $role->group == 'reseller') ? $company->id : null);

        if (!empty($payload['start_date'])) {
            if (!empty($payload['timezone'])) {
                $start_date = Carbon::parse($payload['start_date'], $feed['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse($payload['start_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($feed['timezone'])) {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"), $feed['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $start_date = Carbon::parse(date("Y-m-d 00:00:00"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        if (!empty($payload['end_date'])) {
            if (!empty($feed['timezone'])) {
                $end_date = Carbon::parse($payload['end_date'], $feed['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse($payload['end_date'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        } else {
            if (!empty($feed['timezone'])) {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"), $feed['timezone'])->setTimezone(\config('app.timezone'))->toDateTimeString();
            } else {
                $end_date = Carbon::parse(date("Y-m-d 23:59:59"))->setTimezone(\config('app.timezone'))->toDateTimeString();
            }
        }

        $data = [
            'creator_id'      => $feed['creator_id'],
            'created_by'      => $user->id,
            'type'            => $feed['type'],
            'title'           => $feed['title'],
            'subtitle'        => $feed['subtitle'],
            'description'     => (!empty($feed['description']) ? $feed['description'] : null),
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'company_id'      => $companyId,
            'is_cloned'       => true,
            'cloned_from'     => $feed['id'],
            'category_id'     => $feed['category_id'],
            'sub_category_id' => $feed['sub_category_id'],
            'timezone'        => (!empty($feed['timezone']) ? $feed['timezone'] : 'UTC'),
            'tag_id'          => null
        ];
        
        $newFeed = self::create($data);
        
        if (!empty($feed->getFirstMediaUrl('featured_image'))) {
            $media     = $feed->getFirstMedia('featured_image');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('featured_image')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('featured_image')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('featured_image', config('medialibrary.disk_name'));
        }
        
        if (!empty($feed->getFirstMediaUrl('audio_background'))) {
            $media     = $feed->getFirstMedia('audio_background');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('audio_background')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('audio_background')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('audio_background', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('audio_background_portal'))) {
            $media     = $feed->getFirstMedia('audio_background_portal');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('audio_background_portal')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('audio_background_portal')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('audio_background_portal', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('header_image'))) {
            $media     = $feed->getFirstMedia('header_image');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('header_image')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('header_image')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('audio'))) {
            $media     = $feed->getFirstMedia('audio');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('audio')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('audio')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('audio', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('video'))) {
            $media     = $feed->getFirstMedia('video');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $newFeed->clearMediaCollection('video')
                ->addMediaFromUrl(
                    $feed->getFirstMediaUrl('video')
                )
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('video', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('youtube'))) {
            $videoId = getYoutubeVideoId($feed->getFirstMedia('youtube')->name);
            $media     = $feed->getFirstMedia('youtube');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $customProperties = ['title' => $name, 'ytid' => $videoId];
            $newFeed->clearMediaCollection('youtube')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault')
                )
                ->withCustomProperties($customProperties)
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('youtube', config('medialibrary.disk_name'));
        }

        if (!empty($feed->getFirstMediaUrl('vimeo'))) {
            $videoId = getIdFromVimeoURL($feed->getFirstMedia('vimeo')->name);
            $media     = $feed->getFirstMedia('vimeo');
            $imageData = explode(".", $media->file_name);
            $name      = $newFeed->id . '_' . \time();
            $customProperties = ['title' => $name, 'vmid' => $videoId];
            $newFeed->clearMediaCollection('vimeo')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url')
                )
                ->withCustomProperties($customProperties)
                ->usingName($media->name)
                ->usingFileName($name . '.' . $imageData[1])
                ->toMediaCollection('vimeo', config('medialibrary.disk_name'));
        }

        $feed_companyInput = [];
        $teamIds             = TeamLocation::where('company_id', $companyId)->select('team_id')->distinct()->get()->pluck('team_id')->toArray();
        $feed_companyInput[] = [
            'feed_id'    => $newFeed->id,
            'company_id' => $companyId,
            'created_at' => Carbon::now(),
        ];
        $newFeed->feedteam()->sync($teamIds);
        $newFeed->feedcompany()->sync($feed_companyInput);

        //Removed this compnay and team from old (original) feed
        DB::table('feed_company')->where(['feed_id' => $feed['id'], 'company_id' => $companyId])->delete();
        DB::table('feed_team')->where('feed_id', $feed['id'])->whereIn('team_id', $teamIds)->delete();

        return $newFeed ? true : false;
    }
}
