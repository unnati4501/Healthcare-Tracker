<?php

namespace App\Models;

use App\Models\CategoryTags;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Jobs\SendShortPushNotification;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Shorts extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shorts';

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
        'tag_id',
        'title',
        'duration',
        'deep_link_uri',
        'view_count',
        'description'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

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
    public function shortssubcategory(): BelongsTo
    {
        return $this->BelongsTo('App\Models\SubCategory', 'sub_category_id');
    }

    /**
     * One-to-Many relations with Shorts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shortsGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'shorts_tag', 'short_id', 'goal_id');
    }

    /**
     * One-to-Many relations with Shorts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shortscompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'shorts_company', 'short_id', 'company_id');
    }

    /**
     * One-to-Many relations with Shorts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shortsteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'shorts_team', 'short_id', 'team_id');
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
    public function getHeaderImageAttribute(): string
    {
        return $this->getHeaderImage(['w' => 1080, 'h' => 1920]);
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
        return getThumbURL($params, 'shorts', 'header_image');
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
     * @return BelongsToMany
     */
    public function shortsUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'shorts_user', 'short_id', 'user_id')
            ->withPivot('saved', 'saved_at', 'liked', 'favourited', 'favourited_at', 'view_count')
            ->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->shortsUserLogs()->wherePivot('liked', true)->count();
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
            ->addColumn('header_image', function ($record) {
                return "<div class='table-img table-img-l'><img src='{$record->getFirstMediaUrl('header_image')}' alt=''></div>";
            })
            ->addColumn('companiesName', function ($record) {
                $companies        = $record->shortscompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                $totalCompanies   = sizeof($companies);
                if ($totalCompanies > 0) {
                    return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                }
            })
            ->addColumn('view_count', function ($record) {
                return $record->shortsUserLogs()->sum('shorts_user.view_count');
            })
            ->addColumn('total_likes', function ($record) {
                $likedCountRecords = $record->shortsUserLogs()->select('shorts_user.liked')->get()->pluck('liked')->toArray();
                return array_sum($likedCountRecords);
            })
            ->addColumn('actions', function ($record) {
                return view('admin.shorts.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'header_image', 'companiesName'])
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
                'shorts.*',
                DB::raw('sub_categories.name AS subcategory_name'),
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS author"),
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag")
            )
            ->leftJoin('users', 'users.id', '=', 'shorts.author_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'shorts.sub_category_id')
            ->leftJoin('shorts_tag', 'shorts_tag.short_id', '=', 'shorts.id')
            ->leftJoin('shorts_user', 'shorts_user.short_id', '=', 'shorts.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'shorts.tag_id')
            ->where(['sub_categories.status' => 1])
            ->groupBy('shorts.id');

        if (in_array('shortsName', array_keys($payload)) && !empty($payload['shortsName'])) {
            $query->where('title', 'like', '%' . $payload['shortsName'] . '%');
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
                $query->whereNull('shorts.tag_id');
            } else {
                $query->where('shorts.tag_id', $payload['tag']);
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
            $query->orderByDesc('shorts.updated_at', 'DESC');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
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
        $checkNotification = Notification::Join('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
            ->where(function ($query) {
                $query
                    ->where('notifications.tag', 'short')
                    ->where('notifications.deep_link_uri', $this->deep_link_uri);
            });
        if ($checkNotification->get()->count() > 0) {
            $checkNotification->delete();
        }
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
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'shorts', $collection);
        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getShortsMediaData()
    {
        $return     = [];
        $collection = "";
        $shortTypes = [
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
                'collection' => 'vimeo',
            ],

        ];

        $return['type'] = $shortTypes[$this->type]['type'];
        $collection     = $shortTypes[$this->type]['collection'];
        $media          = $this->getFirstMedia($collection);
        $youtubeBaseUrl = config('zevolifesettings.youtubeappurl');

        if ($this->type == 1) {
            $return['mediaURL']        = \url($media->getUrl());
        } elseif ($this->type == 2) {
            $return['mediaURL']        = $youtubeBaseUrl . $this->getFirstMedia('youtube')->getCustomProperty('ytid');
        } elseif ($this->type == 3) {
            $return['mediaURL']        = $this->getFirstMedia('vimeo')->name;
        }

        return $return;
    }

    public function getSubCategoryData(): array
    {
        $return      = [];
        $subcategory = $this->podcastsubcategory;

        $return['id']   = $subcategory->id;
        $return['name'] = $subcategory->name;

        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getAuthorData(): array
    {
        $return  = [];
        $author = User::find($this->author_id);

        if (!empty($author)) {
            $return['id']    = $author->getKey();
            $return['name']  = $author->full_name;
            $return['image'] = $author->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

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

        $role    = getUserRole();
        $data    = [
            'sub_category_id' => $payload['shorts_category'],
            'author_id'       => $payload['author'],
            'type'            => $payload['shorts_type'],
            'title'           => $payload['title'],
            'description'     => $payload['description'],
            'duration'        => $payload['duration'],
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $short = self::create($data);

        $short->deep_link_uri = "zevolife://zevo/short/" . $short->getKey() . '/' . $short->sub_category_id;
        $short->save();

        if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['vimeo'], 'shorts');
            if (empty($videoId)) {
                throw ValidationException::withMessages([
                    'error' => trans('labels.common_title.something_wrong_try_again'),
                ]);
            }
            $name             = $short->id . '_' . \time();
            $customProperties = ['title' => $payload['title'], 'link' => $payload['vimeo'], 'vmid' => $videoId];
            $short
                ->clearMediaCollection('vimeo')
                ->addMediaFromUrl(
                    config('zevolifesettings.default_fallback_image_url'),
                    $short->getAllowedMediaMimeTypes('logo')
                )
                ->withCustomProperties($customProperties)
                ->usingName($payload['vimeo'])
                ->usingFileName($name . '.png')
                ->toMediaCollection('vimeo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $short->shortsGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['shorts_companys'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['shorts_companys'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $short->shortscompany()->sync($companyIds);
            $short->shortsteam()->sync($payload['shorts_companys']);
        }

        if (!empty($payload['header_image'])) {
            $name = $short->id . '_' . \time();
            $short
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if ($short) {
            // dispatch job to send push notification to all user when short created
            \dispatch(new SendShortPushNotification($short, "short-created"));
            return true;
        } else {
            return false;
        }
    }

    /**
     * get shorts edit data.
     *
     * @param  $id
     * @return array
     */
    public function shortsEditData()
    {
        $role                     = getUserRole();
        $data                     = array();
        $data['data']             = $this;
        $data['data']['duration'] = $this->duration;
        $data['subcategory']      = SubCategory::where('category_id', 10)->pluck('name', 'id')->toArray();
        $author                   = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
            ->where(["is_coach" => 1, 'is_blocked' => 0])
            ->pluck('name', 'id')
            ->toArray();

        $shorts_companys = array();

        if (!empty($this->shortsteam)) {
            $shorts_companys = $this->shortsteam->pluck('id')->toArray();
        }

        $data['shorts_companys'] = $shorts_companys;
        $data['author']    = array_replace(config('zevolifesettings.defaultAuthor'), $author);
        $data['goalTags']  = Goal::pluck('title', 'id')->toArray();
        $data['roleGroup'] = $role->group;
        if ($role->group == 'zevo') {
            $data['tags'] = CategoryTags::where("category_id", 10)->pluck('name', 'id')->toArray();
        }
        $goal_tags = array();
        if (!empty($this->shortsGoalTag)) {
            $goal_tags = $this->shortsGoalTag->pluck('id')->toArray();
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
        $data    = [
            'sub_category_id' => $payload['shorts_category'],
            'author_id'       => $payload['author'],
            'title'           => $payload['title'],
            'description'     => $payload['description'],
            'duration'        => $payload['duration'],
        ];
        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }
        $updated = $this->update($data);

        if (isset($payload['vimeo']) && !empty($payload['vimeo'])) {
            $videoId = getIdFromVimeoURL($payload['vimeo'], 'shorts');
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

        if (!empty($payload['header_image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        $this->shortsGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->shortsGoalTag()->sync($payload['goal_tag']);
        }
        if (!empty($payload['shorts_companys'])) {
            $companyIds             = TeamLocation::whereIn('team_id', $payload['shorts_companys'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $existingComapnies      = $this->shortsteam()->pluck('teams.id')->toArray();
            $newlyAssociatedComps   = array_diff($payload['shorts_companys'], $existingComapnies);
            $removedAssociatedComps = array_diff($existingComapnies, $payload['shorts_companys']);

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
                            ->where('notifications.tag', 'short')
                            ->where('notifications.deep_link_uri', $this->deep_link_uri);
                    })
                    ->delete();
            }

            $this->shortscompany()->sync($companyIds);
            $this->shortsteam()->sync($payload['shorts_companys']);
        }

        // dispatch job to send meditation notification to newly associated company users if any
        if (!empty($newlyAssociatedComps)) {
            \dispatch(new SendShortPushNotification($this, "short-created", $newlyAssociatedComps));
        }

        if ($updated) {
            return true;
        }

        return false;
    }
}
