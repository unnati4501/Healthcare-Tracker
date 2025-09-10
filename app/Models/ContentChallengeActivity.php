<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ContentChallenge;
use Yajra\DataTables\Facades\DataTables;

class ContentChallengeActivity extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content_challenge_activities';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activity',
        'category_id',
        'daily_limit',
        'points_per_action',
        'created_at',
        'updated_at',
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
    protected $dates = [];

    /**
     * "belongs to" relation to `categories` table
     * via `category_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contentChallengeCategory(): belongsTo
    {
        return $this->belongsTo(ContentChallenge::class, 'category_id');
    }

    /**
     * Set datatable for sub-category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getChallengeActivityList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($activity) {
                return $activity->updated_at;
            })
            ->addColumn('actions', function ($activity) {
                return view('admin.contentChallenge.activities.listaction', compact('activity'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get sub category list for data table list.
     *
     * @param payload
     * @return categoryList
     */
    public function getChallengeActivityList($payload)
    {
        $query = self::where('category_id', $payload['category'])->orderBy('id', 'ASC');
        return $query->get();
    }
}
