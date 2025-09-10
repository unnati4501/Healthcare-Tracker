<?php

namespace App\Models;

use App\Events\EventBookedEvent;
use App\Events\EventPendingEvent;
use App\Events\EventRejectedEvent;
use App\Events\EventUpdatedEvent;
use App\Events\SendEventCancelledEvent;
use App\Events\EventStatusChangeEvent;
use App\Jobs\SendEventBookedEamilJob;
use App\Jobs\SendEventPushNotificationJob;
use App\Jobs\SendEventStatusChangeEmailJob;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\CompanyWiseCredit;
use App\Models\EventBookingEmails;
use App\Models\EventBookingLogs;
use App\Models\EventCompany;
use App\Models\EventCsatLogs;
use App\Models\EventPresenters;
use App\Models\EventRegisteredUserLog;
use App\Models\EventPresenterSlots;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserTeam;
use App\Models\CronofyCalendar;
use App\Observers\EventObserver;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Event extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'events';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['creator_id', 'company_id', 'category_id', 'subcategory_id', 'name', 'fees', 'description', 'duration', 'capacity', 'location_type', 'is_csat', 'status', 'is_special', 'presenter', 'deep_link_uri', 'published_on', 'special_event_category_title'];

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
        'is_csat' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_on',
        'created_at',
        'updated_at',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(EventObserver::class);
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
     * "BelongsTo" relation to `companies` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `creator_id` field.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `subcategory_id` field.
     *
     * @return BelongsTo
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    /**
     * "hasMany" relation to `event_companies` table
     * via `company_id` field.
     *
     * @return hasMany
     */
    public function companies(): hasMany
    {
        return $this->hasMany(EventCompany::class, 'event_id');
    }

    /**
     * "hasMany" relation to `event_presenters` table
     * via `company_id` field.
     *
     * @return hasMany
     */
    public function presenters(): hasMany
    {
        return $this->hasMany(EventPresenters::class, 'event_id');
    }

    /**
     * "hasMany" relation to `event_booking_logs` table
     * via `event_id` field.
     *
     * @return hasMany
     */
    public function booking(): hasMany
    {
        return $this->hasMany(EventBookingLogs::class, 'event_id');
    }

    /**
     * "BelongsToMany" relation to `event_booking_logs` table
     * via `event_id` field.
     *
     * @return hasMany
     */
    public function bookedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, EventRegisteredUserLog::class, 'event_id', 'user_id')
            ->withPivot('event_booking_log_id')
            ->withTimestamps();
    }

    /**
     * "BelongsToMany" relation to `event_booking_logs` table
     * via `event_id` field.
     *
     * @return hasMany
     */
    public function csat(): BelongsToMany
    {
        return $this->belongsToMany(EventCsatLogs::class, EventBookingLogs::class, 'event_id', 'id', 'id', 'event_booking_log_id')
            ->withTimestamps();
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 700, 'w' => 700]);
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
        return getThumbURL($params, 'event', 'logo');
    }

    /**
     * @return hasMany
     */
    public function eventBookingEmails(): hasMany
    {
        return $this->hasMany('App\Models\EventBookingEmails', 'event_booking_log_id', 'id');
    }

    /**
     * Set datatable for groups list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $appTimezone = config('app.timezone');
        $eventStatus = config('zevolifesettings.event-status-master');
        $list        = $this->getRecordList($payload);
        $dt          = DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ]);

        if ($payload['type'] == 'booking') {
            $user    = auth()->user();
            $company = $user->company()->first();
            $dt
                ->addColumn('end_time', function ($record) use ($appTimezone) {
                    return Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                        ->toDateTimeString();
                })
                ->addColumn('duration', function ($record) use ($appTimezone) {
                    return Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                        ->toDateTimeString();
                })
                ->addColumn('status', function ($record) use ($eventStatus) {
                    $status = $eventStatus[$record->status];
                    return "<span class='" . $status['class'] . "'>" . $status['text'] . "</span>";
                })
                ->addColumn('actions', function ($record) use ($company) {
                    return view('admin.event.booking-tab-listaction', compact('record', 'company'))->render();
                });
        } else {
            $dt
                ->addColumn('duration', function ($record) {
                    return convertToHoursMins(timeToDecimal($record->duration), false, '%s %s');
                })
                ->addColumn('status', function ($record) use ($eventStatus) {
                    $status = $eventStatus[$record->status];
                    return view('admin.event.publishaction', compact('record', 'status'))->render();
                })
                ->addColumn('actions', function ($record) {
                    $feedBackCount = $openBookingCount = 0;
                    if ($record->status == 2) {
                        $openBookingCount = $record->booking()
                            ->where('event_booking_logs.status', '4')
                            ->count('event_booking_logs.id');
                        $feedBackCount = $record->csat()->count('event_csat_user_logs.id');
                    }
                    return view('admin.event.service-tab-listaction', [
                        'record'           => $record,
                        'openBookingCount' => $openBookingCount,
                        'feedBackCount'    => $feedBackCount,
                    ])->render();
                });
        }

        return $dt
            ->rawColumns(['duration', 'status', 'actions'])
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
        $user        = auth()->user();
        $company     = $user->company()->first();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        if ($payload['type'] == 'booking' && !empty($company)) {
            $query = $this
                ->select(
                    'events.name AS event_name',
                    'event_booking_logs.id',
                    'event_booking_logs.company_id',
                    'assigned_co.name AS assignee',
                    'sub_categories.name AS subcategory_name',
                    \DB::raw('IFNULL(creator_co.name, "Zevo") AS creator'),
                    \DB::raw('event_booking_logs.meta->>"$.presenter" AS presenter_name'),
                    'event_booking_logs.booking_date',
                    'event_booking_logs.start_time',
                    'event_booking_logs.end_time',
                    'event_booking_logs.status',
                    \DB::raw("TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time))) AS startTimeDiff")
                )
                ->selectRaw("? as timezone",[$timezone])
                ->leftJoin('event_booking_logs', 'event_booking_logs.event_id', '=', 'events.id')
                ->join('companies AS assigned_co', 'assigned_co.id', '=', 'event_booking_logs.company_id')
                ->leftJoin('companies AS creator_co', 'creator_co.id', '=', 'events.company_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'events.subcategory_id')
                ->when($payload['assigneeCompany'], function ($query, $string) {
                    $query->where('event_booking_logs.company_id', $string);
                })
                ->when($payload['bookingStatus'], function ($query, $string) {
                    $query->where('event_booking_logs.status', $string);
                });

            if ($company->is_reseller) {
                $assigneeComapnies = Company::select('id')
                    ->where('parent_id', $company->id)
                    ->orWhere('id', $company->id)
                    ->get()->pluck('id')->toArray();
                $query
                    ->whereIn('event_booking_logs.company_id', $assigneeComapnies)
                    ->where(function ($where) use ($company) {
                        $where
                            ->whereNull('events.company_id')
                            ->orWhere('events.company_id', $company->id);
                    });
            } elseif (!is_null($company->parent_id) || is_null($company->parent_id)) {
                $query->where('event_booking_logs.company_id', $company->id);
            }
        } else {
            $query = $this
                ->select(
                    'events.id',
                    'events.name',
                    'events.duration',
                    'events.subcategory_id',
                    \DB::raw('IF(events.is_special = "1", events.special_event_category_title , sub_categories.name) AS subcategory_name'),
                    'events.status'
                )
                ->withCount('companies')
                ->leftjoin('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'events.subcategory_id');
                })
                ->where('events.company_id', (!empty($company) ? $company->id : null))
                ->when($payload['eventStatus'], function ($query, $string) {
                    $query->where('events.status', $string);
                });
        }

        $query
            ->when($payload['eventName'], function ($query, $string) {
                $query->where('events.name', 'like', "%{$string}%");
            })
            ->when($payload['eventCategory'], function ($query, $string) {
                $query->where('events.subcategory_id', $string);
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            if ($payload['type'] == 'booking') {
                $query->orderByDesc('event_booking_logs.updated_at');
            } else {
                $query->orderByDesc('events.updated_at');
            }
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Get presenters by name
     *
     * @param array payload
     * @return array
     */
    public function getPresenters($payload)
    {
        $nowInUTC = now(config('app.timezone'));
        $users    = collect([]);
        $userList = "";
        if ($payload['type'] == 'zsa') {
            $subcategory = ($payload['subcategory'] ?? 0);
            $users       = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id')
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->leftJoin('health_coach_expertises', 'health_coach_expertises.user_id', '=', 'users.id')
                ->where(['users.is_blocked' => 0, 'health_coach_expertises.expertise_id' => $subcategory, 'ws_user.is_cronofy' => true])
                ->where('ws_user.responsibilities', '!=', 1)
                ->whereNull('users.deleted_at')
                ->groupBy('users.id')
                ->get()
                ->pluck('text', 'id');
        } elseif ($payload['type'] == 'rsa' || $payload['type'] == 'rca' || $payload['type'] == 'zca') {
            $user    = auth()->user();
            $company = $user->company()->first();
            $users   = User::select(\DB::raw("CONCAT(users.first_name,' ',users.last_name) AS text"), 'users.id')
                ->join('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->whereHas('company', function ($query) use ($company) {
                    $query->where('companies.id', $company->id);
                })
                ->where('users.is_blocked', 0)
                ->where('ws_user.is_cronofy', true)
                ->whereNull('users.deleted_at')
                ->where("users.start_date", '<=', $nowInUTC->toDateString())
                ->get()
                ->pluck('text', 'id');
        }

        $users->each(function ($user, $key) use (&$userList) {
            $userList .= "<option value='{$key}'>{$user}</option>";
        });

        return [
            'data'  => $userList,
            'count' => count($users),
        ];
    }

    /**
     * To store an event.
     *
     * @param array payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $company     = null;
        if ($role->group != "zevo") {
            $company = $user->company()->first();
        }

        $status = (isset($payload['special_event']) && $payload['special_event'] == 'on') ? 2 : 1;

        $event = $this->create([
            'creator_id'                   => $user->id,
            'company_id'                   => ($company->id ?? null),
            'category_id'                  => 6,
            'subcategory_id'               => (empty($payload['special_event']) ? $payload['subcategory'] : 0),
            'location_type'                => $payload['location'],
            'name'                         => $payload['name'],
            'fees'                         => (!empty($payload['fees']) ? $payload['fees'] : 0),
            'description'                  => $payload['description'],
            'duration'                     => $payload['duration'],
            'capacity'                     => (!empty($payload['capacity']) ? $payload['capacity'] : null),
            'is_csat'                      => (!empty($payload['is_csat']) ? true : false),
            'status'                       => $status,
            'is_special'                   => ((!empty($payload['special_event']) && $payload['special_event'] == 'on') ? true : false),
            'presenter'                    => (!empty($payload['presenterName']) ? $payload['presenterName'] : null),
            'special_event_category_title' => (!empty($payload['specialEventCategoryTitle']) ? $payload['specialEventCategoryTitle'] : null),
        ]);

        if (!empty($payload['logo'])) {
            $name = $event->id . '_' . \time();
            $event
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['company_visibility'])) {
            $companyVisibility = [];
            $presenters        = [];
            foreach ($payload['company_visibility'] as $companyValue) {
                $companyVisibility[] = [
                    'company_id' => $companyValue,
                    'status'     => 1,
                ];

                if (!empty($payload['presenter'])) {
                    foreach ($payload['presenter'] as $user) {
                        $presenters[] = [
                            'company_id' => $companyValue,
                            'user_id'    => $user,
                        ];
                    }
                }
            }
            // attach new event companies
            $event->companies()->createMany($companyVisibility);

            if (!empty($payload['presenter'])) {
                // attach new event presenters
                $event->presenters()->createMany($presenters);
            }
        }

        if (isset($payload['special_event']) && $payload['special_event'] == 'on') {
            $timezone = CompanyLocation::select('timezone')
                ->where('company_id', $company->id)
                ->where('default', 1)->first();
            $timezone    = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
            $bookingDate = Carbon::parse("{$payload['date']} {$payload['timeFrom']}", $timezone)->setTimezone($appTimezone);
            $endTime     = Carbon::parse("{$payload['date']} {$payload['timeTo']}", $timezone)->setTimezone($appTimezone);
            $uid         = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';
            $meta        = [];

            $meta = [
                "presenter" => $payload['presenterName'],
                "timezone"  => $appTimezone,
                "uid"       => $uid,
            ];
            $presenterUserId   = null;

            $bookingDetails = [
                'event_id'          => $event->id,
                'company_id'        => $company->id,
                'presenter_user_id' => $presenterUserId,
                'booking_date'      => $bookingDate->toDateString(),
                'start_time'        => $bookingDate->toTimeString(),
                'end_time'          => $endTime->toTimeString(),
                'meta'              => $meta,
                'status'            => '4',
            ];
            $bookingRecord = $event->booking()->create($bookingDetails);

            if ($bookingRecord) {
                $companyTimezone = $company->locations()->select('timezone')->where('default', 1)->first();
                // get moderators of company for event is getting booked and send booked email
                $companyModeratorsEmails = $company->moderators()->select('users.email')->get()->pluck('email')->chunk(500);
                $companyModeratorsDate   = Carbon::parse("{$payload['date']} {$payload['timeFrom']}", $appTimezone)->setTimezone($companyTimezone->timezone);

                $companyModeratorsEmailData = [
                    'company'       => $company->id,
                    'eventName'     => $payload['name'],
                    'type'          => 'admin',
                    'companyName'   => $company->name,
                    'bookingDate'   => $companyModeratorsDate->format('M d, Y h:i A'),
                    'presenterName' => $payload['presenterName'],
                ];
                dispatch(new SendEventBookedEamilJob($companyModeratorsEmails, $companyModeratorsEmailData));
            }
        }

        if ($event) {
            return true;
        }
        return false;
    }

    /**
     * To update an event
     *
     * @param  array $payload
     * @return booelan
     */
    public function updateEntity($payload)
    {
        $now = now(config('app.timezone'));
        if ($this->status == 2) {
            $oldCsatStatus = $this->is_csat;
            $updated       = $this->update([
                'description'                  => $payload['description'],
                'is_csat'                      => (!empty($payload['is_csat']) ? true : false),
                'fees'                         => (!empty($payload['fees']) ? $payload['fees'] : 0),
                'presenter'                    => (!empty($payload['presenterName']) ? $payload['presenterName'] : null),
                'capacity'                     => (!empty($payload['capacity']) ? $payload['capacity'] : $this->capacity),
                'special_event_category_title' => (!empty($payload['specialEventCategoryTitle']) ? $payload['specialEventCategoryTitle'] : null),

            ]);

            // check if `is_csat` field is changed
            if ($oldCsatStatus != $this->is_csat) {
                // update `is_csat` field as per new value for upcomming event bookings
                $this->booking()
                    ->where('event_booking_logs.status', '4')
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) >= ?", [$now])
                    ->update([
                        'is_csat' => $this->is_csat,
                    ]);
            }
        } else {
            $updated = $this->update([
                'location_type'                => $payload['location'],
                'subcategory_id'               => $payload['subcategory'],
                'name'                         => $payload['name'],
                'fees'                         => (!empty($payload['fees']) ? $payload['fees'] : 0),
                'description'                  => $payload['description'],
                'duration'                     => $payload['duration'],
                'capacity'                     => (!empty($payload['capacity']) ? $payload['capacity'] : null),
                'is_csat'                      => (!empty($payload['is_csat']) ? true : false),
                'status'                       => 1,
                'presenter'                    => (!empty($payload['presenterName']) ? $payload['presenterName'] : null),
                'special_event_category_title' => (!empty($payload['specialEventCategoryTitle']) ? $payload['specialEventCategoryTitle'] : null),
            ]);
        }

        if (!empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['company_visibility']) && !empty($payload['presenter'])) {
            $presenters         = [];
            $oldCompanyIds      = $this->companies()->pluck('company_id')->toArray();
            $removeCompanyIds   = array_diff($oldCompanyIds, $payload['company_visibility']);
            $addCompanyIds      = array_diff($payload['company_visibility'], $oldCompanyIds);
            $commonCompanyIds   = array_intersect($oldCompanyIds, $payload['company_visibility']);
            $oldPresenterIds    = $this->presenters()->groupBy('user_id')->get()->pluck('user_id')->toArray();
            $removePresenterIds = array_diff($oldPresenterIds, $payload['presenter']);
            $addPresenterIds    = array_diff($payload['presenter'], $oldPresenterIds);

            if (!empty($removeCompanyIds)) {
                // remove deleted company
                $this->companies()->whereIn('company_id', $removeCompanyIds)->delete();
                // remove deleted company's presenter
                $this->presenters()->whereIn('company_id', $removeCompanyIds)->delete();
            }

            if (!empty($addCompanyIds)) {
                foreach ($addCompanyIds as $company) {
                    // attach new companies to event
                    $this->companies()->updateOrCreate([
                        'company_id' => $company,
                    ], [
                        'company_id' => $company,
                        'status'     => 1,
                        'deleted_at' => null,
                    ]);

                    foreach ($payload['presenter'] as $user) {
                        $presenters[] = [
                            'company_id' => $company,
                            'user_id'    => $user,
                        ];
                    }
                }
                // attach new event presenters
                $this->presenters()->createMany($presenters);
            }

            if (!empty($removePresenterIds)) {
                // remove deleted presenters from event
                $this->presenters()->whereIn('user_id', $removePresenterIds)->delete();
            }

            $presenters = [];
            if (!empty($addPresenterIds)) {
                foreach ($commonCompanyIds as $company) {
                    foreach ($addPresenterIds as $user) {
                        $presenters[] = [
                            'company_id' => $company,
                            'user_id'    => $user,
                        ];
                    }
                }

                // attach new event presenters
                $this->presenters()->createMany($presenters);
            }
        }

        if ($updated) {
            return true;
        }
        return false;
    }

    /**
     * To publish an event
     *
     * @param  array $payload
     * @return booelan
     */
    public function publishEvent($payload)
    {
        $data = ['published' => true, 'message' => ''];
        // update status of event
        $updated = $this->update(['status' => 2, 'published_on' => \now(config('app.timezone'))->toDateTimeString()]);
        if ($updated) {
            $data['message'] = "Event has been published successfully.";
        } else {
            $data['published'] = false;
            $data['message']   = "Something went wrong while publishing a event!";
        }

        return $data;
    }

    /**
     * To delete an event
     *
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
     * Set datatable for booking logs of event company wise
     *
     * @param array payload
     * @return dataTable
     */
    public function getEventBookins($payload)
    {
        $appTimezone = config('app.timezone');
        $eventStatus = config('zevolifesettings.event-status-master');
        $list        = $this->getEventBookinsRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('end_time', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('duration', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                    ->toDateTimeString();
            })
            ->addColumn('status', function ($record) use ($eventStatus) {
                $status = $eventStatus[$record->status];
                return "<span class='" . $status['class'] . "'>" . $status['text'] . "</span>";
            })
            ->addColumn('actions', function ($record) {
                return view('admin.event.details-listaction', compact('record'))->render();
            })
            ->rawColumns(['duration', 'status', 'actions'])
            ->make(true);
    }

    /**
     * get records for booking logs of event company wise
     *
     * @param array payload
     * @return array
     */
    public function getEventBookinsRecordList($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $query = $this
            ->select(
                'event_booking_logs.id',
                'companies.name AS company_name',
                \DB::raw('event_booking_logs.meta->>"$.presenter" AS presenter_name'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time',
                'event_booking_logs.status',
                \DB::raw("TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time))) AS startTimeDiff")
            )
            ->selectRaw(" ? as timezone",[$timezone])
            ->leftJoin('event_booking_logs', 'event_booking_logs.event_id', '=', 'events.id')
            ->join('companies', 'companies.id', '=', 'event_booking_logs.company_id')
            ->where('events.id', $this->id)
            ->when($payload['eventPresenter'], function ($query, $string) {
                $query->where('event_booking_logs.presenter_user_id', $string);
            })
            ->when($payload['eventCompany'], function ($query, $string) {
                $query->where('event_booking_logs.company_id', $string);
            })
            ->when($payload['eventStatus'], function ($query, $string) {
                $query->where('event_booking_logs.status', $string);
            });

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $query
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('event_booking_logs.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * To get events for marketplace
     *
     * @param array $payload
     * @return string View
     */
    public function getEventsForMarketPlace($payload)
    {
        $returnData = [
            'hasMore' => true,
            'data'    => [],
        ];
        $eventsData = "";
        $user       = auth()->user();
        $role       = getUserRole($user);
        $nowInUTC   = now(config('app.timezone'))->toDateTimeString();
        $company    = null;
        if ($role->group != 'zevo') {
            $company = $user->company()->first();
        }

        $events = $this
            ->select(
                'events.id',
                'events.name',
                'events.duration',
                \DB::raw("(CASE
                    WHEN companies.is_reseller = true THEN 'rsa'
                    WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'rca'
                    WHEN companies.is_reseller = false AND companies.parent_id IS NULL THEN 'zca'
                END) as 'company_type'")
            )
            ->leftjoin('event_presenters', 'event_presenters.event_id', '=', 'events.id')
            ->join('event_companies', function ($query) use ($company) {
                $query->on('event_companies.event_id', '=', 'events.id');
                if ($company && !is_null($company->parent_id)) {
                    $query->where('event_companies.company_id', $company->id);
                }
            })
            ->join('companies', function ($join) use ($nowInUTC) {
                $join
                    ->on('companies.id', '=', 'event_companies.company_id')
                    ->where('companies.subscription_start_date', '<=', $nowInUTC)
                    ->where('companies.subscription_end_date', '>=', $nowInUTC);
            })
            ->where('events.status', 2)
            ->where('events.is_special', 0)
            ->whereNull('event_companies.deleted_at')
            ->havingRaw('company_type IS NOT NULL')
            ->where(function ($query) use ($role, $company) {
                if ($role->group == 'zevo') {
                    $query->whereNull('events.company_id');
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $assigneeComapnies = Company::select('id')
                            ->where('parent_id', $company->id)
                            ->orWhere('id', $company->id)
                            ->get()->pluck('id')->toArray();
                        $query
                            ->whereIn('event_companies.company_id', $assigneeComapnies)
                            ->where(function ($where) use ($company) {
                                $where
                                    ->whereNull('events.company_id')
                                    ->orWhere('events.company_id', $company->id);
                            });
                    } elseif (!is_null($company->parent_id)) {
                        $query->where('event_companies.company_id', $company->id);
                    }
                } elseif ($role->group == 'company') {
                    $query->where('event_companies.company_id', $company->id);
                }
            })
            ->when(($payload['subcategory']), function ($when, $subcategory) {
                $when->where('events.subcategory_id', $subcategory);
            })
            ->when(($payload['name']), function ($when, $name) {
                $when->where('events.name', 'like', "%" . $name . "%");
            })
            ->when(($payload['company']), function ($when, $company) {
                $when->where('event_companies.company_id', $company);
            })
            ->when(($payload['presenter']), function ($when, $presenter) {
                $when->where('event_presenters.user_id', $presenter);
            })
            ->groupBy('events.id')
            ->orderBy('events.name')
            ->simplePaginate(config('zevolifesettings.event_result_pagination'));

        foreach ($events->items() as $event) {
            $duration = convertToHoursMins(timeToDecimal($event->duration), false, '%s %s');
            $eventsData .= view('admin.marketplace.event-block', compact('event', 'duration'))->render();
        }

        $returnData['data']    = $eventsData;
        $returnData['hasMore'] = $events->hasMorePages();
        if ($returnData['hasMore']) {
            $returnData['nextPage'] = ($events->currentPage() + 1);
        }

        return $returnData;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorData(): array
    {
        $return  = [];
        $creator = $this->creator;
        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
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
        $return['url'] = getThumbURL($param, 'event', $collection);
        return $return;
    }

    /**
     * To get available slots with presenters for marketplace
     *
     * @param array $payload
     * @return array
     */
    public function getSlots($event, $bookingLog, $payload)
    {
        $slotsData = [
            'status'       => false,
            'slots'        => "",
            'coUsersCount' => 0,
        ];
        $appTimezone         = config('app.timezone');
        $bookingCompany      = Company::find($payload['company'], ['id', 'is_reseller', 'parent_id']);
        $coType              = 'zca';
        if ($bookingCompany->is_reseller && is_null($bookingCompany->parent_id)) {
            $coType = 'rsa';
        } elseif (!$bookingCompany->is_reseller && !is_null($bookingCompany->parent_id)) {
            $coType = 'rca';
        } elseif (!$bookingCompany->is_reseller && is_null($bookingCompany->parent_id)) {
            $coType = 'zca';
        }
        $timezone = CompanyLocation::select('timezone')
            ->where('company_id', $payload['company'])
            ->where('default', 1)->first();
        $timezone = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
        $duration = convertHoursToMinutes($event->duration);

        // get presenter of event for specific company
        $presenters   = $this->presenters()->where('company_id', $payload['company'])->get();
        $presenterIds = $presenters->pluck('user_id')->toArray();

        $availableIds = $presenterIds;
        if (!empty($bookingLog->id)) {
            $availableIds[] = $bookingLog->presenter_user_id;
            $availableIds   = array_unique($availableIds);
        }

        // get available presenter with thier slots
        if (is_null($this->company_id)) {
            $slots = EventPresenterSlots::select(
                'event_presenter_slots.id',
                'event_presenter_slots.user_id',
                \DB::raw("CONCAT(`users`.`first_name`, ' ', `users`.`last_name`) AS `presenter_name`")
            )
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(`event_presenter_slots`.`start_time`, `users`.`timezone`, ?), '%h:%i %p') AS start_time",[
                $timezone
            ])
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(`event_presenter_slots`.`end_time`, `users`.`timezone`, ?), '%h:%i %p') AS end_time",[
                $timezone
            ])
                ->join('health_coach_user', 'health_coach_user.user_id', '=', 'event_presenter_slots.user_id')
                ->join('users', 'users.id', '=', 'health_coach_user.user_id')
                ->whereIn('health_coach_user.user_id', $availableIds)
                ->whereIn('users.availability_status', [1, 2])
                ->where('health_coach_user.is_cronofy', true)
                ->where(['users.is_coach' => 1, 'users.is_blocked' => 0])
                ->groupBy('event_presenter_slots.user_id')
                ->get();
        } else {
            $slots = User::select('users.id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS presenter_name"))
                ->join('health_coach_user', 'health_coach_user.user_id', '=', 'users.id')
                ->where(['users.is_coach' => 1, 'users.is_blocked' => 0, 'health_coach_user.is_cronofy' => true])
                ->whereIn('users.id', $availableIds)
                ->get();
        }

        if ($slots->isNotEmpty()) {
             $eStartTime     = $eEndTime     = "";
            if (is_null($this->company_id)) {
                if (!empty($bookingLog->meta->start_time) && !empty($bookingLog->meta->end_time) && !empty($bookingLog->meta->timezone)) {
                    $eStartTime = Carbon::createFromFormat('H:i:s', $bookingLog->start_time, $appTimezone)
                        ->setTimezone($timezone)->format('H:i:s');
                    $eEndTime = Carbon::createFromFormat('H:i:s', $bookingLog->end_time, $appTimezone)
                        ->setTimezone($timezone)->format('H:i:s');
                }
            }
            $slots->each(function ($slot) use (&$slotsData, $bookingLog, $payload, $timezone, $appTimezone, $bookedIdsOnDateTime, $event, $duration) {
                $selectedDate = $payload['date'];
                if ($slot->end_time == "12:00 AM") {
                    $slot->end_time = "11:59 PM";
                }
                $splitData['splitslots']             = SplitTime("$selectedDate $slot->start_time", "$selectedDate $slot->end_time", "$duration");
                $disableSlot                         = false;
                $splitData['slot']['id']             = $slot->id;
                $splitData['slot']['presenter_name'] = $slot->presenter_name;
                $splitData['slot']['user_id']        = $slot->user_id;
                $splitData['slot']['user_logo']      = $slot->user->logo;

                // //Check booking log exists for slot or not
                if ($bookingLog->id == null) {
                    //Add mode
                    $bookingLogExists = EventBookingLogs::select(
                        'event_booking_logs.id',
                        'event_booking_logs.slot_id',
                    )
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?,?), '%Y-%m-%d %h:%i %p') AS start_time",[
                            $appTimezone,$timezone
                        ])
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(date_add(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), interval TIME_TO_SEC(events.duration)/60 minute), ?, ?), '%Y-%m-%d %h:%i %p') AS end_time",[
                            $appTimezone,$timezone
                        ])
                        ->leftJoin('events', 'events.id', '=', 'event_booking_logs.event_id')
                        ->where('event_booking_logs.presenter_user_id', $slot->user_id)
                        ->whereIn('event_booking_logs.status', ['4', '6'])
                        ->get()->toArray();
                } else {
                    //Edit mode
                    DB::connection()->enableQueryLog();
                    $selectedBookingSlots = EventBookingLogs::select('id', 'slot_id')
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?), '%h:%i %p') AS start_time",[
                            $appTimezone,$timezone
                        ])
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(`end_time`, ?, ?), '%h:%i %p') AS end_time",[
                            $appTimezone,$timezone
                        ])
                        ->where('event_booking_logs.id', $bookingLog->id)
                        ->get()->first();

                    $bookedSlots = EventBookingLogs::select(
                        'event_booking_logs.id',
                        'event_booking_logs.slot_id'
                    )
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), ?, ?), '%Y-%m-%d %h:%i %p') AS start_time",[
                            $appTimezone,$timezone
                        ])
                        ->selectRaw("DATE_FORMAT(CONVERT_TZ(date_add(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), interval TIME_TO_SEC(events.duration)/60 minute ), ?, ?), '%Y-%m-%d %h:%i %p') AS end_time",[
                            $appTimezone,$timezone
                        ])
                        ->leftJoin('events', 'events.id', '=', 'event_booking_logs.event_id')
                        ->where('event_booking_logs.id', '!=', $bookingLog->id)
                        ->whereIn('event_booking_logs.status', ['4', '6'])
                        ->get()->toArray();
                }
                if (!empty($splitData['splitslots'])) {
                    $slotsData['slots'] .= view('admin.marketplace.booking-page-slots-cronofy-block', [
                        'slot'                 => $slot,
                        'splitData'            => $splitData,
                        'disableSlot'          => $disableSlot,
                        'bookingLog'           => $bookingLog,
                        'bookingLogExists'     => !empty($bookingLogExists) ? $bookingLogExists : '',
                        'selectedBookingSlots' => !empty($selectedBookingSlots) ? $selectedBookingSlots : '',
                        'bookedSlots'          => !empty($bookedSlots) ? $bookedSlots : '',
                        'eventDuration'        => convertToHoursMins(timeToDecimal($event->duration), false, '%s %s'),
                    ])->render();
                }
            });
            $slotsData['status'] = true;
        }

        if (!is_null($this->capacity)) {
            $coUsersId = UserTeam::select('user_team.user_id')
                ->where('user_team.company_id', $payload['company'])
                ->get()->pluck('user_id')->toArray();
            $usersCount = User::select('users.id')
                ->whereIn('users.id', $coUsersId)
                ->where("users.is_blocked", false)
                ->where(function ($query) use ($coType) {
                    if (in_array($coType, ['rsa', 'rca'])) {
                        $query->where("users.can_access_portal", true);
                    } elseif (in_array($coType, ['zca'])) {
                        $query->where("users.can_access_app", true);
                    }
                })
                ->count('users.id');
            $slotsData['coUsersCount'] = (int) (!empty($usersCount) ? $usersCount : 0);
        }

        return $slotsData;
    }

    /**
     * To confirm an event for company
     *
     * @param array $payload
     * @return boolean
     */
    public function confirmBooking($payload)
    {
        $loginUser      = auth()->user();
        $appTimezone    = config('app.timezone');
        $bookingCompany = Company::find($payload['company']);
        $timezone       = CompanyLocation::select('timezone')
            ->where('company_id', $payload['company'])
            ->where('default', 1)->first();

        $status      = (in_array($payload['companyType'], ['zca', 'rsa', 'rca']) && $payload['createdCompany'] != null) ? '4' : '6';
        $timeFrom    = $payload['timeFrom'];
        $timeTo      = $payload['timeTo'];
        $timezone    = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
        $bookingDate = Carbon::parse("{$payload['date']} {$timeFrom}");
        $endTime     = Carbon::parse("{$payload['date']} {$timeTo}");
        $meta        = [];

        $uid         = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';

        $user = User::select('id', 'first_name', 'last_name', 'timezone', 'email')->with(['company' => function ($query) {
            $query->select('companies.id');
        }])->find($payload['ws_user']);

        $slot             = new \stdClass();
        $slot->user_id    = $user->id;
        $slot->company_id = $bookingCompany->id;
        $slot->user       = $user;
        $days             = config('zevolifesettings.hc_availability_days');
        $key              = array_search(Carbon::parse("{$payload['date']}")->format('l'), $days);
        // $meta             = [
        //     "presenter"  => $slot->user->full_name,
        //     "start_time" => $bookingDate->toTimeString(),
        //     "end_time"   => $endTime->toTimeString(),
        //     "day"        => $key,
        //     "timezone"   => $slot->user->timezone,
        //     //"uid"        => $uid,
        // ];

        $bookingDetails = [
            'event_id'           => $this->id,
            'company_id'         => $payload['company'],
            'scheduling_id'      => $payload['schedulingId'],
            'presenter_user_id'  => $slot->user_id,
            'booking_date'       => $bookingDate->toDateString(),
            'start_time'         => $bookingDate->toTimeString(),
            'end_time'           => $endTime->toTimeString(),
            'notes'              => $payload['notes'],
            'email_notes'        => (!empty($payload['email_notes']) ? $payload['email_notes'] : null),
            'register_all_users' => (!empty($payload['register_all_users']) ? true : false),
            'is_csat'            => $this->is_csat,
            //'meta'               => $meta,
            'status'             => $status,
            'description'        => !empty($payload['description']) ? $payload['description'] : strip_tags($this->description),
            'registration_date'  => Carbon::parse($payload['registrationdate'], $loginUser->timezone)->setTimezone($appTimezone)->toDateTimeString(),
            'company_type'       => (!empty($payload['company_type']) ? $payload['company_type'] : 'Zevo'),
            'is_complementary'   => (!empty($payload['is_complementary']) ? true : false),
            'add_to_story'       => (!empty($payload['add_to_story']) ? true : false),
            'video_link'         => (!empty($payload['video_link']) ? $payload['video_link'] : null),
            'timezone'           => (!empty($payload['bookingTimezone']) ? $payload['bookingTimezone'] : null),
        ];

        if (isset($payload['capacity']) && !empty($payload['capacity'])) {
            $bookingDetails['capacity_log'] = $payload['capacity'];
        }

        if (isset($payload['eventbooking_id']) && $payload['eventbooking_id'] > 0) {
            $eventEmails = [
                'previousBookingLogId' => $payload['eventbooking_id'],
                'currentBookingLogId'  => $this->id,
                'bookingDetails'       => $bookingDetails,
                'currentSlotUser'      => $slot,
                'ccEmails'             => $payload['email'],
            ];
            $this->sendEventEmails($eventEmails);
            return 1;
        } else {
            $meta   = [
                "presenter"  => $slot->user->full_name,
                "start_time" => $bookingDate->toTimeString(),
                "end_time"   => $endTime->toTimeString(),
                "day"        => $key,
                "timezone"   => $slot->user->timezone,
                "uid"        => $uid,
            ];
            $bookingDetails['meta'] = $meta;
            $bookingRecord  = $this->booking()->create($bookingDetails);
            $companyDetails = Company::find($payload['company']);
            $creditData     = [
                'credits'         => $companyDetails->credits- 1,
                'on_hold_credits' => $companyDetails->on_hold_credits + 1,
            ];
            $companyDetails->update($creditData);

            $creditLogData = [
                'company_id'        => $payload['company'],
                'user_name'         => $loginUser->full_name,
                'credits'           => 1,
                'notes'             => "Event " . $this->name." booked",
                'type'              => 'On Hold',
                'available_credits' => $creditData['credits'],
            ];
            CompanyWiseCredit::insert($creditLogData);

            $eventEmails   = [
                'currentBookingLogId' => $bookingRecord->id,
                'bookingDetails'      => $bookingDetails,
                'ccEmails'            => $payload['email'],
            ];
            $this->sendEventEmails($eventEmails);
        }
        return 1;
    }

    /**
     * Send emails to admin, presenters, zendesk and cc emails
     * @param array $data
     * @return boolean
     */
    public function sendEventEmails($data)
    {
        $loginUser   = auth()->user();
        $role        = getUserRole($loginUser);
        $appTimezone = config('app.timezone');
        $utcNow      = now($appTimezone);
        $appEnvironment = app()->environment();
        if ($appEnvironment == 'production') {
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.production.email');
        } elseif ($appEnvironment == 'uat') {
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.uat.email');
        } elseif ($appEnvironment == 'local') {
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.local.email');
        } elseif ($appEnvironment == 'dev') {
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.dev.email');
        } else {
            $zendeskEmail = config('zevolifesettings.mail-zendesk-event.qa.email');
        }
        $eventStatus = config('zevolifesettings.event_listing_status');

        if (!empty($data['previousBookingLogId'])) {
            $appName = config('app.name');

            $oldBookingLog = EventBookingLogs::select(
                'event_booking_logs.presenter_user_id',
                'event_booking_logs.meta',
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%M %d, %Y %h:%i %p') AS bookingDate"),
            )
                ->where('event_booking_logs.id', $data['previousBookingLogId'])->first();

            $meta = (!empty($oldBookingLog->meta) ? $oldBookingLog->meta : null);
            $uid  = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
            $previousPresenter = User::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')
                ->where('id', $oldBookingLog->presenter_user_id)->first();

            $hasPresenterChanged = (!is_null($previousPresenter) && ($previousPresenter->id != $data['currentSlotUser']->user_id));
            if ($hasPresenterChanged) {
                $data['bookingDetails']['old_presenter_user_id'] = $previousPresenter->id;
                $oldUid = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
                $uid    = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';
            }

            $bookingRecord = $this->booking()->updateOrCreate([
                'id'       => $data['previousBookingLogId'],
                'event_id' => $data['currentBookingLogId'],
            ], $data['bookingDetails']);

            //Update custom emails
            $customEmailData = [];
            if (!empty($data['ccEmails'])) {
                foreach ($data['ccEmails'] as $emailIndex => $emailValue) {
                    $customEmailData[$emailIndex]['email'] = $emailValue;
                }

                $oldEmails = EventBookingEmails::
                    where('event_booking_log_id', $data['previousBookingLogId'])
                    ->count();

                if ($oldEmails >= 0) {
                    EventBookingEmails::where('event_booking_log_id', $data['previousBookingLogId'])
                        ->delete();
                }
                foreach ($customEmailData as $customEmail) {
                    EventBookingEmails::create([
                        'email'                => $customEmail['email'],
                        'event_booking_log_id' => $data['previousBookingLogId'],
                    ]);
                }
            }

            $currentBookingLog = EventBookingLogs::select(
                'event_booking_logs.id',
                'event_booking_logs.meta',
                'event_booking_logs.company_id',
                'event_booking_logs.event_id',
                'event_booking_logs.presenter_user_id',
                'event_booking_logs.status',
                'event_booking_logs.presenter_user_id as preseterUserId',
                'users.timezone', 
                'events.description', 
                'events.duration', 
                'events.name as eventName', 
                'users.email', 
                'users.timezone', 
                \DB::raw("CONCAT(users.first_name,' ',users.last_name)  as presenterName"), 
                "users.email  as presenterEmail", 
                'companies.name as companyName', 
                'event_booking_logs.email_notes',
                'event_booking_logs.timezone as cronofyTimezone',
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%M %d, %Y %h:%i %p') AS bookingDate"),
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time), '%M %d, %Y %h:%i %p') AS bookingEndTime"),
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%Y-%m-%d %h:%i %p') AS bookingDateUTC")
            )
                ->leftJoin('events', 'events.id', '=', 'event_booking_logs.event_id')
                ->leftJoin('users', 'users.id', '=', 'event_booking_logs.presenter_user_id')
                ->leftJoin('companies', 'companies.id', '=', 'event_booking_logs.company_id')
                ->where('event_booking_logs.id', $data['previousBookingLogId'])->first();
            
            $ccEmails    = EventBookingEmails::select('email')->where('event_booking_log_id', $currentBookingLog->id)->whereNotNull('email')->get()->pluck('email')->toArray();
            array_push($ccEmails, $zendeskEmail);
            // if previous presenter and new  presenter both are different then send removed email to previous presenter and assigned email to new presenter
            if ($hasPresenterChanged) {
                if (!is_null($previousPresenter)) {
                    $sequenceLog = $currentBookingLog->inviteSequence()->select('users.id')->where('user_id', $previousPresenter->id)->first();
                    $sequence    = 0;
                    if (is_null($sequenceLog)) {
                        // record not exist adding
                        $currentBookingLog->inviteSequence()->attach([$previousPresenter->id]);
                        $sequence = 0;
                    } else {
                        // record exist updating sequence
                        $sequence = ($sequenceLog->pivot->sequence + 1);
                        $sequenceLog->pivot->update([
                            'sequence' => $sequence,
                        ]);
                    }

                    $previousPresenterTimeZone       = (!empty($previousPresenter->timezone) ? $previousPresenter->timezone : $appTimezone);
                    $previousPresenterBookingDate    = Carbon::parse("{$oldBookingLog->bookingDate}", $appTimezone)->setTimezone($previousPresenterTimeZone);

                    $presenterName = $previousPresenter->full_name;
                    $inviteTitle   = $currentBookingLog->eventName;
                    $getCronofyEmail = CronofyCalendar::select('profile_name')->where('user_id', $previousPresenter->id)->where('primary',true)->first();
                    $startTime       = Carbon::parse("{$currentBookingLog->bookingDate}", config('app.timezone'));
                    $endTime         = Carbon::parse("{$currentBookingLog->bookingEndTime}", config('app.timezone'));
                   
                    // prepare iCal invite array for previous presenter
                    $presenterEmailData['subject'] = "{$inviteTitle} - Event Cancelled";
                    $presenterEmailData['message'] = "Hi {$previousPresenter->first_name},<br/><br/>This is to notify you that {$inviteTitle} with {$currentBookingLog->companyName} scheduled on {$previousPresenterBookingDate->format('M d, Y')} at {$previousPresenterBookingDate->format('h:i A')} has been cancelled. <br/><br/>More details will follow from Zevo Health.";

                    if(!empty($getCronofyEmail) && $getCronofyEmail->profile_name != $previousPresenter->email){
                        $presenterEmailData['iCal']  = generateiCal([
                            'uid'         => $oldUid,
                            'appName'     => $appName,
                            'inviteTitle' => $currentBookingLog->eventName,
                            'description' => $currentBookingLog->description,
                            'timezone'    => $previousPresenter->timezone,
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $previousPresenter->full_name,
                            'orgEamil'    => $getCronofyEmail->profile_name,
                            'userEmail'   => $previousPresenter->email,
                            'sequence'    => 0,
                        ],'cancelled');
                    }

                    event(new EventUpdatedEvent($previousPresenter, $presenterEmailData));

                    // attach new presenter to sequence
                    $bookingRecord->inviteSequence()->attach([$previousPresenter->presenter_user_id]);
                }
                if ($data['currentSlotUser']->user) {
                    // send event registered email to new presenter along with ical event
                    $presenterTimeZone  = (!empty($currentBookingLog->timezone) ? $currentBookingLog->timezone : $appTimezone);
                    $presenterStartTime = Carbon::parse("{$currentBookingLog->bookingDate}", $appTimezone)->setTimezone($currentBookingLog->timezone);
                    $presenterName      = $currentBookingLog->presenterName;
                    $inviteTitle        = $currentBookingLog->eventName;
                    $description        = strip_tags($currentBookingLog->description);
                    $startTime       = Carbon::parse("{$currentBookingLog->bookingDate}", config('app.timezone'));
                    $endTime         = Carbon::parse("{$currentBookingLog->bookingEndTime}", config('app.timezone'));
                    $getCronofyEmail = CronofyCalendar::select('profile_name')->where('user_id', $currentBookingLog->preseterUserId)->where('primary',true)->first();

                    $presenterEmailData = [
                        'company'        => (!empty($currentBookingLog->company_id) ? $currentBookingLog->company_id : null),
                        'email'          => $currentBookingLog->email,
                        'eventBookingId' => $bookingRecord->id,
                        'eventName'      => $currentBookingLog->eventName,
                        'type'           => 'presenter',
                        'bookingDate'    => $presenterStartTime->format('M d, Y h:i A'),
                        'companyName'    => $currentBookingLog->companyName,
                        'presenterName'  => $presenterName,
                        'iCal'           => generateiCal([
                            'uid'         => $uid,
                            'appName'     => $appName,
                            'inviteTitle' => $currentBookingLog->eventName,
                            'description' => $currentBookingLog->description,
                            'timezone'    => $currentBookingLog->timezone,
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $currentBookingLog->presenterName,
                            'orgEamil'    => $getCronofyEmail->profile_name,
                            'userEmail'   => $currentBookingLog->email,
                            'sequence'    => 0,
                        ])
                    ];
                    event(new EventBookedEvent($presenterEmailData));
                    // attching presenter to event_invite_sequence_user_logs table for futher sequence reference
                    $bookingRecord->inviteSequence()->attach([$currentBookingLog->presenter_user_id]);
                }

                /**************************************** */
                /*  SEND MAIL TO COMPANY MODERATORS */
                /***************************************** */
                // get moderators of company for event is getting booked and send booked email
                if ($role->slug != 'super_admin') {
                    $companyLocation = CompanyLocation::select('timezone')
                        ->where('company_id', $currentBookingLog->company_id)
                        ->where('default', 1)->first();
                    $timezone                = (!empty($companyLocation->timezone) ? $companyLocation->timezone : $appTimezone);
                    $bookingCompany          = Company::find($currentBookingLog->company_id);
                    $companyModeratorsData   = $bookingCompany->moderators()->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')->where('users.id', $loginUser->id)->get()->toArray();
                    $moderatorsData = [
                        'company'       => $bookingCompany->id,
                        'eventName'     => $currentBookingLog->eventName,
                        'type'          => 'admin',
                        'timezone'      => $currentBookingLog->cronofyTimezone,
                        'companyName'   => $bookingCompany->name,
                        'duration'      => $currentBookingLog->duration,
                        'presenterName' => $currentBookingLog->presenterName,
                        'emailNotes'    => $currentBookingLog->email_notes,
                    ];
                    foreach ($companyModeratorsData as $nUser) {
                        $companyModeratorsDate   = Carbon::parse("{$currentBookingLog->bookingDateUTC}", config('app.timezone'))->setTimezone($currentBookingLog->cronofyTimezone)->format('M d, Y h:i A');
                        $sequenceLog = $currentBookingLog->inviteSequence()->select('users.id')->where('user_id', $nUser['id'])->first();
                        $sequence    = 0;
                        if (is_null($sequenceLog)) {
                            // record not exist adding
                            $pendingEventBookingRecords->inviteSequence()->attach([$nUser['id']]);
                            $sequence = 0;
                        } else {
                            // record exist updating sequence
                            $sequence = ($sequenceLog->pivot->sequence + 1);
                            $sequenceLog->pivot->update([
                                'sequence' => $sequence,
                            ]);
                        }
                        $startTime           = Carbon::parse("{$currentBookingLog->bookingDate}", config('app.timezone'));
                        $endTime             = Carbon::parse("{$currentBookingLog->bookingEndTime}", config('app.timezone'));
                        $moderatorsData['email']         = $nUser['email'];
                        $moderatorsData['userFirstName'] = $nUser['first_name'];
                        $moderatorsData['userName']      = $nUser['first_name'] . ' ' . $nUser['last_name'];
                        $moderatorsData['bookingDate']   = $companyModeratorsDate;
                        $moderatorsData['iCal']          = generateiCal([
                            'uid'         => $uid,
                            'appName'     => $appName,
                            'inviteTitle' => $currentBookingLog->eventName,
                            'description' => $currentBookingLog->description,
                            'timezone'    => $currentBookingLog->cronofyTimezone,
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $currentBookingLog->presenterName,
                            'orgEamil'    => $currentBookingLog->presenterEmail,
                            'userEmail'   => $nUser['email'],
                            'sequence'    => $sequence,
                        ]);
                        
                        event(new EventBookedEvent($moderatorsData));
                        $userData = User::find($nUser['id']);
                        event(new SendEventCancelledEvent($userData, [
                            "subject" => "{$currentBookingLog->eventName} - Event Cancelled",
                            "message" => "Hi {$nUser['first_name']}, <br/><br/> This is to notify you that the planned {$currentBookingLog->eventName} Event with {$previousPresenter->full_name} has been cancelled.",
                            'iCal'    => generateiCal([
                                'uid'         => $oldUid,
                                'appName'     => $appName,
                                'inviteTitle' => $currentBookingLog->eventName,
                                'description' => $currentBookingLog->description,
                                'timezone'    => $nUser['timezone'],
                                'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                                'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                                'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                                'orgName'     => $previousPresenter->full_name,
                                'orgEamil'    => $previousPresenter->email,
                                'userEmail'   => $nUser['email'],
                                'sequence'    => $sequence,
                            ], 'cancelled'),
                        ]));
                    }
                }
                $ccEmailsData = [
                    'company'       => $bookingCompany->id,
                    'eventName'     => $currentBookingLog->eventName,
                    //'type'          => 'ccuser',
                    'emailType'     => 'updated',
                    'eventStatus'   => $eventStatus[$currentBookingLog->status],
                    'timezone'      => $currentBookingLog->cronofyTimezone,
                    'companyName'   => $bookingCompany->name,
                    'duration'      => $currentBookingLog->duration,
                    'presenterName' => $currentBookingLog->presenterName,
                    'emailNotes'    => $currentBookingLog->email_notes,
                ];
                foreach ($ccEmails as $nUser) {
                    $companyModeratorsDate      = Carbon::parse("{$currentBookingLog->bookingDateUTC}", config('app.timezone'))->setTimezone($currentBookingLog->cronofyTimezone)->format('M d, Y h:i A');
                    $ccEmailsData['email']      = $nUser;
                    $ccEmailsData['subject']    = $currentBookingLog->eventName." - Event Updated";
                    $ccEmailsData['bookingDate']= $companyModeratorsDate;
                    event(new EventStatusChangeEvent($ccEmailsData));
                }

                $meta             = [
                    "presenter"  => $currentBookingLog->presenterName,
                    "timezone"   => $currentBookingLog->timezone,
                    "uid"        => $uid,
                ];
    
                $records = EventBookingLogs::where('id', $data['previousBookingLogId'])
                    ->update([
                        'meta'       => $meta
                    ]);

            } else {
                if (!is_null($previousPresenter)) {
                    $sequence    = 0;
                    $sequenceLog = $currentBookingLog->inviteSequence()->select('users.id')->where('user_id', $previousPresenter->id)->first();
                    if (is_null($sequenceLog)) {
                        // record not exist adding
                        $currentBookingLog->inviteSequence()->attach($previousPresenter->id);
                        $sequence = 0;
                    } else {
                        // record exist updating sequence
                        $sequence = ($sequenceLog->pivot->sequence + 1);
                        $sequenceLog->pivot->update([
                            'sequence' => $sequence,
                        ]);
                    }

                    $previousPresenterTimeZone    = (!empty($previousPresenter->timezone) ? $previousPresenter->timezone : $appTimezone);
                    $previousPresenterBookingDate = Carbon::parse("{$currentBookingLog->bookingDate}", $appTimezone)->setTimezone($previousPresenterTimeZone);
                    $presenterName                = $currentBookingLog->presenterName;
                    $inviteTitle                  = $currentBookingLog->eventName;
                    $description                  = strip_tags($currentBookingLog->description);
                    $bookingCompany               = Company::find($currentBookingLog->company_id);
                    $getCronofyEmail = CronofyCalendar::select('profile_name')->where('user_id', $currentBookingLog->preseterUserId)->where('primary',true)->first();
                    $startTime           = Carbon::parse("{$currentBookingLog->bookingDate}", config('app.timezone'));
                    $endTime             = Carbon::parse("{$currentBookingLog->bookingEndTime}", config('app.timezone'));
                   
                    // presenter is same then just send update only
                    $presenterEmailData['subject'] = "{$inviteTitle} - Event Updated";
                    $presenterEmailData['message'] = "Hi {$previousPresenter->first_name},<br/><br/> You have received a booking for a rescheduled {$inviteTitle} with {$bookingCompany->name}. This is scheduled for {$previousPresenterBookingDate->format('M d, Y')} at {$previousPresenterBookingDate->format('h:i A')}. <br/><br/>The Zevo team will be notified. Please contact the team with any questions.";

                    if(!empty($getCronofyEmail) && $getCronofyEmail->profile_name != $previousPresenter->email){
                        $presenterEmailData['iCal']  = generateiCal([
                            'uid'         => $uid,
                            'appName'     => $appName,
                            'inviteTitle' => $currentBookingLog->eventName,
                            'description' => $currentBookingLog->description,
                            'timezone'    => $currentBookingLog->timezone,
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $currentBookingLog->presenterName,
                            'orgEamil'    => $getCronofyEmail->profile_name,
                            'userEmail'   => $currentBookingLog->email,
                            'sequence'    => 0,
                        ]);
                    }
                    event(new EventUpdatedEvent($previousPresenter, $presenterEmailData));
                    /**************************************** */
                    /*  SEND MAIL TO COMPANY MODERATORS */
                    /***************************************** */
                    // get moderators of company for event is getting booked and send booked email
                    if ($role->slug != 'super_admin') {
                        $companyLocation = CompanyLocation::select('timezone')
                            ->where('company_id', $currentBookingLog->company_id)
                            ->where('default', 1)->first();
                        $timezone                = (!empty($companyLocation->timezone) ? $companyLocation->timezone : $appTimezone);
                        $bookingCompany          = Company::find($currentBookingLog->company_id);
                        $companyModeratorsData   = $bookingCompany->moderators()->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')->where('users.id', $loginUser->id)->get()->toArray();
                        $moderatorsData = [
                            'company'       => $bookingCompany->id,
                            'eventName'     => $currentBookingLog->eventName,
                            'type'          => 'admin',
                            'emailType'     => 'update',
                            'timezone'      => $currentBookingLog->cronofyTimezone,
                            'subject'       => $currentBookingLog->eventName . " - Event Updated",
                            'companyName'   => $bookingCompany->name,
                            'duration'      => $currentBookingLog->duration,
                            'presenterName' => $currentBookingLog->presenterName,
                            'emailNotes'    => $currentBookingLog->email_notes,
                        ];
                        foreach ($companyModeratorsData as $key => $nUser) {
                            $companyModeratorsDate   = Carbon::parse("{$currentBookingLog->bookingDateUTC}", config('app.timezone'))->setTimezone($currentBookingLog->cronofyTimezone)->format('M d, Y h:i A');
                            $sequenceLog = $currentBookingLog->inviteSequence()->select('users.id')->where('user_id', $nUser['id'])->first();
                            $sequence    = 0;
                            if (is_null($sequenceLog)) {
                                // record not exist adding
                                $pendingEventBookingRecords->inviteSequence()->attach([$nUser['id']]);
                                $sequence = 0;
                            } else {
                                // record exist updating sequence
                                $sequence = ($sequenceLog->pivot->sequence + 1);
                                $sequenceLog->pivot->update([
                                    'sequence' => $sequence,
                                ]);
                            }
                            $startTime           = Carbon::parse("{$currentBookingLog->bookingDate}", config('app.timezone'));
                            $endTime             = Carbon::parse("{$currentBookingLog->bookingEndTime}", config('app.timezone'));
                            $moderatorsData['email']         = $nUser['email'];
                            $moderatorsData['userFirstName'] = $nUser['first_name'];
                            $moderatorsData['userName']      = $nUser['first_name'] . ' ' . $nUser['last_name'];
                            $moderatorsData['bookingDate']   = $companyModeratorsDate;
        
                            $moderatorsData['iCal']          = generateiCal([
                                'uid'         => $uid,
                                'appName'     => $appName,
                                'inviteTitle' => $currentBookingLog->eventName,
                                'description' => $currentBookingLog->description,
                                'timezone'    => $nUser['timezone'],
                                'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                                'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                                'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                                'orgName'     => $currentBookingLog->presenterName,
                                'orgEamil'    => $currentBookingLog->presenterEmail,
                                'userEmail'   => $nUser['email'],
                                'sequence'    => $sequence,
                            ]);
                            event(new EventBookedEvent($moderatorsData));
                        }
                    }

                    /**************************************** */
                    /*  SEND MAIL TO CC Users */
                    /***************************************** */
                    // get moderators of company for event is getting booked and send booked email
                    $ccEmailsData = [
                        'company'       => $bookingCompany->id,
                        'eventName'     => $currentBookingLog->eventName,
                        //'type'          => 'ccuser',
                        'emailType'     => 'updated',
                        'eventStatus'   => $eventStatus[$currentBookingLog->status],
                        'timezone'      => $currentBookingLog->cronofyTimezone,
                        'subject'       => $currentBookingLog->eventName . " - Event Updated",
                        'companyName'   => $bookingCompany->name,
                        'duration'      => $currentBookingLog->duration,
                        'presenterName' => $currentBookingLog->presenterName,
                        'emailNotes'    => $currentBookingLog->email_notes,
                    ];
                    foreach ($ccEmails as $nUser) {
                        $companyModeratorsDate   = Carbon::parse("{$currentBookingLog->bookingDateUTC}", config('app.timezone'))->setTimezone($currentBookingLog->cronofyTimezone)->format('M d, Y h:i A');
                        $ccEmailsData['email']         = $nUser;
                        $ccEmailsData['bookingDate']   = $companyModeratorsDate;
                        event(new EventStatusChangeEvent($ccEmailsData));
                    }
                    // Completed CC user emails
                }
            }
            // $ccEmails    = EventBookingEmails::select('email')->where('event_booking_log_id', $currentBookingLog->id)->whereNotNull('email')->get()->pluck('email')->toArray();
            // array_push($ccEmails, $zendeskEmail);
            // $statusChangeEmailData = [
            //     'company'        => (!empty($currentBookingLog->company_id) ? $currentBookingLog->company_id : null),
            //     'eventBookingId' => $currentBookingLog->id,
            //     'eventName'      => $currentBookingLog->eventName,
            //     'duration'       => $currentBookingLog->duration,
            //     'messageType'    => 'eventupdate',
            //     'subject'        => "{$currentBookingLog->eventName} - Event Updated",
            //     'presenterName'  => $currentBookingLog->presenterName,
            //     'bookingDate'    => $currentBookingLog->bookingDate,
            //     'companyName'    => $currentBookingLog->companyName,
            // ];
            // dispatch(new SendEventStatusChangeEmailJob($ccEmails, $statusChangeEmailData));
        } else {
            //Add custom emails
            $customCCEmailsData = [];
            if (!empty($data['ccEmails'])) {
                foreach ($data['ccEmails'] as $emailIndex => $emailValue) {
                    $customCCEmailsData[$emailIndex]['email'] = $emailValue;
                }

                foreach ($customCCEmailsData as $customCCEmailIndex => $customCCEmailValue) {
                    EventBookingEmails::create([
                        'email'                => $customCCEmailValue['email'],
                        'event_booking_log_id' => $data['currentBookingLogId'],
                    ]);
                }
            }

            $pendingEventBookingRecords = EventBookingLogs::select(
                'event_booking_logs.id',
                'event_booking_logs.company_id',
                'event_booking_logs.event_id',
                'event_booking_logs.presenter_user_id',
                'event_booking_logs.status',
                'event_booking_logs.email_notes',
                'event_booking_logs.meta',
                'users.timezone', 
                'events.description', 
                'events.duration', 
                'events.name as eventName', 
                'users.email', 
                'users.timezone', 
                \DB::raw("CONCAT(users.first_name,' ',users.last_name)  as presenterName"), 
                "users.email  as presenterEmail", 
                "users.id as presenterId",
                'companies.name as companyName', 
                'event_booking_logs.timezone as cronofyTimezone',
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%M %d, %Y %h:%i %p') AS bookingDate"), 
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time), '%M %d, %Y %h:%i %p') AS bookingEndTime"), 
                \DB::raw("DATE_FORMAT(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '%Y-%m-%d %h:%i %p') AS bookingDateUTC")
            )
            ->leftJoin('events', 'events.id', '=', 'event_booking_logs.event_id')
            ->leftJoin('users', 'users.id', '=', 'event_booking_logs.presenter_user_id')
            ->leftJoin('companies', 'companies.id', '=', 'event_booking_logs.company_id')
            ->where('event_booking_logs.status', '6')
            ->where('event_booking_logs.id', $data['currentBookingLogId'])
            ->first();

            if (!empty($pendingEventBookingRecords) && $pendingEventBookingRecords->status == '6') {
                $ccEmails    = EventBookingEmails::select('email')->where('event_booking_log_id', $pendingEventBookingRecords->id)->whereNotNull('email')->get()->pluck('email')->toArray();
                $meta                 = (!empty($pendingEventBookingRecords->meta) ? $pendingEventBookingRecords->meta : null);
                $uid                  = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');    
                $inviteTitle = $pendingEventBookingRecords->eventName;
                $description = strip_tags($pendingEventBookingRecords->description);
                $appName     = config('app.name');

                $bookingDateTime = Carbon::parse("{$pendingEventBookingRecords->bookingDate}", config('app.timezone'))->setTimezone($pendingEventBookingRecords->timezone)->format('M d, Y h:i A');
                $getCronofyEmail = CronofyCalendar::select('profile_name')->where('user_id', $pendingEventBookingRecords->presenterId)->where('primary',true)->first();
                
                $presenterEmailData = [
                    'company'        => (!empty($pendingEventBookingRecords->company_id) ? $pendingEventBookingRecords->company_id : null),
                    'email'          => $pendingEventBookingRecords->email,
                    'eventBookingId' => $pendingEventBookingRecords->id,
                    'eventName'      => $pendingEventBookingRecords->eventName,
                    'duration'       => $pendingEventBookingRecords->duration,
                    'presenterName'  => $pendingEventBookingRecords->presenterName,
                    'type'           => 'presenter',
                    'bookingDate'    => $bookingDateTime,
                    'companyName'    => $pendingEventBookingRecords->companyName,
                ];

                //If cronofy email and user email not match then send iCal to WBS
                if(!empty($getCronofyEmail) && $getCronofyEmail->profile_name != $pendingEventBookingRecords->email){
                    $startEventTime = Carbon::parse("{$pendingEventBookingRecords->bookingDate}", config('app.timezone'));
                    $endEventTime   = Carbon::parse("{$pendingEventBookingRecords->bookingEndTime}", config('app.timezone'));
                
                    $presenterEmailData['iCal'] = generateiCal([
                        'uid'         => $uid,
                        'appName'     => $appName,
                        'inviteTitle' => $pendingEventBookingRecords->eventName,
                        'description' => $pendingEventBookingRecords->description,
                        'timezone'    => $pendingEventBookingRecords->timezone,
                        'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startEventTime)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endEventTime)->format('Ymd\THis\Z'),
                        'orgName'     => $pendingEventBookingRecords->presenterName,
                        'orgEamil'    => $getCronofyEmail->profile_name,
                        'userEmail'   => $pendingEventBookingRecords->email,
                        'sequence'    => 0,
                    ]);
                }
                event(new EventBookedEvent($presenterEmailData));
                /**************************************** */
                /*  SEND STATUS CHANGE MAIL TO STATIC MAIL ZENDESK AND CC EMAILS */
                /***************************************** */
                // array_push($ccEmails, $zendeskEmail);
                // $statusChangeEmailData = [
                //     'company'        => (!empty($pendingEventBookingRecords->company_id) ? $pendingEventBookingRecords->company_id : null),
                //     'eventBookingId' => $pendingEventBookingRecords->id,
                //     'eventName'      => $pendingEventBookingRecords->eventName,
                //     'duration'       => $pendingEventBookingRecords->duration,
                //     'presenterName'  => $pendingEventBookingRecords->presenterName,
                //     'messageType'    => 'booked',
                //     'bookingDate'    => $pendingEventBookingRecords->bookingDate,
                //     'companyName'    => $pendingEventBookingRecords->companyName,
                //     'eventStatus'    => 'Paused',
                // ];
                // dispatch(new SendEventStatusChangeEmailJob($ccEmails, $statusChangeEmailData));

                /**************************************** */
                /*  SEND MAIL TO COMPANY MODERATORS */
                /***************************************** */
                // get moderators of company for event is getting booked and send booked email
                if ($role->slug != 'super_admin') {
                    $companyLocation = CompanyLocation::select('timezone')
                    ->where('company_id', $pendingEventBookingRecords->company_id)
                    ->where('default', 1)->first();
        
                    $timezone                = (!empty($companyLocation->timezone) ? $companyLocation->timezone : $appTimezone);
                    $bookingCompany          = Company::find($pendingEventBookingRecords->company_id);
                    $companyModeratorsData   = $bookingCompany->moderators()->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')->where('users.id', $loginUser->id)->get()->toArray();

                    $sequence = 0;
                    $moderatorsData = [
                        'company'       => $bookingCompany->id,
                        'eventName'     => $pendingEventBookingRecords->eventName,
                        'type'          => 'admin',
                        'timezone'      => $pendingEventBookingRecords->cronofyTimezone,
                        'companyName'   => $bookingCompany->name,
                        'duration'      => $pendingEventBookingRecords->duration,
                        'presenterName' => $pendingEventBookingRecords->presenterName,
                        'emailNotes'    => $pendingEventBookingRecords->email_notes,
                    ];
                    foreach ($companyModeratorsData as $key => $nUser) {
                        $companyModeratorsDate   = Carbon::parse("{$pendingEventBookingRecords->bookingDateUTC}", config('app.timezone'))->setTimezone($pendingEventBookingRecords->cronofyTimezone)->format('M d, Y h:i A');
                        $sequenceLog = $pendingEventBookingRecords->inviteSequence()->select('users.id')->where('user_id', $nUser['id'])->first();
                        $sequence    = 0;
                        if (is_null($sequenceLog)) {
                            // record not exist adding
                            $pendingEventBookingRecords->inviteSequence()->attach([$nUser['id']]);
                            $sequence = 0;
                        } else {
                            // record exist updating sequence
                            $sequence = ($sequenceLog->pivot->sequence + 1);
                            $sequenceLog->pivot->update([
                                'sequence' => $sequence,
                            ]);
                        }
                        $startTime           = Carbon::parse("{$pendingEventBookingRecords->bookingDate}", config('app.timezone'));
                        $endTime             = Carbon::parse("{$pendingEventBookingRecords->bookingEndTime}", config('app.timezone'));
                        $moderatorsData['email']         = $nUser['email'];
                        $moderatorsData['userFirstName'] = $nUser['first_name'];
                        $moderatorsData['userName']      = $nUser['first_name'] . ' ' . $nUser['last_name'];
                        $moderatorsData['bookingDate']   = $companyModeratorsDate;

                        $moderatorsData['iCal']          = generateiCal([
                            'uid'         => $uid,
                            'appName'     => $appName,
                            'inviteTitle' => $pendingEventBookingRecords->eventName,
                            'description' => $pendingEventBookingRecords->description,
                            'timezone'    => $nUser['timezone'],
                            'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'orgName'     => $pendingEventBookingRecords->presenterName,
                            'orgEamil'    => $pendingEventBookingRecords->presenterEmail,
                            'userEmail'   => $nUser['email'],
                            'sequence'    => $sequence,
                        ]);
                        event(new EventBookedEvent($moderatorsData));
                    }
                }

                /**************************************** */
                /*  SEND MAIL TO CC Users */
                /***************************************** */
                // get moderators of company for event is getting booked and send booked email
                $companyLocation = CompanyLocation::select('timezone')
                ->where('company_id', $pendingEventBookingRecords->company_id)
                ->where('default', 1)->first();
    
                $timezone                = (!empty($companyLocation->timezone) ? $companyLocation->timezone : $appTimezone);
                $bookingCompany          = Company::find($pendingEventBookingRecords->company_id);

                $ccEmailsData = [
                    'company'       => $bookingCompany->id,
                    'eventName'     => $pendingEventBookingRecords->eventName,
                    //'type'          => 'ccuser',
                    'emailType'     => 'booked',
                    'eventStatus'   => 'Paused',
                    'timezone'      => $pendingEventBookingRecords->cronofyTimezone,
                    'companyName'   => $bookingCompany->name,
                    'duration'      => $pendingEventBookingRecords->duration,
                    'presenterName' => $pendingEventBookingRecords->presenterName,
                    'emailNotes'    => $pendingEventBookingRecords->email_notes,

                ];
                array_push($ccEmails, $zendeskEmail);
                foreach ($ccEmails as $nUser) {
                    $companyModeratorsDate   = Carbon::parse("{$pendingEventBookingRecords->bookingDateUTC}", config('app.timezone'))->setTimezone($pendingEventBookingRecords->cronofyTimezone)->format('M d, Y h:i A');
                    $ccEmailsData['email']         = $nUser;
                    $ccEmailsData['bookingDate']   = $companyModeratorsDate;
                    event(new EventStatusChangeEvent($ccEmailsData));
                }
                // Completed CC user emails
            }
        }
    }

    /**
     * To update an booked event for company
     *
     * @param EventBookingLogs $bookingLog
     * @param array $payload
     * @return boolean
     */
    public function updateBookedEvent($bookingLog, $payload)
    {
        $event             = $this;
        $user              = auth()->user();
        $role              = getUserRole($user);
        $oldBookingDetails = collect($bookingLog->toArray());
        $appTimezone       = config('app.timezone');
        $eventCompanyId    = $this->company_id;
        $bookingCompany    = Company::find($bookingLog->company_id);
        $timezone          = CompanyLocation::select('timezone')
            ->where('company_id', $bookingLog->company_id)
            ->where('default', 1)->first();
        $timezone = (!empty($timezone->timezone) ? $timezone->timezone : $appTimezone);
        $meta     = $bookingLog->meta;

        // data for ical generation
        $utcNow      = now($appTimezone);
        $uid         = (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
        $description = strip_tags($this->description);
        $inviteTitle = $this->name;
        $appName     = config('app.name');

        $user = User::select('id', 'first_name', 'last_name', 'timezone', 'email')->with(['company' => function ($query) {
            $query->select('companies.id');
        }])->find($bookingLog->presenter_user_id);

        $slot             = new \stdClass();
        $slot->user_id    = $user->id;
        $slot->company_id = $bookingCompany->id;
        $slot->user       = $user;

        // update meta as per new bookings
        $meta->presenter = $slot->user->full_name;
        $meta->timezone  = $slot->user->timezone;

        // update booking log details as per new booking
        $bookingLog->meta              = $meta;
        $bookingLog->slot_id           = $payload['selectedslot'];
        $bookingLog->presenter_user_id = $slot->user_id;
        $bookingLog->notes             = $payload['notes'];
        $bookingLog->description       = $payload['description'];
        $bookingLog->company_type      = (!empty($payload['company_type']) ? $payload['company_type'] : 'Zevo');
        $bookingLog->video_link        = (!empty($payload['video_link']) ? $payload['video_link'] : null);
        if (!empty($payload['email_notes'])) {
            $bookingLog->email_notes = $payload['email_notes'];
        }

        $bookingRecord = false;

        if ($bookingLog->save()) {
            $bookingRecord = $bookingLog;
        }

        if ($bookingRecord) {
            //Update custom emails
            $customEmailData = [];
            if (!empty($payload['email'])) {
                foreach ($payload['email'] as $emailIndex => $emailValue) {
                    $customEmailData[$emailIndex]['email'] = $emailValue;
                }
                $oldEmails = $bookingLog
                    ->eventBookingEmails()
                    ->where('event_booking_log_id', $bookingLog->id)
                    ->count();
                if ($oldEmails >= 0) {
                    $bookingLog
                        ->eventBookingEmails()
                        ->where('event_booking_log_id', $bookingLog->id)
                        ->delete();
                }
                foreach ($customEmailData as $customEmail) {
                    $bookingLog->eventBookingEmails()->create([
                        'email' => $customEmail['email'],
                    ]);
                }
            }
            // previous presenter details
            $previousPresenter = User::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.timezone')
                ->where('id', $oldBookingDetails['presenter_user_id'])->first();
            $hasPresenterChanged = (!is_null($previousPresenter) && ($previousPresenter->id != $bookingLog->presenter_user_id));
            $hasScheduleChanged  = !($oldBookingDetails['booking_date'] == $bookingLog->booking_date &&
                $oldBookingDetails['start_time'] == $bookingLog->start_time &&
                $oldBookingDetails['end_time'] == $bookingLog->end_time);

            if ($hasPresenterChanged || $hasScheduleChanged) {
                // if previous presenter and new  presenter both are different then send removed email to previous presenter and assigned email to new presenter
                if ($hasPresenterChanged) {
                    if (!is_null($previousPresenter)) {
                        $sequenceLog = $bookingRecord->inviteSequence()->select('users.id')->where('user_id', $previousPresenter->id)->first();
                        $sequence    = 0;
                        if (is_null($sequenceLog)) {
                            // record not exist adding
                            $bookingRecord->inviteSequence()->attach([$previousPresenter->id]);
                            $sequence = 0;
                        } else {
                            // record exist updating sequence
                            $sequence = ($sequenceLog->pivot->sequence + 1);
                            $sequenceLog->pivot->update([
                                'sequence' => $sequence,
                            ]);
                        }
                        $previousPresenterTimeZone  = (!empty($previousPresenter->timezone) ? $previousPresenter->timezone : $appTimezone);
                        $previousPresenterStartTime = Carbon::parse("{$oldBookingDetails['booking_date']} {$oldBookingDetails['start_time']}", $appTimezone)->setTimezone($previousPresenterTimeZone)->format('M d, Y h:i A');

                        // prepare iCal invite array for previous presenter
                        event(new EventUpdatedEvent($previousPresenter, [
                            "subject" => "{$inviteTitle} - Event Cancelled",
                            "message" => "Hi {$previousPresenter->first_name},<br/><br/>This is to notify you that {$inviteTitle} with {$bookingCompany->name} scheduled on {$previousPresenterStartTime} has been cancelled. <br/><br/>More details will follow from Zevo Health.",
                            "iCal"    => generateiCal([
                                'uid'         => $uid,
                                'appName'     => $appName,
                                'inviteTitle' => $inviteTitle,
                                'description' => "You have been removed from the {$inviteTitle} event.",
                                'timezone'    => $previousPresenterTimeZone,
                                'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                                'startTime'   => Carbon::parse($bookingDate)->format('Ymd\THis\Z'),
                                'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                                'orgName'     => $previousPresenter->full_name,
                                'orgEamil'    => $previousPresenter->email,
                                'sequence'    => $sequence,
                            ], 'cancelled'),
                        ]));

                        // attach new presenter to sequence
                        $bookingRecord->inviteSequence()->attach([$slot->user_id]);
                    }
                } else {
                    if (!is_null($previousPresenter)) {
                        $sequence    = 0;
                        $sequenceLog = $bookingRecord->inviteSequence()->select('users.id')->where('user_id', $previousPresenter->id)->first();
                        if (is_null($sequenceLog)) {
                            // record not exist adding
                            $bookingRecord->inviteSequence()->attach($previousPresenter->id);
                            $sequence = 0;
                        } else {
                            // record exist updating sequence
                            $sequence = ($sequenceLog->pivot->sequence + 1);
                            $sequenceLog->pivot->update([
                                'sequence' => $sequence,
                            ]);
                        }
                        $startTimeDuration = date("Y-m-d H:i:s", strtotime($bookingDate));
                        $endTimeDuration   = date("Y-m-d H:i:s", strtotime($endTime));
                        $duration          = Carbon::parse($endTimeDuration)->diffInMinutes($startTimeDuration);

                        $previousPresenterTimeZone    = (!empty($previousPresenter->timezone) ? $previousPresenter->timezone : $appTimezone);
                        $previousPresenterBookingDate = Carbon::parse("{$bookingDate}", $appTimezone)->setTimezone($previousPresenterTimeZone);

                        // presenter is same then just send update only
                        event(new EventUpdatedEvent($previousPresenter, [
                            "subject"       => "{$inviteTitle} - Event Updated",
                            "message"       => "Hi {$presenterName},<br/><br/> This is to notify you that details of {$inviteTitle} has been updated. Please make note of the changes below.",
                            "eventName"     => $inviteTitle,
                            'bookingDate'   => $previousPresenterBookingDate->format('M d, Y'),
                            'bookingTime'   => $previousPresenterBookingDate->format('h:i A'),
                            'presenterName' => $presenterName,
                            'duration'      => $duration . " Minutes",
                            "iCal"          => generateiCal([
                                'uid'         => $uid,
                                'appName'     => $appName,
                                'inviteTitle' => $inviteTitle,
                                'description' => $description,
                                'timezone'    => $previousPresenterTimeZone,
                                'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                                'startTime'   => Carbon::parse($bookingDate)->format('Ymd\THis\Z'),
                                'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                                'orgName'     => $presenterName,
                                'orgEamil'    => $presenterEmail,
                                'sequence'    => $sequence,
                            ]),
                        ]));
                    }
                }
            }

            return 1;
        }
        return 0;
    }
}
