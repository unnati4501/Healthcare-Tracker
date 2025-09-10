<?php

namespace App\Models;

use App\Jobs\SendPodcastPushNotification;
use App\Models\CategoryTags;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Observers\PodcastObserver;
use App\Traits\HasRewardPointsTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Podcast extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'podcasts';

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
        'title',
        'duration',
        'deep_link_uri',
        'view_count',
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
    protected $dates = [
        'created_at',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(PodcastObserver::class);
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
    public function podcastsubcategory(): BelongsTo
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
    public function podcastGoalTag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'podcast_tag', 'podcast_id', 'goal_id');
    }

    /**
     * One-to-Many relations with Meditation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function podcastcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'podcast_company', 'podcast_id', 'company_id');
    }

    /**
     * One-to-Many relations with Meditation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function podcastteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'podcast_team', 'podcast_id', 'team_id');
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
    public function getLogoUrlAttribute(): string
    {
        return $this->getLogoUrl(['w' => 800, 'h' => 800]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoUrlNameAttribute(): string
    {
        return $this->getFirstMedia('logo')->name;
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoUrl(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'podcasts', 'logo');
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
            ->addColumn('logo', function ($record) {
                $label = trans('podcast.buttons.listen');
                $url = $record->track_url;

                return "<div class='table-img table-img-l'><img src='{$record->logo_url}'/></div>
                <a class='mt-1 play-podcast-media' data-toggle='modal' data-source='{$url}' data-title='{$record->title}' href='javascript:void(0);'>
                    <i class='far fa-play mr-2'>
                    </i>
                    {$label}
                </a>";
            })
            ->addColumn('companiesName', function ($record) {
                $companies        = $record->podcastcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                $totalCompanies   = sizeof($companies);

                if ($totalCompanies > 0) {
                    return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                }
            })
            ->addColumn('totalLikes', function ($record) {
                return !empty($record->totalLikes) ? numberFormatShort($record->totalLikes) : 0;
            })
            ->addColumn('view_count', function ($record) {
                return !empty($record->view_count) ? numberFormatShort($record->view_count) : 0;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.podcast.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'logo', 'companiesName'])
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
                'podcasts.*',
                DB::raw('sum(user_podcast_logs.view_count) as view_count'),
                DB::raw('SUM(user_podcast_logs.liked = 1) AS totalLikes'),
                DB::raw('sub_categories.name AS subcategory_name'),
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS coach_name"),
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag")
            )
            ->leftJoin('users', 'users.id', '=', 'podcasts.coach_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
            ->leftJoin('user_podcast_logs', 'user_podcast_logs.podcast_id', '=', 'podcasts.id')
            ->leftJoin('category_tags', 'category_tags.id', '=', 'podcasts.tag_id')
            ->where(['sub_categories.status' => 1])
            ->groupBy('podcasts.id');

        if (in_array('podcastName', array_keys($payload)) && !empty($payload['podcastName'])) {
            $query->where('title', 'like', '%' . $payload['podcastName'] . '%');
        }

        if (in_array('coach', array_keys($payload)) && !empty($payload['coach'])) {
            $query->where('coach_id', '=', $payload['coach']);
        }

        if (in_array('subcategory', array_keys($payload)) && !empty($payload['subcategory'])) {
            $query->where('sub_category_id', '=', $payload['subcategory']);
        }

        if (in_array('tag', array_keys($payload)) && !empty($payload['tag'])) {
            if (strtolower($payload['tag']) == 'na') {
                $query->whereNull('podcasts.tag_id');
            } else {
                $query->where('podcasts.tag_id', $payload['tag']);
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
            $query->orderByDesc('podcasts.updated_at', 'DESC');
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
            'title'           => $payload['name'],
            'sub_category_id' => $payload['podcast_subcategory'],
            'coach_id'        => $payload['health_coach'],
            'duration'        => $payload['duration'],
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $track = self::create($data);

        $track->deep_link_uri = "zevolife://zevo/podcast/" . $track->getKey() . '/' . $track->sub_category_id;
        $track->save();

        if (!empty($payload['podcast_logo'])) {
            $name = $track->id . '_' . \time();
            $track
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('podcast_logo')
                ->usingName($payload['podcast_logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['podcast_logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
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

        if (!empty($payload['goal_tag'])) {
            $track->podcastGoalTag()->sync($payload['goal_tag']);
        }

        if (!empty($payload['podcast_company'])) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['podcast_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $track->podcastcompany()->sync($companyIds);
            $track->podcastteam()->sync($payload['podcast_company']);
        }

        if ($track) {
            // dispatch job to send push notification to all user when meditation created
           \dispatch(new SendPodcastPushNotification($track, "podcast-created"));

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

    public function podcastEditData()
    {
        $data                = array();
        $data['data']        = $this;
        $data['subcategory'] = SubCategory::where('category_id', 9)->pluck('name', 'id')->toArray();
        $healthcoach         = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
            ->where(["is_coach" => 1, 'is_blocked' => 0])
            ->pluck('name', 'id')
            ->toArray();
        $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $healthcoach);
        $data['goalTags']    = Goal::pluck('title', 'id')->toArray();

        $podcast_companies = array();

        if (!empty($this->podcastteam)) {
            $podcast_companies = $this->podcastteam->pluck('id')->toArray();
        }

        $data['podcast_companies'] = $podcast_companies;
        
        $goal_tags = array();
        if (!empty($this->podcastGoalTag)) {
            $goal_tags = $this->podcastGoalTag->pluck('id')->toArray();
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
            'sub_category_id' => $payload['podcast_subcategory'],
            'coach_id'        => $payload['health_coach'],
            'duration'        => $payload['duration'],
        ];

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $updated = $this->update($data);

        if (!empty($payload['podcast_logo'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('podcast_logo')
                ->usingName($payload['podcast_logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['podcast_logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
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

        $this->podcastGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->podcastGoalTag()->sync($payload['goal_tag']);
        }
        if (!empty($payload['podcast_company'])) {
            $companyIds             = TeamLocation::whereIn('team_id', $payload['podcast_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            $existingComapnies      = $this->podcastteam()->pluck('teams.id')->toArray();
            $newlyAssociatedComps   = array_diff($payload['podcast_company'], $existingComapnies);
            $removedAssociatedComps = array_diff($existingComapnies, $payload['podcast_company']);

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

            $this->podcastcompany()->sync($companyIds);
            $this->podcastteam()->sync($payload['podcast_company']);
        }

        // dispatch job to send meditation notification to newly associated company users if any
        if (!empty($newlyAssociatedComps)) {
            \dispatch(new SendPodcastPushNotification($this, "podcast-created", $newlyAssociatedComps));
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
        $return['url'] = getThumbURL($param, 'podcasts', $collection);
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
     * @return BelongsToMany
     */
    public function podcastUserLogs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_podcast_logs', 'podcast_id', 'user_id')->withPivot('id', 'saved', 'liked', 'favourited', 'favourited_at', 'view_count')->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->podcastUserLogs()->wherePivot('liked', true)->count();
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
    public function podcastIncompletedUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_incompleted_podcasts', 'podcast_id', 'user_id')->withPivot('id', 'duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function podcastListenedUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_listened_podcasts', 'podcast_id', 'user_id')->withPivot('id', 'duration_listened')->withTimestamps();
    }
}
