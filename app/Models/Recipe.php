<?php

namespace App\Models;

use App\Jobs\SendRecipePushNotification;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\Goal;
use App\Models\RecipeType;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Observers\RecipeObserver;
use App\Traits\HasRewardPointsTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Recipe extends Model implements HasMedia
{
    use InteractsWithMedia, HasRewardPointsTrait;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'recipe';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'chef_id',
        'company_id',
        'title',
        'description',
        'calories',
        'cooking_time',
        'servings',
        'ingredients',
        'nutritions',
        'deep_link_uri',
        'status',
        'view_count',
        'created_at',
        'updated_at',
        'tag_id',
        'type_id',
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
    protected $casts = ['status' => 'boolean', 'view_count' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(RecipeObserver::class);
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
     * @return BelongsToMany
     */
    public function recipeSubCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'recipe_category', 'recipe_id', 'sub_category_id')
            ->withTimestamps();
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipeGoalTag(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'recipe_tag', 'recipe_id', 'goal_id');
    }

    /**
     * One-to-Many relations with Recipe.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipecompany(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'recipe_company', 'recipe_id', 'company_id');
    }

    /**
     * One-to-Many relations with Recipe.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipeteam(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'recipe_team', 'recipe_id', 'team_id');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * "BelongsTo" relation to `category_tags` table
     * via `tag_id` field.
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(CategoryTags::class, 'tag_id');
    }

    /**
     * "BelongsTo" relation to `recipe_types` table
     * via `type_id` field.
     *
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RecipeType::class, 'type_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 100, 'w' => 100]);
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
        return getThumbURL($params, 'recipe', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImageAttribute(): string
    {
        return $this->getHeaderImage(['w' => 800, 'h' => 800]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImageNameAttribute(): string
    {
        return $this->getFirstMedia('header_image')->name ?? '';
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getHeaderImage(array $params): string
    {
        $media = $this->getFirstMedia('header_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('header_image');
        }
        return getThumbURL($params, 'recipe', 'header_image');
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAllMediaData(string $collection, array $param): array
    {
        $return   = [];
        $allmedia = $this->getMedia($collection);
        if (sizeof($allmedia) > 0) {
            $allmedia->each(function ($media) use (&$return, $collection, &$param) {
                $mediaData = [
                    'id'     => $media->getKey(),
                    'width'  => $param['w'],
                    'height' => $param['h'],
                ];
                $src = $media->getUrl();
                if (!empty($src)) {
                    $param['src'] = $media->getUrl();
                }
                $mediaData['url'] = getThumbURL($param, 'recipe', $collection);
                $return[]         = $mediaData;
            });
        } else {
            $return[] = [
                'width'  => $param['w'],
                'height' => $param['h'],
                'url'    => getThumbURL($param, 'recipe', $collection),
            ];
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
            $param['src'] = $this->getFirstMediaUrl($collection);
        }
        $return['url'] = getThumbURL($param, 'recipe', $collection);
        return $return;
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $companyData = $user->company()->select('companies.id', 'companies.parent_id', 'companies.is_reseller')->first();
        $list        = $this->getRecordList($payload);
        $companyId   = (!is_null($companyData) ? $companyData->id : null);
        $parentId    = (!is_null($companyData) ? $companyData->parent_id : null);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('logo', function ($record) {
                return $record->logo;
            })
            ->addColumn('company_name', function ($record) {
                return $record->company_name ?? 'Zevo';
            })
            ->addColumn('companiesName', function ($record) use ($role, $companyData) {
                if (($role->group == 'zevo') || ($role->group == 'reseller' && !empty($companyData) && $companyData->is_reseller)) {
                    if ($role->group == 'zevo') {
                        $companies = $record->recipecompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->distinct()->get()->toArray();
                    } elseif ($role->group == 'reseller') {
                        $companies = $record->recipecompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' ELSE 'Child' END ) AS group_type"))
                            ->where(function ($query) use ($companyData) {
                                $query->where('companies.id', $companyData->id)
                                    ->orwhere('companies.parent_id', $companyData->id);
                            })
                            ->distinct()->get()->toArray();
                    }
                    $totalCompanies   = sizeof($companies);

                    if ($totalCompanies > 0) {
                        return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                    }
                }
                return "";
            })
            ->addColumn('status', function ($record) use ($user, $role, $companyId, $parentId) {
                return view('admin.recipe.approveaction', compact('record', 'user', 'role', 'companyId', 'parentId'))->render();
            })
            ->addColumn('actions', function ($record) use ($user, $role, $companyId) {
                return view('admin.recipe.listaction', compact('record', 'user', 'role', 'companyId'))->render();
            })
            ->rawColumns(['status', 'actions', 'companiesName'])
            ->make(true);
    }

    public function getRecordList($payload)
    {
        $role           = getUserRole();
        $companyDetails = \Auth::user()->company->first();

        $query = $this
            ->join('recipe_category', function ($join) {
                $join->on('recipe_category.recipe_id', '=', 'recipe.id');
            })
            ->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'recipe_category.sub_category_id')->where('sub_categories.status', 1);
            })
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'recipe.chef_id');
            })
            ->leftJoin('companies', function ($join) {
                $join->on('companies.id', '=', 'recipe.company_id');
            })
            ->leftJoin('category_tags', 'category_tags.id', '=', 'recipe.tag_id')
            ->leftJoin('recipe_types', 'recipe_types.id', '=', 'recipe.type_id');
        if ($role->group != 'zevo') {
            if ($role->group == 'reseller' && $companyDetails->parent_id == null) {
                $subcompany = company::where('parent_id', $companyDetails->id)->orWhere('id', $companyDetails->id)->pluck('id')->toArray();
                $query->Join('recipe_company', function ($join) use ($subcompany) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->whereIn('recipe_company.company_id', $subcompany);
                });
            } else {
                $query->Join('recipe_company', function ($join) use ($companyDetails) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $companyDetails->id);
                });
            }
        }
        $query
            ->select(
                'recipe.id',
                'recipe.company_id',
                'recipe.chef_id',
                'recipe.updated_at',
                'recipe.title',
                'recipe.creator_id',
                'recipe.description',
                'recipe.status',
                'recipe.created_at',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS username"),
                'companies.name AS company_name',
                DB::raw("IFNULL(category_tags.name, 'NA') AS category_tag"),
                DB::raw("IFNULL(recipe_types.type_name, 'NA') AS recipe_type")
            )
            ->groupBy('recipe.id')
            ->orderBy('recipe.status', 'ASC');

        if ($role->group == 'zevo') {
            $query->Where('recipe.status', 1);
        } elseif ($role->group == 'reseller') {
            if ($companyDetails->parent_id == null) {
                $query->Where('recipe.status', 1);
            }
        }

        if (in_array('company', array_keys($payload)) && $payload['company'] != '') {
            $query->where('recipe.company_id', ($payload['company'] == 'zevo' ? null : $payload['company']));
        }
        if (in_array('recipename', array_keys($payload)) && !empty($payload['recipename'])) {
            $query->whereRaw('LOWER(recipe.title) like ?', ['%' . strtolower($payload['recipename']) . '%']);
        }

        if (in_array('username', array_keys($payload)) && !empty($payload['username'])) {
            $query->whereRaw("LOWER(CONCAT(users.first_name, ' ', users.last_name)) like ?", ['%' . strtolower($payload['username']) . '%']);
        }

        if (in_array('sub_category', array_keys($payload)) && !empty($payload['sub_category'])) {
            $query->where('recipe_category.sub_category_id', $payload['sub_category']);
        }

        if (in_array('status', array_keys($payload)) && $payload['status'] != '') {
            $query->where('recipe.status', $payload['status']);
        }

        if (in_array('type', array_keys($payload)) && $payload['type'] != '') {
            $query->where('recipe_types.id', $payload['type']);
        }

        if (in_array('tag', array_keys($payload)) && !empty($payload['tag'])) {
            if (strtolower($payload['tag']) == 'na') {
                $query->whereNull('recipe.tag_id');
            } else {
                $query->where('recipe.tag_id', $payload['tag']);
            }
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $sortcolumn = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            if ($sortcolumn == 'companiesName') {
                $column = "id";
            } else {
                $column = $sortcolumn;
            }
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('recipe.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorData(): array
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getChefData(): array
    {
        $return  = [];
        $creator = User::find($this->chef_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @return BelongsToMany
     */
    public function recipeUserLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'recipe_user', 'recipe_id', 'user_id')
            ->withPivot('saved', 'saved_at', 'favourited', 'favourited_at', 'liked', 'view_count')
            ->withTimestamps();
    }

    /**
     * @return integer
     */
    public function getTotalLikes(): int
    {
        return $this->recipeUserLogs()->wherePivot('liked', true)->count();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $user            = auth()->user();
        $role            = getUserRole();
        $nutritions      = config('zevolifesettings.nutritions');
        $company         = $user->company->first();
        $company_id      = !is_null($company) ? $company->id : null;
        $storeNutritions = [];
        $i               = 1;

        $data = [
            'creator_id'   => $user->id,
            'chef_id'      => ((!empty($payload['chef'])) ? $payload['chef'] : $user->id),
            'company_id'   => (($role->group == 'zevo') ? null : $company_id),
            'title'        => $payload['title'],
            'description'  => $payload['description'],
            'calories'     => round($payload['calories'], 1),
            'cooking_time' => decimalToTime((int) $payload['cooking_time']),
            'servings'     => $payload['servings'],
            'ingredients'  => json_encode($payload['ingredients'], JSON_FORCE_OBJECT),
            'status'       => 1,
            'type_id'      => $payload['type'],
        ];

        foreach ($payload['nutritions'] as $value) {
            $storeNutritions[] = [
                'id'    => ($i),
                'title' => $nutritions[$i]['display_name'],
                'value' => number_format(round($value, 1), 1, '.', ''),
            ];
            $i++;
        }

        $data['nutritions'] = json_encode($storeNutritions);

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $record = self::create($data);
        $record->recipesubcategories()->sync($payload['recipesubcategory']);

        if (isset($payload['image']) && !empty($payload['image'])) {
            foreach ($payload['image'] as $file) {
                $name = $record->getKey() . '_' . Str::random() . \time();
                $record->addMedia($file)
                    ->usingName($name)
                    ->usingFileName($name . '.' . $file->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }
        }

        if (!empty($payload['header_image'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (!empty($payload['goal_tag'])) {
            $record->recipeGoalTag()->sync($payload['goal_tag']);
        }

        $recipe_companyInput = [];
        if ($role->group == 'zevo') {
            // recipe_company now convert to team ids
            $companyIds = TeamLocation::whereIn('team_id', $payload['recipe_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $recipe_companyInput[] = [
                    'recipe_id'  => $record->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
            $record->recipeteam()->sync($payload['recipe_company']);
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $recipe_companyInput = [];
            // recipe_company now convert to team ids
            $companyIds = TeamLocation::whereIn('team_id', $payload['recipe_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $recipe_companyInput[] = [
                    'recipe_id'  => $record->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
            $record->recipeteam()->sync($payload['recipe_company']);
        } elseif ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            $teamIds               = TeamLocation::where('company_id', $company_id)->select('team_id')->distinct()->get()->pluck('team_id')->toArray();
            $recipe_companyInput[] = [
                'recipe_id'  => $record->id,
                'company_id' => $company_id,
                'created_at' => Carbon::now(),
            ];
            $record->recipeteam()->sync($teamIds);
        }

        $record->recipecompany()->sync($recipe_companyInput);

        if ($record) {
            $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['recipes', 'all']);
                })
                ->whereRaw('user_team.team_id IN ( SELECT team_id FROM `recipe_team` WHERE recipe_id = ? )', [$record->id])
                ->where('is_blocked', false)
                ->groupBy('users.id')
                ->get()
                ->toArray();

            // dispatch job to send push notification to all user when recipe created
            \dispatch(new SendRecipePushNotification($record, "community-recipe-added", $notificationUser, ''));

            return true;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $user            = auth()->user();
        $role            = getUserRole();
        $nutritions      = config('zevolifesettings.nutritions');
        $company         = $user->company->first();
        $storeNutritions = [];
        $i               = 1;

        $data = [
            'title'        => $payload['title'],
            'description'  => $payload['description'],
            'calories'     => round($payload['calories'], 1),
            'cooking_time' => decimalToTime((int) $payload['cooking_time']),
            'servings'     => $payload['servings'],
            'ingredients'  => json_encode($payload['ingredients'], JSON_FORCE_OBJECT),
            'type_id'      => $payload['type'],
        ];

        if ($role->group == 'zevo') {
            $data['chef_id'] = ((!empty($payload['chef'])) ? $payload['chef'] : $user->id);
        } elseif ($this->creator()->first()->roles()->where(['default' => 1, 'slug' => 'user'])->count() == 0) {
            $data['chef_id'] = ((!empty($payload['chef'])) ? $payload['chef'] : $user->id);
        }

        foreach ($payload['nutritions'] as $value) {
            $storeNutritions[] = [
                'id'    => ($i),
                'title' => $nutritions[$i]['display_name'],
                'value' => number_format(round($value, 1), 1, '.', ''),
            ];
            $i++;
        }
        $data['nutritions'] = json_encode($storeNutritions);

        if ($role->group == 'zevo') {
            $data['tag_id'] = (!empty($payload['tag']) ? $payload['tag'] : null);
        }

        $updated = $this->update($data);
        $this->recipesubcategories()->sync($payload['recipesubcategory']);

        if (!empty($payload['image'])) {
            foreach ($payload['image'] as $file) {
                $name = $this->getKey() . '_' . Str::random() . \time();
                $this->addMedia($file)
                    ->usingName($name)
                    ->usingFileName($name . '.' . $file->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }
        }

        if (isset($payload['header_image']) && !empty($payload['header_image'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('header_image')
                ->addMediaFromRequest('header_image')
                ->usingName($payload['header_image']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['header_image']->extension())
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
        }

        if (!empty($payload['deletedImages'])) {
            $deletedImages = explode(',', $payload['deletedImages']);
            foreach ($deletedImages as $mediaId) {
                $media = $this->media->find($mediaId);
                if (!empty($media)) {
                    $this->deleteMedia($media->id);
                }
            }
        }

        $this->recipeGoalTag()->detach();
        if (!empty($payload['goal_tag'])) {
            $this->recipeGoalTag()->sync($payload['goal_tag']);
        }

        $recipe_companyInput = [];
        if ($role->group == 'zevo') {
            $companyIds = TeamLocation::whereIn('team_id', $payload['recipe_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $recipe_companyInput[$value] = [
                    'recipe_id'  => $this->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $companyIds = TeamLocation::whereIn('team_id', $payload['recipe_company'])->select('company_id')->distinct()->get()->pluck('company_id')->toArray();
            foreach ($companyIds as $value) {
                $recipe_companyInput[] = [
                    'recipe_id'  => $this->id,
                    'company_id' => $value,
                    'created_at' => Carbon::now(),
                ];
            }
        }

        if (!empty($payload['recipe_company'])) {
            $existingComapnies      = $this->recipeteam()->pluck('teams.id')->toArray();
            $newlyAssociatedComps   = array_diff($payload['recipe_company'], $existingComapnies);
            $removedAssociatedComps = array_diff($existingComapnies, $payload['recipe_company']);
            // delete notifications for the users which company has been removed from visibility
            if (!empty($removedAssociatedComps)) {
                // Get user id list from companies ids
                $userIds = User::select('users.id')
                    ->join("user_team", "user_team.user_id", "=", "users.id")
                    ->whereIn("user_team.team_id", $removedAssociatedComps)
                    ->get()
                    ->pluck('id')
                    ->toArray();

                Notification::Join('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                    ->whereIn('notification_user.user_id', $userIds)
                    ->where(function ($query) {
                        $query
                            ->where('notifications.tag', 'recipe')
                            ->where('notifications.deep_link_uri', $this->deep_link_uri);
                    })
                    ->delete();
            }

            $this->recipeteam()->sync($payload['recipe_company']);
            $this->recipecompany()->sync($companyIds);
        }
        if (!empty($newlyAssociatedComps)) {
            $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['recipes', 'all']);
                })
                ->whereRaw(DB::raw('user_team.team_id IN (?)'),[implode(',', $newlyAssociatedComps)])
                ->where('is_blocked', false)
                ->groupBy('users.id')
                ->get()
                ->toArray();

            // dispatch job to send push notification to all user when recipe created
            \dispatch(new SendRecipePushNotification($this, "community-recipe-added", $notificationUser, ''));
        }

        if ($updated) {
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
        $this->clearMediaCollection('logo');
        $user = auth()->user();

        if (!empty($user) && !empty($this->creator_id) && $user->id != $this->creator_id) {
            $membersData = User::where("users.id", $this->creator_id)
                ->select('users.*', DB::raw('1 AS notification_flag'))
                ->groupBy('users.id')
                ->get()
                ->toArray();

            // dispatch job to send push notification to all user when recipe deleted
            \dispatch_now(new SendRecipePushNotification($this, "community-recipe-deleted", $membersData, ''));
        }

        if ($this->delete()) {
            return array('deleted' => true, 'message' => trans('labels.recipe.deleted_success'));
        }
        return array('deleted' => false, 'message' => trans('labels.recipe.deleted_error'));
    }

    /**
     * approve record by record id.
     *
     * @param $id
     * @return boolean
     */
    public function approveRecord()
    {
        $updated = $this->update(['status' => 1]);
        if ($updated) {
            $user           = auth()->user();
            $role           = getUserRole($user);
            $companyDetails = $user->company()->first();
            $companyId      = $companyDetails->id;

            if ($role->group == 'company') {
                $counts = Recipe::select(DB::raw('SUM(status = 1) AS approved'), DB::raw('SUM(status = 0) AS unapproved'))->Join('recipe_company', function ($join) use ($companyId) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $companyId);
                })->whereRaw('(recipe.company_id = ? OR recipe.company_id IS NULL)', [$companyId])->first();
            } else {
                $counts = Recipe::select(DB::raw('IFNULL(SUM(status = 1), 0) AS approved'), DB::raw('IFNULL(SUM(status = 0), 0) AS unapproved'))
                    ->Join('recipe_company', function ($join) use ($companyId) {
                        $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                            ->where('recipe_company.company_id', $companyId);
                    })
                    ->Where('recipe.company_id', $companyId)
                    ->orWhere('recipe.company_id', null)
                    ->orWhere('recipe.company_id', $companyDetails->parent_id)
                    ->first();
            }

            $membersData = User::where("users.id", $this->creator_id)
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['recipes', 'all']);
                })
                ->select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->groupBy('users.id')
                ->get()
                ->toArray();

            // dispatch job to send push notification to app user when recipe approved by CA
            \dispatch(new SendRecipePushNotification($this, "community-recipe-approved", $membersData, ''));

            $data['approved']         = true;
            $data['count_approved']   = $counts['approved'];
            $data['count_unapproved'] = $counts['unapproved'];
            $data['message']          = trans('labels.recipe.approved_success');

            return $data;
        }
        return array('approved' => false, 'message' => trans('labels.recipe.approved_error'));
    }
}
