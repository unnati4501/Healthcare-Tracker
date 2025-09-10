<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;
use DB;


class ConsentForm extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'consent_form';

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
    public function questions(): HasMany
    {
        return $this->hasMany('App\Models\ConsentFormQuestions', 'consent_id');

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
            ConsentFormQuestions::where('consent_id', $this->id)->delete();
            $questionsArray = [];
            foreach ($payload['question_title'] as $key => $value) {
                $questionsArray[] = [
                    'consent_id'  => $this->id,
                    'title'       => $value,
                    'description' => $payload['question_description'][$key],
                ];
            }
            ConsentFormQuestions::insert($questionsArray);
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
             ->addColumn('actions', function ($record) {
                     return view('admin.cronofy.consentform.listaction', compact('record'))->render();
             })
             ->rawColumns(['actions'])
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
                 'consent_form.id',
                 'consent_form.title',
                 'consent_form.updated_at',
                 DB::raw('IF(consent_form.category = "1", "Online" , "Offline") AS category'),
             )
             ->groupBy('consent_form.id');
 
         if (isset($payload['order'])) {
             $column = $payload['columns'][$payload['order'][0]['column']]['data'];
             $order  = $payload['order'][0]['dir'];
             $query->orderBy($column, $order);
         } else {
             $query->orderBy('consent_form.id');
         }
 
         $data              = [];
         $data['total']     = $query->get()->count();
         $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
         $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
         $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();
 
         return $data;
     }
}
