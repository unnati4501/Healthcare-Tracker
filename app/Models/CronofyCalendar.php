<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class CronofyCalendar extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronofy_calendar';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'cronofy_id',
        'provider_name',
        'profile_name',
        'calendar_id',
        'profile_id',
        'readonly',
        'primary',
        'status',
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
     * To store an cronofy authenticate details.
     *
     * @param array payload
     * @return boolean
     */
    public function storeCalendar(array $payload, $authId)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        krsort($payload);

        foreach ($payload as $key => $data) {
            if (!$data['calendar_readonly']) {
                $response = $this->updateOrCreate([
                    'cronofy_id'   => $authId,
                    'profile_name' => $data['profile_name'],
                    'user_id'      => $user->id,
                ], [
                    'provider_name' => $data['provider_name'],
                    'calendar_id'   => $data['calendar_id'],
                    'profile_id'    => $data['profile_id'],
                    'readonly'      => $data['calendar_readonly'],
                    'primary'       => ($key == 0),
                    'status'        => 1,
                ]);
            }
        }

        if ($role->slug == 'wellbeing_specialist') {
            $user->wsuser()->update([
                'is_authenticate' => true,
            ]);
        } elseif ($role->slug == 'health_coach') {
            $user->healthCoachUser()->update([
                'is_authenticate' => true,
            ]);
        }

        return $response;
    }

    /**
     * Set datatable for calendar list.
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
            ->addColumn('calendar_id', function ($record) {
                if ($record->provider_name == 'exchange') {
                    $image = '<img src="' . asset('assets/dist/img/outlook-icon.png') . '" />';
                } else {
                    $image = '<img src="' . asset('assets/dist/img/gmail-icon.png') . '" />';
                }
                return $image . ' ' . $record->profile_name;
            })
            ->addColumn('primary', function ($record) {
                return view('admin.cronofy.primary', compact('record'))->render();
            })
            ->addColumn('status', function ($record) {
                if ($record->status) {
                    return '<span class="text-success">Active</span>';
                } else {
                    return '<span class="text-danger">In Active</span>';
                }
            })
            ->addColumn('action', function ($record) {
                return view('admin.cronofy.actions', compact('record'))->render();
            })
            ->rawColumns(['calendar_id', 'primary', 'status', 'action'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return list
     */
    public function getRecordList($payload)
    {
        $user  = auth()->user();
        $query = $this->where('user_id', $user->id)
            ->orderBy("updated_at", 'DESC');

        return [
            'total'  => $query->get()->count(),
            'record' => $query->get(),
        ];
    }

    /**
     * remove calendar from the database
     *
     * @param profileId
     */
    public function removeCalendar($profileId)
    {
        $user   = auth()->user();
        $unlink = $this->where('user_id', $user->id)->where('profile_id', $profileId)->delete();

        if ($unlink) {
            return array('unlink' => 'true');
        }
        return array('unlink' => 'false');
    }

    /**
     * Get Calendar Ids based on user ids
     */
    public function getCalendarIds($userId)
    {
        return $this->where('user_id', $userId)->select('calendar_id')->orderByDesc('primary')->get()->pluck('calendar_id')->toArray();
    }
}
