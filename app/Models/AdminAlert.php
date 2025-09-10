<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;
use DB;


class AdminAlert extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_alerts';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
    ];

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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany('App\Models\AdminAlertUsers', 'alert_id');

    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $updated = $this->update([
            'title'       => $payload['title'],
            'description' => $payload['description'],
        ]);
        
        if ($updated) {
            AdminAlertUsers::where('alert_id', $this->id)->delete();
            $userArray = [];
            if (!empty($payload['user_name'])){
                foreach ($payload['user_name'] as $key => $value) {
                    $userArray[] = [
                        'alert_id'   => $this->id,
                        'user_name'  => $value,
                        'user_email' => $payload['user_email'][$key],
                    ];
                }
                AdminAlertUsers::insert($userArray);
            }
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

     public function getTableData($payload)
     {
         $list = $this->getRecordList($payload);
         return DataTables::of($list['record'])
             ->skipPaging()
             ->with([
                 "recordsTotal"    => $list['total'],
                 "recordsFiltered" => $list['total'],
             ])
             ->addColumn('notify_users', function ($record) {
                $alertUsers = AdminAlertUsers::join('admin_alerts', 'admin_alerts.id', '=', 'admin_alert_users.alert_id')->select('admin_alert_users.user_name', 'admin_alert_users.user_email')->where('alert_id', $record->id)->distinct()->get()->toArray();
                $totalUsers = sizeof($alertUsers);

                if ($totalUsers > 0) {
                    return "<a href='javascript:void(0);' title='View Users' class='preview_users' data-rowdata='" . base64_encode(json_encode($alertUsers)) . "' data-cid='" . $record->id . "'> " . $totalUsers . "</a>";
                } else {
                    return "0";
                }
            })
             ->addColumn('actions', function ($record) {
                     return view('admin.admin-alerts.listaction', compact('record'))->render();
             })
             ->rawColumns(['notify_users','actions'])
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
         $query   = $this
             ->select(
                'admin_alerts.id',
                'admin_alerts.title',
                'admin_alerts.updated_at',
             )
             ->groupBy('admin_alerts.id');
 
        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
             $column = $payload['columns'][$payload['order'][0]['column']]['data'];
             $order  = $payload['order'][0]['dir'];
             $query->orderBy($column, $order);
         } else {
             $query->orderBy('admin_alerts.id');
         }
 
         $data              = [];
         $data['total']     = $query->get()->count();
         $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
         $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
         $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();
 
         return $data;
     }

     public static function getemailtemplate(){
        $getcontent = $this->where('title','Access Next to Kin Info')->first();
        $getcontent = json_decode(json_encode($getcontent),true);
        return $getcontent;
    }
}
