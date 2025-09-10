<?php

namespace App\Models;

use App\Models\AppTheme;
use App\Models\Company;
use App\Jobs\CreditHistoryExportJob;
use DataTables;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CompanyWiseCredit extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_wise_credits';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_name',
        'credits',
        'notes',
        'type',
        'available_credits'
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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $data = [
            'company_id'    => $payload['company_id'],
            'user_name'     => $payload['user_name'],
            'credits'       => $payload['credits'],
            'notes'         => $payload['notes'],
            'type'          => $payload['type'],
        ];

        if ( $payload['type'] == 'Add' ) {
            $data['available_credits'] = $payload['available_credits'] +  $payload['credits'];
        } else {
            $data['available_credits'] = $payload['available_credits'] -  $payload['credits'];
        }

        $record   = self::create($data);
        if ($record) {
            Company::where('id', $payload['company_id'])->update(['credits' => $data['available_credits']]);
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
             ->make(true);
     }
 
     /**
      * get record list for data table list.
      *
      * @param payload
      * @return recordList
      */
 
     public function getRecordList($payload, $type=null)
     {
         $user          = auth()->user();
         $appTimezone   = config('app.timezone');
         $query   = $this
            ->select(
                'type',
                'credits',
                'user_name',
                'available_credits',
                'notes',
            )
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(created_at, ? , ?), '%M %d, %Y %h:%i %p') AS export_date",[
                $appTimezone, $user->timezone
            ])
            ->where('company_id', $payload['company'])
            ->orderBy('id', 'ASC');
         
         if (isset($payload['order'])) {
             $column = $payload['columns'][$payload['order'][0]['column']]['data'];
             $order  = $payload['order'][0]['dir'];
             $query->orderBy($column, $order);
         } else {
             $query->orderBy('id');
         }
         
         if ($type == 'export') {
            return $query->get();
         }

         $data              = [];
         $data['total']     = $query->get()->count();
         $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
         $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
         $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();
         
       
         return $data;
     }

     /**
     * Export Report list
     *
     * @param $payload
     * @return array
     */
    public function exportCreditHistory($payload)
    {
        $user    = auth()->user();
        $records = $this->getRecordList($payload, 'export');
        $email   = ($payload['email'] ?? $user->email);
        if ($records) {
            \dispatch(new CreditHistoryExportJob($records->toArray(), $email, $user));
            return true;
        }
    }
}
