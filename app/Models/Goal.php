<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Goal extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'goals';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'media'];

    /**
     * "has many" relation to `mood_user` table
     * via `mood_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moodUser()
    {
        return $this->hasMany(\App\Models\MoodUser::class, 'mood_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 100, 'h' => 100]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getImageAttribute()
    {
        return $this->getMediaData('logo', ['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        return $this->getFirstMedia('logo')->name;
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
        return getThumbURL($params, 'goals', 'logo');
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
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'goals', $collection);
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
        $list = $this->getRecordList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('logo', function ($record) {
                $goalObj = Goal::find($record->id);
                return ' <div class="table-img table-img-l"><img src="' . $goalObj->logo . '" alt=""></div>';
            })
            ->addColumn('title', function ($record) {
                return $record->title;
            })
            ->addColumn('feed', function ($record) {
                return $record->feed_content_count;
            })
            ->addColumn('masterclass', function ($record) {
                return $record->course_content_count;
            })
            ->addColumn('recipe', function ($record) {
                return $record->recipe_content_count;
            })
            ->addColumn('meditation', function ($record) {
                return $record->meditation_content_count;
            })
            ->addColumn('webinar', function ($record) {
                return $record->webinar_content_count;
            })
            ->addColumn('total', function ($record) {
                return $record->total;
            })
            ->addColumn('actions', function ($record) {
                $totalContent = $record->total;

                $view   = (!access()->allow('view-goal-tags') || $totalContent <= 0) ? 'hidden' : '';
                $edit   = !access()->allow('update-goal-tags') ? 'hidden' : '';
                $delete = (!access()->allow('delete-goal-tags') || $totalContent > 0) ? 'hidden' : '';
                return '<a class="action-icon" href="' . route('admin.goals.view', $record->id) . '" title="'.trans('goals.table.view').'" ' . $view . '>
                            <i aria-hidden="true" class="far fa-eye"></i>
                        </a>
                        <a class="action-icon" href="' . route('admin.goals.edit', $record->id) . '" title="'.trans('buttons.general.tooltip.edit').'" ' . $edit . '>
                            <i aria-hidden="true" class="far fa-edit"></i>
                        </a>
                        <a href="javaScript:void(0)" class="action-icon danger" title="'.trans('buttons.general.tooltip.delete').'" data-id="' . $record->id . '" id="deleteModal" ' . $delete . '>
                            <i class="far fa-trash-alt" aria-hidden="true">
                            </i>
                        </a>';
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
        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $columnName = $payload['columns'][$payload['order'][0]['column']]['data'];
            switch ($columnName) {
                case 'feed':
                    $column = 'feed_content_count';
                    break;
                case 'masterclass':
                    $column = 'course_content_count';
                    break;
                case 'recipe':
                    $column = 'recipe_content_count';
                    break;
                case 'meditation':
                    $column = 'meditation_content_count';
                    break;
                case 'webinar':
                    $column = 'webinar_content_count';
                    break;
                case 'total':
                    $column = 'total';
                    break;
                default:
                    $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                    $column = is_string($column) ? $column : 'total';
                    break;
            }

            $order = $payload['order'][0]['dir'];
            $order = is_string($order) ? $order : 'DESC';
        } else {
            $column = 'goals.updated_at';
            $order   = 'DESC';
        }

        return DB::select(DB::raw(" select records.*, ( feed_content_count + course_content_count + meditation_content_count + recipe_content_count + webinar_content_count ) AS total FROM ( select `goals`.`id`,`goals`.`title`, `goals`.`updated_at`, (select COUNT(feed_tag.id) from feed_tag inner join feeds on feed_tag.feed_id = feeds.id where feed_tag.goal_id = goals.id AND CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone) <= CONVERT_TZ(now(), @@session.time_zone , feeds.timezone) AND CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone) >= CONVERT_TZ(now(), @@session.time_zone , feeds.timezone) ) feed_content_count , (select COUNT(course_tag.id) from course_tag inner join courses on courses.id = course_tag.course_id  where  course_tag.goal_id = goals.id AND courses.status = 1 ) course_content_count, (select COUNT(meditation_tracks_tag.id) from meditation_tracks_tag where  meditation_tracks_tag.goal_id = goals.id) meditation_content_count, (select COUNT(recipe_tag.id) from recipe_tag inner join recipe on recipe.id = recipe_tag.recipe_id where  recipe_tag.goal_id = goals.id and recipe.status = 1 ) recipe_content_count, ( SELECT COUNT(webinar_tag.id) FROM webinar_tag INNER JOIN webinar ON webinar.id = webinar_tag.webinar_id WHERE webinar_tag.goal_id = goals.id ) AS webinar_content_count from `goals`) as records ORDER BY '?' '?'", [$column, $order])->getValue(DB::getQueryGrammar()));
    }

    /**
     * get records list for datatable.
     *
     * @param payload
     * @return array
     */
    public function getTagsTableData($request)
    {
        $list = $this->getTagsRecods($request);

        return DataTables::of($list['record'])
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->skipPaging()
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('logo', function ($record) {
                switch ($record->type) {
                    case 'Feed':
                        $feedObj = Feed::find($record->tag_id);
                        return '<div class="table-img table-img-l"><img src="' . $feedObj->logo . '" alt=""></div>';
                        break;
                    case 'Masterclass':
                        $courseObj = Course::find($record->tag_id);
                        return '<div class="table-img table-img-l"><img src="' . $courseObj->logo . '" alt=""></div>';
                        break;
                    case 'Meditation':
                        $meditationTrackObj = MeditationTrack::find($record->tag_id);
                        return '<div class="table-img table-img-l"><img src="' . $meditationTrackObj->cover_url . '" alt=""></div>';
                        break;
                    case 'Recipe':
                        $recipeObj = Recipe::find($record->tag_id);
                        return '<div class="table-img table-img-l"><img src="' . $recipeObj->logo . '" alt=""></div>';
                        break;
                    case 'Webinar':
                        $webinarObj = Webinar::find($record->tag_id);
                        return '<div class="table-img table-img-l"><img src="' . $webinarObj->logo . '" alt=""></div>';
                        break;
                    default:
                        return '';
                        break;
                }
            })
            ->addColumn('title', function ($record) {
                return $record->title;
            })
            ->addColumn('type', function ($record) {
                return $record->type;
            })
            ->addColumn('actions', function ($record) {
                return '<a href="javaScript:void(0)" class="action-icon danger" title="' . trans('goals.table.unmapped') . '" data-id="' . $record->id . '" data-type="' . $record->type . '" id="deleteModal">
                        <i class="far fa-unlink"></i>
                    </a>';
            })
            ->rawColumns(['logo', 'actions'])
            ->make(true);
    }

    /**
     * get records list from database.
     *
     * @param payload
     * @return array
     */
    public function getTagsRecods($payload)
    {
        $goal_id = $payload['goal_id'];
        $title   = $payload['title'];
        $tagtype = $payload['type'];

        switch ($tagtype) {
            case 'feed':
                $query = DB::table('feed_tag')
                    ->join('feeds', 'feed_tag.feed_id', '=', 'feeds.id')
                    ->select('feed_tag.id', 'feeds.title', DB::raw("'Feed' as type"), 'feeds.updated_at', 'feed_tag.feed_id AS tag_id')
                    ->where('feed_tag.goal_id', '=', $goal_id)
                    ->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null)
                    ->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);

                if ($title) {
                    $query->Where('feeds.title', 'like', '%' . $title . '%');
                }
                break;
            case 'recipe':
                $query = DB::table('recipe_tag')
                    ->join('recipe', 'recipe_tag.recipe_id', '=', 'recipe.id')
                    ->select('recipe_tag.id', 'recipe.title', DB::raw("'Recipe' as type"), 'recipe.updated_at', 'recipe_tag.recipe_id AS tag_id')
                    ->where('recipe_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $query->Where('recipe.title', 'like', '%' . $title . '%');
                }
                break;
            case 'meditation':
                $query = DB::table('meditation_tracks_tag')
                    ->join('meditation_tracks', 'meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id')
                    ->select('meditation_tracks_tag.id', 'meditation_tracks.title', DB::raw("'Meditation' as type"), 'meditation_tracks.updated_at', 'meditation_tracks_tag.meditation_track_id AS tag_id')
                    ->where('meditation_tracks_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $query->Where('meditation_tracks.title', 'like', '%' . $title . '%');
                }
                break;
            case 'course':
                $query = DB::table('course_tag')
                    ->join('courses', 'course_tag.course_id', '=', 'courses.id')
                    ->select('course_tag.id', 'courses.title', DB::raw("'Masterclass' as type"), 'courses.updated_at', 'course_tag.course_id AS tag_id')
                    ->where('course_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $query->Where('courses.title', 'like', '%' . $title . '%');
                }
                break;
            case 'webinar':
                $query = DB::table('webinar_tag')
                    ->join('webinar', 'webinar_tag.webinar_id', '=', 'webinar.id')
                    ->select('webinar_tag.id', 'webinar.title', DB::raw("'Webinar' as type"), 'webinar.updated_at', 'webinar_tag.webinar_id AS tag_id')
                    ->where('webinar_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $query->Where('webinar.title', 'like', '%' . $title . '%');
                }
                break;
            default:
                $feed = DB::table('feed_tag')
                    ->join('feeds', 'feed_tag.feed_id', '=', 'feeds.id')
                    ->select('feed_tag.id', 'feeds.title', DB::raw("'Feed' as type"), 'feeds.updated_at', 'feed_tag.feed_id AS tag_id')
                    ->where('feed_tag.goal_id', '=', $goal_id)
                    ->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))
                    ->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"));
                if ($title) {
                    $feed->Where('feeds.title', 'like', '%' . $title . '%');
                }

                $course = DB::table('course_tag')
                    ->join('courses', 'course_tag.course_id', '=', 'courses.id')
                    ->select('course_tag.id', 'courses.title', DB::raw("'Masterclass' as type"), 'courses.updated_at', 'course_tag.course_id AS tag_id')
                    ->where('course_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $course->Where('courses.title', 'like', '%' . $title . '%');
                }

                $meditation = DB::table('meditation_tracks_tag')
                    ->join('meditation_tracks', 'meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id')
                    ->select('meditation_tracks_tag.id', 'meditation_tracks.title', DB::raw("'Meditation' as type"), 'meditation_tracks.updated_at', 'meditation_tracks_tag.meditation_track_id AS tag_id')
                    ->where('meditation_tracks_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $meditation->Where('meditation_tracks.title', 'like', '%' . $title . '%');
                }

                $recipe = DB::table('recipe_tag')
                    ->join('recipe', 'recipe_tag.recipe_id', '=', 'recipe.id')
                    ->select('recipe_tag.id', 'recipe.title', DB::raw("'Recipe' as type"), 'recipe.updated_at', 'recipe_tag.recipe_id AS tag_id')
                    ->where('recipe_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $recipe->Where('recipe.title', 'like', '%' . $title . '%');
                }

                $webinar = DB::table('webinar_tag')
                    ->join('webinar', 'webinar_tag.webinar_id', '=', 'webinar.id')
                    ->select('webinar_tag.id', 'webinar.title', DB::raw("'Webinar' as type"), 'webinar.updated_at', 'webinar_tag.webinar_id AS tag_id')
                    ->where('webinar_tag.goal_id', '=', $goal_id);
                if ($title) {
                    $webinar->Where('webinar.title', 'like', '%' . $title . '%');
                }

                $query = $feed->unionAll($course)
                    ->unionAll($meditation)
                    ->unionAll($recipe)
                    ->unionAll($webinar);

                if (isset($payload['order'])) {
                    $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                    $order  = $payload['order'][0]['dir'];
                    $query->orderBy($column, $order);
                } else {
                    $query->orderBy('updated_at', 'DESC');
                }
                break;
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $goalsInput = [
            'title' => $payload['title'],
        ];

        $record = self::create($goalsInput);

        if ($record) {
            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $record->id . '_' . \time();
                $record->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($payload['logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

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
        $goalsInput = [
            'title' => $payload['title'],
        ];

        $record = $this->update($goalsInput);

        if ($record) {
            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $this->id . '_' . \time();
                $this->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($payload['logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

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
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    public function getAssociatedGoalTags($goalId = "")
    {
        $goalRecords = self::select(
            "goals.*",
            DB::raw(" ((select COUNT(feed_tag.id) from feed_tag inner join feeds on feed_tag.feed_id = feeds.id where feed_tag.goal_id = goals.id AND CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone) <= CONVERT_TZ(now(), @@session.time_zone , feeds.timezone) AND CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone) >= CONVERT_TZ(now(), @@session.time_zone , feeds.timezone) ) + ( select COUNT(course_tag.id) from course_tag inner join courses on courses.id = course_tag.course_id  where  course_tag.goal_id = goals.id AND courses.status = 1 ) +( select COUNT(meditation_tracks_tag.id) from meditation_tracks_tag where  meditation_tracks_tag.goal_id = goals.id ) + ( select COUNT(recipe_tag.id) from recipe_tag inner join recipe on recipe.id = recipe_tag.recipe_id where  recipe_tag.goal_id = goals.id and recipe.status = 1)) as totalAssociated ")
        );
        if (!empty($goalId)) {
            $goalRecords = $goalRecords->where('goals.id', $goalId);
        }

        return $goalRecords->orderBy('goals.updated_at', 'DESC')
            ->having("totalAssociated", ">", 0)
            ->get();
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */
    public function deleteTypeRecord($id, $type)
    {
        switch ($type) {
            case 'Feed':
                $tableName = "feed_tag";
                break;
            case 'Masterclass':
                $tableName = "course_tag";
                break;
            case 'Meditation':
                $tableName = "meditation_tracks_tag";
                break;
            case 'Webinar':
                $tableName = "webinar_tag";
                break;
            default:
                $tableName = "recipe_tag";
                break;
        }
        if (DB::delete('delete from ' . $tableName . ' where id = ?', [$id])) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
