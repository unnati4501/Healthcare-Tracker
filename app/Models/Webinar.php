<?php

namespace App\Models;

use App\Jobs\SendWebinarPushNotifications;
use App\Models\CategoryTags;
use App\Models\TeamLocation;
use App\Observers\WebinarObserver;
use App\Traits\HasRewardPointsTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Webinar extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'webinar';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'author_id',
        'type',
        'title',
        'duration',
        'view_count',
        'deep_link_uri',
        'tag_id',
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
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(WebinarObserver::class);
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
     * One-to-Many relations with Webinar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function webinarcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'webinar_company', 'webinar_id', 'company_id');
    }

    /**
     * One-to-Many relations with Webinar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function webinarteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'webinar_team', 'webinar_id', 'team_id');
    }

    /**
     * @return BelongsToMany
     */
    public function webinarSubCategories(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\SubCategory', 'webinar_category', 'webinar_id', 'sub_category_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function webinarsubcategory(): BelongsTo
    {
        return $this->BelongsTo('App\Models\SubCategory', 'sub_category_id');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function webinarGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'webinar_tag', 'webinar_id', 'goal_id');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
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
        return $this->getLogo(['h' => 100, 'w' => 100]);
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
        return getThumbURL($params, 'webinar', 'logo');
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
        return getThumbURL($params, 'webinar', 'header_image');
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAllMediaData(string $collection, array $param): array
    {
        $return   = [];
        $allmedia = $this->getMedia($collection);
        if (sizeof($allmedia) > 0) {
            $allmedia->each(function ($media) use (&$return, $collection, &$param) {
                $mediaData = [
                    'id'     => $media->getKey(),
                    'width'  => $param['w'],
                    'height' => $param['h'],
                ];
                $src = $media->getUrl();
                if (!empty($src)) {
                    $param['src'] = $media->getUrl();
                }
                $mediaData['url'] = getThumbURL($param, 'webinar', $collection);
                $return[]         = $mediaData;
            });
        } else {
            $return[] = [
                'width'  => $param['w'],
                'height' => $param['h'],
                'url'    => getThumbURL($param, 'webinar', $collection),
            ];
        }
        return $return;
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
        $return['url'] = getThumbURL($param, 'webinar', $collection);
        return $return;
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
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('logo', function ($record) {
                $label = trans('webinar.table.watch_video');
                switch ($record->type) {
                    case '2':
                        $url = $record->getFirstMedia('track')->getCustomProperty('ytid');
                        break;
                    case '3':
                        $url = $record->getFirstMedia('track')->getCustomProperty('vmid');
                        break;
                    default:
                        $url = $record->track_url;
                        break;
                }
                return "<div class='table-img table-img-l'><img src='{$record->logo}' alt=''></div><a href='javascript:void(0);' class='mt-1 play-meditation-media' data-type='{$record->type}' data-title='{$record->title}' data-source='{$url}'><i class='far fa-play mr-2'></i> {$label}</a></div>";
            })
            ->addColumn('webinar_name', function ($record) {
                return $record->title;
            })
            ->addColumn('duration', function ($record) {
                $minutes = $record->duration / 60;
                return is_int($minutes) ? $minutes : round($minutes, 2);
            })
            ->addColumn('category', function ($record) {
                return $record->subcategory_name;
            })
            ->addColumn('companiesName', function ($record) {
                $companies        = $record->webinarcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                $totalCompanies   = sizeof($companies);
                if ($totalCompanies > 0) {
                    return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                }
            })
            ->addColumn('author', function ($record) {
                return $record->author_name;
            })
            ->addColumn('created_at', function ($record) {
                return $record->created_at;
            })
            ->addColumn('totalLikes', function ($record) {
                $likedCountRecords = $record->webinarUserLogs()->select('webinar_user.liked')->get()->pluck('liked')->toArray();
                return array_sum($likedCountRecords);
            })
            ->addColumn('view_count', function ($record) {
                $viewCountRecords = $record->webinarUserLogs()->select('webinar_user.view_count')->get()->pluck('view_count')->toArray();
                return array_sum($viewCountRecords);
            })
            ->addColumn('actions', function ($record) {
                return view('admin.webinar.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'logo', 'webinar', 'companiesName'])
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
                'webinar.*',
                DB::raw('SUM(webinar_user.liked = 1) AS totalLikes'),
                DB::raw('sub_categories.name AS subcategory_name'),
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS author_name"),
                DB::raw('IFNULL(sum(webinar_user.view_count),0) AS view_count'),
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag")
            )
            ->leftJoin('users', 'users.id', '=', 'webinar.author_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'webinar.sub_category_id')
            ->leftJoin('webinar_tag', 'webinar_tag.webinar_id', '=', 'webinar.id')
            ->leftJoin('webinar_user', 'webinar_user.webinar_id', '=', 'webinar.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'webinar.tag_id')
            ->where(['sub_categories.status' => 1])
            ->groupBy('webinar.id');

        if (in_array('webinarname', array_keys($payload)) && !empty($payload['webinarname'])) {
            $query->where('title', 'like', '%' . $payload['webinarname'] . '%');
        }

        if (in_array('author', array_keys($payload)) && !empty($payload['author'])) {
            $query->where('author_id', '=', $payload['author']);
        }

        if (in_array('subcategory', array_keys($payload)) && !empty($payload['subcategory'])) {
            $query->where('sub_category_id', '=', $payload['subcategory']);
        }

        if (in_array('type', array_keys($payload)) && !empty($payload['type'])) {
            $query->where('type', '=', $payload['type']);
        }

        if (in_array('tag', array_keys($payload)) && !empty($payload['tag'])) {
            if (strtolower($payload['tag']) == 'na') {
                $query->whereNull('webinar.tag_id');
            } else {
                $query->where('webinar.tag_id', $payload['tag']);
            }
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $sortcolumn = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order      = $payload['order'][0]['dir'];
            switch ($sortcolumn) {
                case 'webinar_name':
                    $column = 'title';
                    break;
                case 'category':
                    $column = 'sub_category_id';
                    break;
                case 'author':
                    $column = 'author_id';
                    break;
                case 'companiesName':
                    $column = "id";
                    break;
                default:
                    $column = $sortcolumn;
                    break;
            }
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('webinar.updated_at', 'DESC');
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

        $role    = getUserRole();
        $seconds = $payload['duration'] * 60;
        $data    = [
            'sub_category_id' => $payload['webinar_category'],
            'author_id'       => $payload['author'],
            'type'            => $payload['webinar_type'],
            'title'           => $payload['title'],
            'duration'        => $seconds,
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $webinar = self::create($data);

        $webinar->deep_link_uri = "zevolife://zevo/webinar/" . $webinar->getKey() . '/' . $webinar->sub_category_id;
        $webinar->save();
        $webinar->webinarSubCategories()->sync($payload['webinar_category']);

        if (!empty($payload['webinar_cover'])) {
            $name = $webinar->id . '_' . \time();
            $webinar
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('webinar_cover')
                ->usingName($payload['webinar_cover']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['webinar_cover']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['webinar_file'])) {
            $name = $webinar->id . '_' . \time();
            $webinar
                ->clearMediaCollection('track')
                ->addMediaFromRequest('webinar_file')
                ->usingName($payload['webinar_file']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['webinar_file']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['webinar_youtube']) && !empty($payload['webinar_youtube'])) {
            $videoId = getYoutubeVideoId($payload['webinar_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $webinar->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['webinar_youtube'], 'ytid' => $videoId];
            $webinar
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $webinar->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['webinar_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['webinar_vimeo']) && !empty($payload['webinar_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['webinar_vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $webinar->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['webinar_vimeo'], 'vmid' => $videoId];
            $webinar
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $webinar->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['webinar_vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $webinar->webinarGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['webinar_company'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['webinar_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $webinar->webinarcompany()->sync($companyIds);
            $webinar->webinarteam()->sync($payload['webinar_company']);
        }

        if (!empty($payload['header_image'])) {
            $name = $webinar->id . '_' . \time();
            $webinar
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if ($webinar) {
            // dispatch job to send push notification to all user when feed created
            \dispatch(new SendWebinarPushNotifications($webinar, "webinar-created"));
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
    public function webinarEditData()
    {
        $role                     = getUserRole();
        $data                     = array();
        $data['data']             = $this;
        $minutes                  = $this->duration / 60;
        $minutes                  = is_int($minutes) ? $minutes : round($minutes, 2);
        $data['data']['duration'] = $minutes;
        $data['subcategory']      = SubCategory::where('category_id', 7)->pluck('name', 'id')->toArray();
        $author                   = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
            ->where(["is_coach" => 1, 'is_blocked' => 0])
            ->pluck('name', 'id')
            ->toArray();

        $webinar_companys = array();

        if (!empty($this->webinarteam)) {
            $webinar_companys = $this->webinarteam->pluck('id')->toArray();
        }

        $data['webinar_companys'] = $webinar_companys;

        $data['author']    = array_replace(config('zevolifesettings.defaultAuthor'), $author);
        $data['goalTags']  = Goal::pluck('title', 'id')->toArray();
        $data['roleGroup'] = $role->group;
        if ($role->group == 'zevo') {
            $data['tags'] = CategoryTags::where("category_id", 7)->pluck('name', 'id')->toArray();
        }
        $goal_tags = array();
        if (!empty($this->webinarGoalTag)) {
            $goal_tags = $this->webinarGoalTag->pluck('id')->toArray();
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
        $role    = getUserRole();
        $seconds = $payload['duration'] * 60;
        $data    = [
            'sub_category_id' => $payload['webinar_category'],
            'author_id'       => $payload['author'],
            'title'           => $payload['title'],
            'duration'        => $seconds,
        ];
        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }
        $updated = $this->update($data);

        if (!empty($payload['webinar_cover'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('webinar_cover')
                ->usingName($payload['webinar_cover']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['webinar_cover']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['webinar_file'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('track')
                ->addMediaFromRequest('webinar_file')
                ->usingName($payload['webinar_file']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['webinar_file']->getClientOriginalExtension())
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['webinar_youtube']) && !empty($payload['webinar_youtube'])) {
            $videoId = getYoutubeVideoId($payload['webinar_youtube']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['webinar_youtube'], 'ytid' => $videoId];
            $this
                ->clearMediaCollection('track')
                ->addMediaFromUrl(
                    getYoutubeVideoCover($videoId, 'hqdefault'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['webinar_youtube'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('track', config('medialibrary.disk_name'));
        }

        if (isset($payload['webinar_vimeo']) && !empty($payload['webinar_vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['webinar_vimeo']);
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $this->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['webinar_vimeo'], 'vmid' => $videoId];
            $this->clearMediaCollection('track')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $this->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['webinar_vimeo'])
                ->usingFileName($name . '.png')
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

        $this->webinarGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->webinarGoalTag()->sync($payload['goal_tag']);
        }
        if (!empty($payload['webinar_company'])) {
            $companyIds             = TeamLocation::whereIn('team_id', $payload['webinar_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $existingComapnies      = $this->webinarteam()->pluck('teams.id')->toArray();
            $newlyAssociatedComps   = array_diff($payload['webinar_company'], $existingComapnies);
            $removedAssociatedComps = array_diff($existingComapnies, $payload['webinar_company']);

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
                            ->where('notifications.tag', 'webinar')
                            ->where('notifications.deep_link_uri', $this->deep_link_uri);
                    })
                    ->delete();
            }

            $this->webinarcompany()->sync($companyIds);

            $this->webinarteam()->sync($payload['webinar_company']);
        }

        // dispatch job to added webinar notification to newly associated company users if any
        if (!empty($newlyAssociatedComps)) {
            \dispatch(new SendWebinarPushNotifications($this, "webinar-created", $newlyAssociatedComps));
        }

        $this->webinarSubCategories()->sync($payload['webinar_category']);
        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function webinarUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'webinar_user', 'webinar_id', 'user_id')
            ->withPivot('saved', 'saved_at', 'liked', 'favourited', 'favourited_at', 'view_count')
            ->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->webinarUserLogs()->wherePivot('liked', true)->count();
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorData(): array
    {
        $return  = [];
        $creator = User::find($this->author_id);

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
     * @return array
     */
    public function getWebinarMediaData()
    {
        $return       = [];
        $webinarTypes = [
            1 => [
                'type'       => 'VIDEO',
                'collection' => 'video',
            ],
            2 => [
                'type'       => 'YOUTUBE',
                'collection' => 'youtube',
            ],
            3 => [
                'type'       => 'VIMEO',
                'collection' => 'track',
            ],

        ];

        $return['type'] = $webinarTypes[$this->type]['type'];
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
            $return['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['mediaURL']        = $this->track_url;
        } elseif ($this->type == 2) {
            $return['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['mediaURL']        = $youtubeBaseUrl . $this->getFirstMedia('track')->getCustomProperty('ytid');
        } elseif ($this->type == 3) {
            $return['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $return['mediaURL']        = $vimeoBaseUrl . $this->getFirstMedia('track')->getCustomProperty('vmid');
        }

        return $return;
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
}
