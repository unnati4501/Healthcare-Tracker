<?php

namespace App\Models;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class CronofySessionAttachments extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronofy_sessions_attachments';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cronofy_schedule_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta'              => 'object',
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
     * "BelongsTo" relation to `eap_tickets` table
     * via `ticket_id` field.
     *
     * @return BelongsTo
     */
    public function cronofySchedule(): BelongsTo
    {
        return $this->belongsTo(CronofySchedule::class, 'cronofy_schedule_id');
    }

    /**
     * store attachment files in bulk.
     * @param payload
     * @return boolean
     */
    public function storeAttachments($cronofySchedule, array $payload)
    {
        foreach ($payload['attachments'] as $file) {
            $record = $this->create([
                'cronofy_schedule_id' => $cronofySchedule->id,
            ]);

            if ($record) {
                if (isset($file) && !empty($file)) {
                    $name = $record->id . '_' . \time();
                    $record
                        ->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($name . '.' . $file->extension())
                        ->toMediaCollection('attachment', config('medialibrary.disk_name'));
                }
                $record = null;
            } else {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get attachment list
     *
     * @param array payload
     * @return dataTable
     */
    public function getTableData($cronofySchedule, $payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $list = $this->getRecordList($cronofySchedule, $payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('file_name', function ($record) {
                return $record->getFirstMedia('attachment')->name;
            })
            ->addColumn('created_at', function ($record) use($timezone) {
                return Carbon::parse($record->created_at)->setTimeZone($timezone)->format('M d, Y, H:i');
            })
            ->addColumn('actions', function ($record) use ($role, $payload) {
                $from = $payload['from'];
                return view('admin.cronofy.sessionlist.attachment_action', compact('record', 'role', 'from'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get attachment list data table
     *
     * @param array payload
     * @return array
     */
    public function getRecordList($cronofySchedule, $payload)
    {
        $query  = $this
            ->select(
                'cronofy_sessions_attachments.id',
                'cronofy_sessions_attachments.created_at'
            )->where('cronofy_sessions_attachments.cronofy_schedule_id', $cronofySchedule->id);

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('cronofy_sessions_attachments.id');
        }
        return [
            'total'  => $query->get()->count(),
            'record' => $query
            ->limit(3)
            ->get()
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
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }


    
    /**
     * Get attachment list for client
     *
     * @param array cronofySchedule
     * @param array payload
     * @return dataTable
     */
    public function getClientAttachmentData($cronofySchedule, $payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $list = $this->getClientAttachmentRecordList($cronofySchedule, $payload);
        return DataTables::of($list)
            ->addColumn('file_name', function ($record) {
                return $record->getFirstMedia('attachment')->name;
            })
            ->addColumn('created_at', function ($record) use($timezone) {
                return Carbon::parse($record->created_at)->setTimeZone($timezone)->format('M d, Y, H:i');
            })
            ->addColumn('actions', function ($record) use ($role, $payload) {
                $from = $payload['from'];
                return view('admin.cronofy.sessionlist.attachment_action', compact('record', 'role', 'from'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get attachment list for clients
     *
     * @param array cronofySchedule
     * @param array payload
     * @return array
     */
    public function getClientAttachmentRecordList($cronofySchedule, $payload)
    {
        $query       = $this
            ->select(
                'cronofy_sessions_attachments.id',
                'cronofy_sessions_attachments.created_at'
            )
            ->leftjoin('cronofy_schedule', 'cronofy_schedule.id', '=', 'cronofy_sessions_attachments.cronofy_schedule_id')
            ->leftjoin('users', 'users.id', '=', 'cronofy_schedule.user_id')
            ->where('cronofy_schedule.is_group', false)
            ->where('cronofy_schedule.user_id', $cronofySchedule->user_id);

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('cronofy_sessions_attachments.id');
        }
        return $query->get();
    }
}
