<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Facades\DataTables;

class ContentChallenge extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content_challenge';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contentChallengeActivity()
    {
        return $this->hasMany('App\Models\ContentChallengeActivity', 'category_id');
    }

    /**
     * Set datatable for content challenge category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getContentChallengeCategoryList($payload);
        return DataTables::of($list)
            ->addColumn('updated_at', function ($contentChallengeCategory) {
                return $contentChallengeCategory->updated_at;
            })
            ->addColumn('activities', function ($contentChallengeCategory) {
                return $contentChallengeCategory->contentChallengeActivity()->count();
            })
            ->addColumn('actions', function ($contentChallengeCategory) {
                return view('admin.contentChallenge.listaction', compact('contentChallengeCategory'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get category list for data table list.
     *
     * @param payload
     * @return categoryList
     */

    public function getContentChallengeCategoryList($payload)
    {
        $query = self::with('contentChallengeActivity');
        return $query->get();
    }
}
