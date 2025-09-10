<?php

namespace App\Models;

use App\Models\Exercise;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Badge extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'badges';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'challenge_category_id',
        'challenge_target_id',
        'type',
        'title',
        'description',
        'can_expire',
        'expires_after',
        'target',
        'uom',
        'model_id',
        'model_name',
        'is_default',
        'challenge_type_slug',
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
    protected $casts = ['can_expire' => 'boolean', 'is_default' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * @return BelongsToMany
     */
    public function badgeusers(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'badge_user', 'badge_id', 'user_id')->withPivot('status', 'expired_at', 'created_at', 'level')->withTimestamps();
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
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $media = $this->getFirstMedia('logo');
        return !empty($media->name) ? $media->name : "onboard_large.png";
    }

    /**
     * @param string $size
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
        return getThumbURL($params, 'badge', 'logo');
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
                //'video/quicktime',
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

    public function getTableData($payload, $type = "default")
    {
        $list              = $this->getRecordList($payload, $type);
        $challengeTypeName = config('zevolifesettings.challenge');
        $role              = getUserRole();
        $issuperAdmin      = ($role->group == 'zevo');

        if ($type == 'masterclass') {
            return DataTables::of($list['record'])
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $list['total'],
                    "recordsFiltered" => $list['total'],
                ])
                ->addColumn('awarded_badge', function ($record) {
                    return $record->badgeusers()->count();
                })
                ->rawColumns(['actions', 'logo'])
                ->make(true);
        } else {
            return DataTables::of($list['record'])
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $list['total'],
                    "recordsFiltered" => $list['total'],
                ])
                ->addColumn('activity', function ($record) use ($challengeTypeName) {
                    if ($record->type == "challenge" && !empty($record->challenge_type_slug)) {
                        return $challengeTypeName[$record->challenge_type_slug] . " Challenge";
                    } elseif (!empty($record->challenge_target_id) && $record->challenge_target_id == 4) {
                        $exercises = Exercise::where('id', $record->model_id)->first();
                        if (!empty($exercises)) {
                            return $record->name . "-" . $exercises->title;
                        } else {
                            return $record->name;
                        }
                    } elseif ($record->type == "masterclass") {
                        return ucfirst($record->type);
                    } elseif ($record->type == "daily") {
                        return 'Steps';
                    } else {
                        return $record->name;
                    }
                })
                ->addColumn('type', function ($record) {
                    if ($record->type == 'daily') {
                        return 'Daily Target';
                    } else {
                        return ucfirst($record->type);
                    }
                })
                ->addColumn('target_value', function ($record) {
                    if ($record->type == 'masterclass' || $record->type == 'daily' || $record->type == 'ongoing') {
                        return "N/A";
                    } else {
                        return ($record->type == "challenge" && $record->target == 0)
                            ? "N/A"
                            : "{$record->target} {$record->uom}"
                        ;
                    }
                })
                ->addColumn('awarded_badge', function ($record) use ($issuperAdmin) {
                    if ($issuperAdmin) {
                        if ($record->type == 'masterclass') {
                            return DB::table('badge_user')->leftJoin('badges', 'badges.id', '=', 'badge_user.badge_id')->where('badges.type', 'masterclass')->where('badges.is_default', false)->count();
                        } else {
                            return $record->badgeusers()->count();
                        }
                    } else {
                        if (!empty(\Auth::user()->company->first())) {
                            return $record->badgeusers()
                                ->join("user_team", "user_team.user_id", "=", "badge_user.user_id")
                                ->where("user_team.company_id", \Auth::user()->company->first()->id)
                                ->count();
                        } else {
                            return $record->badgeusers()->count();
                        }
                    }
                })
                ->addColumn('logo', function ($record) {
                    return '<div class="table-img table-img-l"><img src="' . $record->logo . '" alt=""></div>';
                })
                ->addColumn('actions', function ($record) {
                    return view('admin.badge.listaction', compact('record'))->render();
                })
                ->rawColumns(['actions', 'logo'])
                ->make(true);
        }
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getRecordList($payload, $type = 'default')
    {
        $user            = auth()->user();
        $company         = $user->company->first();
        $challengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        $query           = $this
            ->select(
                "badges.*",
                "challenge_targets.name"
            )
            ->leftJoin("challenge_targets", "badges.challenge_target_id", "=", "challenge_targets.id")
            ->where(function ($query) use ($company, $challengeAccess) {
                if (!is_null($company)) {
                    $query = $query
                        ->where("type", "=", "challenge")
                        ->where('is_default', 1)
                        ->where("challenge_type_slug", "!=", "inter_company");

                    if (!$challengeAccess) {
                        $query = $query->where('challenge_type_slug', 'personal');
                    }

                    return $query;
                } else {
                    return $query;
                }
            });

        if ($type == 'masterclass') {
            $query->orderByDesc("badges.updated_at")
                ->where('type', 'masterclass')
                ->where('is_default', false);
        } else {
            $query->orderByDesc("badges.is_default")
                ->Where(function ($query) {
                    $query->where(function ($query) {
                        $query->where("type", "=", "masterclass")
                            ->where('is_default', true);
                    })->orWhere("type", "!=", "masterclass");
                });
        }

        if (in_array('badgeName', array_keys($payload)) && !empty($payload['badgeName'])) {
            $query->where('title', 'like', '%' . $payload['badgeName'] . '%');
        }

        if (in_array('badgeType', array_keys($payload)) && !empty($payload['badgeType'])) {
            $query->where('type', 'like', '%' . $payload['badgeType'] . '%');
        }

        if (in_array('willExpire', array_keys($payload)) && !empty($payload['willExpire'])) {
            if ($payload['willExpire'] == "yes") {
                $query->where('can_expire', '=', 1);
            } elseif ($payload['willExpire'] == "no") {
                $query->where('can_expire', '=', 0);
            }
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc("badges.updated_at");
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
        $user       = auth()->user();
        $badgeInput = [
            'creator_id'          => $user->id,
            'company_id'          => !is_null(\Auth::user()->company->first()) ? \Auth::user()->company->first()->id : null,
            'challenge_target_id' => ($payload['unite1'] == "challenge" || $payload['unite1'] == "general" || $payload['unite1'] == "ongoing") ? $payload['badge_target'] : null,
            'type'                => $payload['unite1'],
            'title'               => $payload['name'],
            'description'         => $payload['info'],
            'can_expire'          => (!empty($payload['will_badge_expire']) && $payload['will_badge_expire'] == 'yes') ? 1 : 0,
            'expires_after'       => $payload['no_of_days'],
            'target'              => !empty($payload['target_values']) ? $payload['target_values'] : 0,
            'uom'                 => $payload['unite'],
        ];

        if ($payload['badge_target'] == 4 && $payload['unite1'] != "course") {
            $badgeInput['model_id']   = $payload['excercise_type'];
            $badgeInput['model_name'] = 'Exercise';
        }

        $badges = self::create($badgeInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $badges->id . '_' . \time();
            $badges->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        return $badges;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity(array $payload)
    {
        $badgeInput = [
            'title'       => $payload['name'],
            'description' => $payload['info'],
        ];

        $updated = $this->update($badgeInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($updated) {
            return true;
        }

        return false;
    }

    public function badgeEditData()
    {
        $data              = array();
        $data['badgeData'] = $this;
        $data['id']        = $this->id;

        $data['badgeTypes']        = config('zevolifesettings.badgeTypes');
        $data['challengeTypeSlug'] = config('zevolifesettings.challenge');
        
        if ($this->type == 'masterclass') {
            $data['challenge_targets'] = [
                'materclass' => 'Masterclass',
            ];
        } elseif ($this->type == 'daily') {
            $data['challenge_targets'] = [
                'steps' => 'Steps',
            ];
        } else {
            $data['challenge_targets'] = \App\Models\ChallengeTarget::where("is_excluded", 0)->pluck('name', 'id')->toArray();
        }
        $data['exercises'] = \App\Models\Exercise::pluck('title', 'id')->toArray();
        $data['uoms']      = array();
        $data['uom_data']  = config('zevolifesettings.uom');
        $data['edit']      = true;
        return $data;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        $this->clearMediaCollection('logo');
        if ($this->delete()) {
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

    public function getMembersTableData($payload)
    {
        $list = $this->badgeusers()
            ->select('users.id', 'users.first_name', 'users.last_name')
            ->orderBy('badge_user.updated_at', 'DESC');

        if (!is_null(\Auth::user()->company->first())) {
            $list->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                ->where('user_team.company_id', \Auth::user()->company->first()->id);
        }

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $list->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['recordName'] . '%');
        }

        $list = $list->get();

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->pivot->updated_at;
            })
            ->addColumn('name', function ($record) {
                return $record->first_name . " " . $record->last_name;
            })
            ->addColumn('awardedon', function ($record) {
                return $record->pivot->created_at;
            })
            ->addColumn('status', function ($record) {
                return $record->pivot->status;
            })
            ->make(true);
    }

    /**
     * @param string $collection
     * @param array $param
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

        if ($this->type == 'masterclass') {
            $collection = 'masterclass_logo';
        }

        $return['url'] = getThumbURL($param, 'badge', $collection);
        return $return;
    }

    public function expireBadgeForUser(): void
    {
        if (!empty($this->expires_after)) {
            // for scheduled expire badges
            $created_date = Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone);

            $expire_at = $created_date->copy()->addDays((int) $this->expires_after)->todateTimeString();

            $nowInUT = now($this->timezone)->todateTimeString();

            if ($expire_at < $nowInUT) {
                $user = User::find($this->recepient_id);

                if (!empty($user)) {
                    \DB::table('badge_user')->where('id', $this->pivotId)->where('user_id', $user->getKey())->where('badge_id', $this->badge_id)->update(['status' => 'Expired', 'expired_at' => now()->toDateTimeString()]);
                }
            }
        }
    }
}
