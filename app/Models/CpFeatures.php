<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpFeatures extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cp_features';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'manage',
        'status',
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
     * Get plan features treeview list for company plan add and edit.
     *
     * @param none
     * @return array
     */
    public function getCpPlanFeatures($group = 1): array
    {
        $parentFeatures = $this->get()
            ->where('parent_id', null);

        $featureLists = [];

        foreach ($parentFeatures as $value) {
            $childFeatures = $this->where('parent_id', $value->id)
                ->where('group', $group)
                ->where('status', 1)
                ->select('id', 'name')
                ->get()
                ->toArray();

            if (!empty($childFeatures)) {
                $featureLists[] = [
                    'id'           => $value->id,
                    'name'         => $value->name,
                    'manage'       => $value->manage,
                    'children'     => $childFeatures,
                ];
            }
        }

        return $featureLists;
    }
}
