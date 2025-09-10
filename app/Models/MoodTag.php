<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class MoodTag extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mood_tags';

    /**
     * @var array
     */
    protected $fillable = [
        'tag',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);
        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('tag', function ($record) {
                return $record->tag;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.moodTags.listaction', compact('record'))->render();
            })
            ->rawColumns(['logo', 'actions'])
            ->make(true);
    }

    /**
     * get records list for datatable.
     *
     * @param payload
     * @return array
     */
    public function getRecordList($payload)
    {
        return self::select(
            'mood_tags.id',
            'mood_tags.tag',
            'mood_tags.updated_at'
        )
            ->orderBy('mood_tags.updated_at', 'DESC')
            ->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $moodTagsInput = [
            'tag' => $payload['tag'],
        ];

        $record = self::create($moodTagsInput);

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * For pre-populating data in edit moods page.
     *
     * @param none
     * @return array
     */
    public function getUpdateData()
    {
        $data = array();

        $data['record'] = $this;

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
        $moodTagsInput = [
            'tag' => $payload['tag'],
        ];

        $record = $this->update($moodTagsInput);

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
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
