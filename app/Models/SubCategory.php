<?php

namespace App\Models;

use App\Jobs\ContentReportExportReportJob;
use App\Models\Category;
use App\Models\Course;
use App\Models\Event;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\RecipeCategory;
use App\Models\User;
use App\Models\WebinarCategory;
use App\Models\Podcast;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SubCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sub_categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'short_name',
        'status',
        'default',
        'is_excluded',
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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * "belongs to" relation to `categories` table
     * via `category_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): belongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /*
     * "hasMany" relation to `courses` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function courses(): hasMany
    {
        return $this->hasMany(Course::class);
    }

    /*
     * "hasMany" relation to `feeds` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function feeds(): hasMany
    {
        return $this->hasMany(Feed::class);
    }

    /*
     * "hasMany" relation to `groups` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function groups(): hasMany
    {
        return $this->hasMany(Group::class);
    }

    /*
     * "hasMany" relation to `meditation_tracks` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function meditations(): hasMany
    {
        return $this->hasMany(MeditationTrack::class);
    }

    /*
     * "hasMany" relation to `recipe_category` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function recipes(): hasMany
    {
        return $this->hasMany(RecipeCategory::class);
    }

    /*
     * "hasMany" relation to `webinar_category` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function webinar(): hasMany
    {
        return $this->hasMany(WebinarCategory::class);
    }

    /*
     * "hasMany" relation to `events` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function events(): hasMany
    {
        return $this->hasMany(Event::class, 'subcategory_id');
    }

    /**
     * "belongsToMany" relation to `health_coach_expertises` table
     * via `expertise_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function healthcoachs(): belongsToMany
    {
        return $this->belongsToMany(User::class, 'health_coach_expertises', 'expertise_id', 'user_id');
    }

    /*
     * "hasMany" relation to `podcast` table
     * via `sub_category_id` field.
     * @return hasMany
     */
    public function podcasts(): hasMany
    {
        return $this->hasMany(Podcast::class);
    }


    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 36, 'w' => 36]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $media = $this->getFirstMedia('logo');
        return !empty($media->name) ? $media->name : "onboard_large.png";
    }

    /**
     * @param string $size
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
        return getThumbURL($params, 'sub_categories', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundAttribute()
    {
        return $this->getBackground(['h' => 320, 'w' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackgroundNameAttribute()
    {
        $media = $this->getFirstMedia('background');
        return !empty($media->name) ? $media->name : "onboard_large.png";
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBackground(array $params): string
    {
        $media = $this->getFirstMedia('background');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('background');
        }
        return getThumbURL($params, 'sub_categories', 'background');
    }

    /**
     * Set datatable for sub-category list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getSubCategoryList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($subCategory) {
                return $subCategory->updated_at;
            })
            ->addColumn('actions', function ($subCategory) {
                return view('admin.categories.subcategories.listaction', compact('subCategory'))->render();
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
    public function getSubCategoryList($payload)
    {
        $query = self::where('category_id', $payload['category']);

        return $query->get();
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

        $return['url'] = getThumbURL($param, 'sub_categories', $collection);
        return $return;
    }

    /**
     * store sub category data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $subCategory = self::create([
            'category_id' => $payload['category'],
            'name'        => $payload['name'],
            'short_name'  => str_replace(' ', '_', strtolower($payload['name'])),
        ]);
        
        if (!empty($payload['logo'])) {
            $name = $subCategory->id . '_' . \time();
            $subCategory
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['background'])) {
            $name = $subCategory->id . '_' . \time();
            $subCategory
                ->clearMediaCollection('background')
                ->addMediaFromRequest('background')
                ->usingName($payload['background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
        }

        if ($subCategory) {
            return $subCategory;
        }

        return false;
    }

    /**
     * For pre-populating data in edit sub category page.
     *
     * @param $id
     * @return array
     */
    public function getUpdateData()
    {
        return [
            'id'              => $this->id,
            'categories'      => Category::get()->pluck('name', 'id')->toArray(),
            'subCategoryData' => $this,
            'categoryId'      => $this->category_id,
        ];
    }

    /**
     * update sub-category data.
     *
     * @param payload , $id
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $updated = $this->update([
            'name' => $payload['name'],
        ]);
        
        if (!empty($payload['logo'])) {
            $name =$this->id . '_' . \time();
            $this->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['background'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('background')
                ->addMediaFromRequest('background')
                ->usingName($payload['background']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['background']->extension())
                ->toMediaCollection('background', config('medialibrary.disk_name'));
        }

        if ($updated) {
            return true;
        }
        return false;
    }

    /**
     * delete sub-category by id.
     *
     * @param $id
     * @return array
     */
    public function deleteSub()
    {
        if ($this->category_id == 1 && $this->courses->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 2 && $this->feeds->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 3 && $this->groups->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 4 && $this->meditations->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 5 && $this->recipes->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 6 && ($this->events->count() > 0 || $this->healthcoachs->count() > 0)) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 7 && $this->webinar->count() > 0) {
            return array('deleted' => 'use');
        } elseif ($this->category_id == 9 && $this->podcasts->count() > 0) {
            return array('deleted' => 'use');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }

        return array('deleted' => 'error');
    }

    /**
     * Get Datatable for Content Report
     *
     * @param $payload
     * @return array
     */
    public function getContentReport($payload)
    {
        $list = $this->getContentReportList($payload);
        return DataTables::of($list)
            ->addColumn('title', function ($records) {
                return $records->title;
            })
            ->addColumn('name', function ($records) {
                return $records->name;
            })
            ->addColumn('type', function ($records) {
                return $records->type;
            })
            ->addColumn('like_count', function ($records) {
                return $records->like_count;
            })
            ->addColumn('view_count', function ($records) {
                return $records->view_count;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get content report list
     *
     * @param $payload
     * @return array
     */
    public function getContentReportList($payload)
    {
        $user        = auth()->user();
        $company     = $user->company()->first();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        if ($role->group == 'reseller') {
            $subcompany = company::where('parent_id', $company->id)->orWhere('id', $company->id)->pluck('id')->toArray();
            $subcompany = implode(',', $subcompany);
        }
        if (empty($payload['type']) || $payload['type'] == 4) {
            $meditationRecords = DB::table('sub_categories')
                ->select(
                    'meditation_tracks.title',
                    'sub_categories.name',
                    DB::raw("'Meditation' AS type"),
                    DB::raw("sum(user_meditation_track_logs.liked) AS like_count"),
                    DB::raw("sum(user_meditation_track_logs.view_count) AS view_count")
                )
                ->join("meditation_tracks", "meditation_tracks.sub_category_id", "=", "sub_categories.id")
                ->join("user_meditation_track_logs", "user_meditation_track_logs.meditation_track_id", "=", "meditation_tracks.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $meditationRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'user_meditation_track_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $meditationRecords->whereRaw('meditation_tracks.id IN (SELECT meditation_track_id FROM meditation_tracks_company WHERE company_id IN (?) group by meditation_track_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $meditationRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'user_meditation_track_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $meditationRecords->whereRaw('meditation_tracks.id IN (SELECT meditation_track_id FROM meditation_tracks_company WHERE company_id IN (?) group by meditation_track_id)', [$subcompany]);
            }
            $meditationRecords->where(function ($query) {
                $query->whereRaw('user_meditation_track_logs.view_count > 0');
            });
            $meditationRecords->where('sub_categories.category_id', 4);

            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $meditationRecords->where('meditation_tracks.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $meditationRecords->where('meditation_tracks.sub_category_id', '=', $payload['category']);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $meditationRecords->whereBetween('user_meditation_track_logs.created_at', [$fromdate, $todate]);
            }
            $meditationRecords = $meditationRecords->groupBy('meditation_tracks.id');
        }

        if (empty($payload['type']) || $payload['type'] == 1) {
            $masterclassRecords = DB::table('sub_categories')
                ->select(
                    'courses.title',
                    'sub_categories.name',
                    DB::raw("'Masterclass' AS type"),
                    DB::raw("sum(user_course.liked) AS like_count"),
                    DB::raw("sum(user_course.joined) AS view_count")
                )
                ->join("courses", "courses.sub_category_id", "=", "sub_categories.id")
                ->join("user_course", "user_course.course_id", "=", "courses.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $masterclassRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'user_course.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $masterclassRecords->whereRaw('courses.id IN (SELECT masterclass_id FROM masterclass_company WHERE company_id = ? group by masterclass_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $masterclassRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'user_course.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $masterclassRecords->whereRaw('courses.id IN (SELECT masterclass_id FROM masterclass_company WHERE company_id IN (?) group by masterclass_id)', [$subcompany]);
            }
            $masterclassRecords->where(function ($query) {
                $query->whereRaw('user_course.joined > 0');
            });
            $masterclassRecords->where('sub_categories.category_id', 1);

            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $masterclassRecords->where('courses.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $masterclassRecords->where('courses.sub_category_id', '=', $payload['category']);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $masterclassRecords->whereBetween('user_course.created_at', [$fromdate, $todate]);
            }
            $masterclassRecords = $masterclassRecords->groupBy('courses.id');
        }

        if (empty($payload['type']) || $payload['type'] == 2) {
            $feedRecords = DB::table('sub_categories')
                ->select(
                    'feeds.title',
                    'sub_categories.name',
                    DB::raw("'Feed' AS type"),
                    DB::raw("sum(feed_user.liked) AS like_count"),
                    DB::raw("sum(feed_user.view_count) AS view_count")
                )
                ->join("feeds", "feeds.sub_category_id", "=", "sub_categories.id")
                ->join("feed_user", "feed_user.feed_id", "=", "feeds.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $feedRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'feed_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $feedRecords->whereRaw('feeds.id IN (SELECT feed_id FROM feed_company WHERE company_id IN (?) group by feed_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $feedRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'feed_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $feedRecords->whereRaw('feeds.id IN (SELECT feed_id FROM feed_company WHERE company_id IN (?) group by feed_id)', [$subcompany]);
            }
            $feedRecords->where(function ($query) {
                $query->whereRaw('feed_user.view_count > 0');
            });
            $feedRecords->where('sub_categories.category_id', 2);

            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $feedRecords->where('feeds.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $feedRecords->where('feeds.sub_category_id', '=', $payload['category']);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $feedRecords->whereBetween('feed_user.created_at', [$fromdate, $todate]);
            }
            $feedRecords = $feedRecords->groupBy('feeds.id');
        }

        if (empty($payload['type']) || $payload['type'] == 5) {
            $recipeRecords = DB::table('recipe')
                ->select(
                    'recipe.title',
                    DB::raw("(SELECT GROUP_CONCAT(sub_categories.name) FROM sub_categories LEFT JOIN recipe_category ON recipe_category.sub_category_id = sub_categories.id WHERE recipe_category.recipe_id = `recipe`.`id`) AS name"),
                    DB::raw("'Recipe' AS type"),
                    DB::raw("sum(recipe_user.liked) AS like_count"),
                    DB::raw("sum(recipe_user.view_count) AS view_count")
                )
                ->join("recipe_user", "recipe_user.recipe_id", "=", "recipe.id");
            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $recipeRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'recipe_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $recipeRecords->whereRaw('recipe.id IN (SELECT recipe_id FROM recipe_company WHERE company_id = ? group by recipe_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $recipeRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'recipe_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $recipeRecords->whereRaw('recipe.id IN (SELECT recipe_id FROM recipe_company WHERE company_id IN (?) group by recipe_id)', [$subcompany]);
            }
            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $recipeRecords->where('recipe.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $recipeRecords->whereRaw('recipe.id IN (SELECT recipe_id FROM recipe_category WHERE sub_category_id = ?)', [$payload['category']]);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $recipeRecords->whereBetween('recipe_user.created_at', [$fromdate, $todate]);
            }
            $recipeRecords->where(function ($query) {
                $query->whereRaw('recipe_user.view_count > 0');
            });
            $recipeRecords = $recipeRecords->groupBy('recipe.id');
        }

        if (empty($payload['type']) || $payload['type'] == 7) {
            $webinarRecords = DB::table('sub_categories')
                ->select(
                    'webinar.title',
                    'sub_categories.name',
                    DB::raw("'Webinar' AS type"),
                    DB::raw("sum(webinar_user.liked) AS like_count"),
                    DB::raw("sum(webinar_user.view_count) AS view_count")
                )
                ->join("webinar", "webinar.sub_category_id", "=", "sub_categories.id")
                ->join("webinar_user", "webinar_user.webinar_id", "=", "webinar.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $webinarRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'webinar_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $webinarRecords->whereRaw('webinar.id IN (SELECT webinar_id FROM webinar_company WHERE company_id = ? group by webinar_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $webinarRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'webinar_user.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $webinarRecords->whereRaw('webinar.id IN (SELECT webinar_id FROM webinar_company WHERE company_id IN (?) group by webinar_id)', [$subcompany]);
            }
            $webinarRecords->where(function ($query) {
                $query->whereRaw('webinar_user.view_count > 0');
            });
            $webinarRecords->where('sub_categories.category_id', 7);
            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $webinarRecords->where('webinar.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $webinarRecords->where('webinar.sub_category_id', '=', $payload['category']);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $webinarRecords->whereBetween('webinar.created_at', [$fromdate, $todate]);
            }
            $webinarRecords = $webinarRecords->groupBy('webinar.id');
        }

        if (empty($payload['type']) || $payload['type'] == 8) {
            $eapRecords = DB::table('eap_list')
                ->select(
                    'eap_list.title',
                    DB::raw("'N/A' AS name"),
                    DB::raw("'Supports (EAP)' AS type"),
                    DB::raw("'-' AS like_count"),
                    DB::raw("sum(eap_logs.view_count) AS view_count")
                )
                ->join("eap_logs", "eap_logs.eap_id", "=", "eap_list.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $eapRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'eap_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $eapRecords->whereRaw('eap_list.id IN (SELECT eap_id FROM eap_company WHERE company_id = ? group by eap_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $eapRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'eap_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $eapRecords->whereRaw('eap_list.id IN (SELECT eap_id FROM eap_company WHERE company_id IN (?) group by eap_id)', [$subcompany]);
            }
            $eapRecords->where(function ($query) {
                $query->whereRaw('eap_logs.view_count > 0');
            });
            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $eapRecords->where('eap_list.title', 'like', '%' . $payload['title'] . '%');
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $eapRecords->whereBetween('eap_list.created_at', [$fromdate, $todate]);
            }
            $eapRecords = $eapRecords->groupBy('eap_list.id');
        }
        //Content Report For Podcast
        if (empty($payload['type']) || $payload['type'] == 9) {
            $pocastRecords = DB::table('sub_categories')
                ->select(
                    'podcasts.title',
                    'sub_categories.name',
                    DB::raw("'Podcast' AS type"),
                    DB::raw("sum(user_podcast_logs.liked) AS like_count"),
                    DB::raw("sum(user_podcast_logs.view_count) AS view_count")
                )
                ->join("podcasts", "podcasts.sub_category_id", "=", "sub_categories.id")
                ->join("user_podcast_logs", "user_podcast_logs.podcast_id", "=", "podcasts.id");

            if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
                $companyId = $payload['company'];
                $pocastRecords->join('user_team', function ($join) use ($companyId) {
                    $join->on('user_team.user_id', '=', 'user_podcast_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$companyId]);
                });
                $pocastRecords->whereRaw('podcasts.id IN (SELECT podcast_id FROM podcast_company WHERE company_id IN (?) group by podcast_id)', [$companyId]);
            } elseif ($role->group == 'reseller') {
                $pocastRecords->join('user_team', function ($join) use ($subcompany) {
                    $join->on('user_team.user_id', '=', 'user_podcast_logs.user_id')
                        ->whereRaw('user_team.company_id IN (?)', [$subcompany]);
                });
                $pocastRecords->whereRaw('podcasts.id IN (SELECT podcast_id FROM podcasts WHERE company_id IN (?) group by podcast_id)', [$subcompany]);
            }
            $pocastRecords->where(function ($query) {
                $query->whereRaw('user_podcast_logs.view_count > 0');
            });
            $pocastRecords->where('sub_categories.category_id', 9);

            if (in_array('title', array_keys($payload)) && !empty($payload['title'])) {
                $pocastRecords->where('podcasts.title', 'like', '%' . $payload['title'] . '%');
            }
            if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
                $pocastRecords->where('podcasts.sub_category_id', '=', $payload['category']);
            }
            if ((in_array('fromdate', array_keys($payload)) && isset($payload['fromdate']) && !empty($payload['fromdate']) && strtotime($payload['fromdate']) !== false) && (in_array('todate', array_keys($payload)) && isset($payload['todate']) && !empty($payload['todate']) && strtotime($payload['todate']) !== false)) {
                $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                $pocastRecords->whereBetween('user_podcast_logs.created_at', [$fromdate, $todate]);
            }
            $pocastRecords = $pocastRecords->groupBy('podcasts.id');
        }

        switch ($payload['type']) {
            case '4':
                $records = $meditationRecords;
                break;
            case '1':
                $records = $masterclassRecords;
                break;
            case '2':
                $records = $feedRecords;
                break;
            case '5':
                $records = $recipeRecords;
                break;
            case '7':
                $records = $webinarRecords;
                break;
            case '8':
                $records = $eapRecords;
                break;
            case '9':
                $records = $pocastRecords;
                break;
            default:
                $records = $meditationRecords;
                $records->unionAll($masterclassRecords);
                $records->unionAll($feedRecords);
                $records->unionAll($recipeRecords);
                $records->unionAll($webinarRecords);
                $records->unionAll($eapRecords);
                break;
        }

        return $records->get();
    }

    /**
     * Export Report list
     * @param $payload
     * @return array
     */
    public function exportContentReport($payload)
    {
        $user    = auth()->user();
        $records = $this->getContentReportList($payload);
        $email   = ($payload['email'] ?? $user->email);

        if ($records) {
            // Generate Content export report
            \dispatch(new ContentReportExportReportJob($records, $email, $user));
            return true;
        }
    }
}
