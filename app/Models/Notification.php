<?php

namespace App\Models;

use App\Notifications\SystemManualNotification;
use App\Observers\NotificationObserver;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Notification extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'creator_timezone',
        'tag',
        'type',
        'title',
        'message',
        'deep_link_uri',
        'scheduled_at',
        'push',
        'is_mobile',
        'is_portal',
        'debug_identifier',
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
    protected $casts = ['push' => 'boolean', 'is_mobile' => 'boolean', 'is_portal' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['scheduled_at'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(NotificationObserver::class);
    }

    /**
     * @return HasMany
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User')
            ->withPivot('sent')
            ->withPivot('read')
            ->withPivot('read_at')
            ->withPivot('sent_on')
            ->withTimestamps();
    }

    /**
     * 'hasMany' relation using 'companies'
     * table via 'company_id' field.
     *
     * @return hasMany
     */
    public function company(): HasMany
    {
        return $this->hasMany(Company::class, 'id', 'company_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 320, 'h' => 320]);
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
        return getThumbURL($params, 'notification', 'logo');
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

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('message', function ($record) {
                return $record->message;
            })
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.notifications.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'message'])
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
        $query = self::leftJoin('users', 'notifications.creator_id', '=', 'users.id')
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->select('notifications.id', 'notifications.title', 'notifications.message', 'notifications.updated_at', DB::raw("CONCAT(users.first_name,' ',users.last_name) as creatorName"), 'notifications.company_id')
            ->where(function ($query) {
                if (!is_null(\Auth::user()->company->first())) {
                    return $query->where('notifications.company_id', \Auth::user()->company->first()->id)
                        ->orWhere('user_team.company_id', \Auth::user()->company->first()->id);
                } else {
                    return $query;
                }
            });

        if (in_array('recordTitle', array_keys($payload)) && !empty($payload['recordTitle'])) {
            $query->where('notifications.title', 'like', '%' . $payload['recordTitle'] . '%');
        }
        if (in_array('recordMsg', array_keys($payload)) && !empty($payload['recordMsg'])) {
            $query->where('notifications.message', 'like', '%' . $payload['recordMsg'] . '%');
        }
        if (in_array('recordCreator', array_keys($payload)) && !empty($payload['recordCreator'])) {
            $query->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['recordCreator'] . '%');
        }

        $orderColumn = array("notifications.updated_at", "notifications.title", "creatorName", "notifications.message");
        $orName      = $orderColumn[$payload['order'][0]['column']];

        if (!empty($orName)) {
            $query = $query->orderBy($orName, $payload['order'][0]['dir']);
        } else {
            $query = $query->orderBy('notifications.updated_at', 'DESC');
        }

        $data           = array();
        $data['total']  = $query->count();
        $data['record'] = $query->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $user         = auth()->user();
        $userTimeZone = $user->timezone;

        $data = [
            'type'             => 'Manual',
            'creator_id'       => $user->getKey(),
            'company_id'       => !is_null(\Auth::user()->company->first()) ? \Auth::user()->company->first()->id : null,
            'creator_timezone' => $userTimeZone,
            'title'            => $payload['title'],
            'message'          => $payload['message'],
            'push'             => (!empty($payload['push']) && $payload['push'] == 'on'),
            'scheduled_at'     => Carbon::parse($payload['schedule_date_time'], $userTimeZone)->setTimezone(\config('app.timezone')),
            'deep_link_uri'    => 'zevolife://zevo/alerts',
        ];

        $record = self::create($data);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $record->id . '_' . \time();
            $record->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->preservingOriginal()
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        // add selected members as notification recipients
        $notifiables = \App\Models\User::find($payload['members_selected']);

        if (!empty($notifiables)) {
            foreach ($notifiables as $notifiable) {
                $notifiable->notifications()->attach($record, ['sent' => false]);
            }
        }

        if ($record) {
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
        $this->clearMediaCollection('logo');
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    public function sendNotification(): void
    {
        if ($this->is_mobile) {
            // scheduled date in notification creator's timezone
            $scheduled_at = Carbon::parse($this->scheduled_at, config('app.timezone'))->setTimezone($this->creator_timezone)->todatetimeString();

            $nowInUT = now($this->creator_timezone)->todatetimeString();

            if ($scheduled_at <= $nowInUT) {
                $user = User::find($this->recepient_id);

                if (!empty($user)) {
                    $pivotExsisting = $user->notifications()->wherePivot('user_id', $user->getKey())->wherePivot('notification_id', $this->id)->first();

                    if (!empty($pivotExsisting)) {
                        if ($this->push) {
                            \Notification::send(
                                $user,
                                new SystemManualNotification($this)
                            );
                        }

                        $pivotExsisting->pivot->sent    = 1;
                        $pivotExsisting->pivot->sent_on = now()->todatetimeString();
                        $pivotExsisting->pivot->save();
                    }
                }
            }
        }
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getRecipientsData($payload)
    {
        $list = $this->getRecipientsList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('received', function ($record) {

                $currentTime = \now($record->timezone);
                $createdTime = Carbon::parse($record->sent_on, config('app.timezone'))->setTimezone($record->timezone);

                $duration = preg_replace('/(\d+)/', '${1} ', $currentTime->diffForHumans($createdTime, true, true, 1)) . ' ago';

                $search   = array('mos', 'yrs');
                $replace  = array('mo', 'yr');
                $subject  = $duration;
                return str_replace($search, $replace, $subject);
            })
            ->addColumn('read', function ($record) {
                return ($record->read == 0) ? 'False' : 'True';
            })
            ->addColumn('sent', function ($record) {
                return ($record->sent == 0) ? 'False' : 'True';
            })
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getRecipientsList($payload)
    {
        $whereConditions = [];

        if (!is_null(\Auth::user()->company->first())) {
            $whereConditions = [
                'user_team.company_id' => \Auth::user()->company->first()->id,
            ];
        }

        $query = \DB::table('notification_user')
            ->leftJoin('users', 'users.id', '=', 'notification_user.user_id')
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->select('notification_user.*', \DB::raw("CONCAT(users.first_name,' ',users.last_name) as userName"), 'users.email', 'users.timezone', 'user_team.id', 'user_team.company_id')
            ->where('notification_user.notification_id', $this->getKey())
            ->where('notification_user.sent', true)
            ->where($whereConditions)
            ->orderBy('notification_user.updated_at', 'DESC');

        return $query->get();
    }
}
