<?php

namespace App\Models;

use App\Models\User;
use App\Observers\PersonalChallengeObserver;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class PersonalChallenge extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_challenges';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'logo',
        'library_image_id',
        'title',
        'duration',
        'challenge_type',
        'type',
        'description',
        'recursive',
        'target_value',
        'deep_link_uri',
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
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
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(PersonalChallengeObserver::class);
    }

    /**
     * "belongs to" relation to `companies` table
     * via `company_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Companie::class, 'company_id');
    }

    /**
     * "belongs to" relation to `users` table
     * via `creator_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }

    /**
     * @return BelongsTo
     */
    public function libraryImage(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeImageLibrary', 'library_image_id');
    }

    /**
     * "has many" relation to `personal_challenge_tasks` table
     * via `personal_challenge_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function personalChallengeTasks(): HasMany
    {
        return $this->hasMany('App\Models\PersonalChallengeTask');
    }

    /**
     * "has many" relation to `personal_challenge_user_tasks` table
     * via `personal_challenge_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function personalChallengeUserTasks(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\PersonalChallengeTask', 'personal_challenge_user_tasks', 'personal_challenge_id', 'personal_challenge_tasks_id')
            ->withPivot('id', 'personal_challenge_user_id', 'date', 'completed', 'set_time')
            ->withTimestamps();
    }

    /**
     * "has many" relation to `personal_challenge_users` table
     * via `personal_challenge_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function personalChallengeUsers(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'personal_challenge_users', 'personal_challenge_id', 'user_id')
            ->withPivot('joined', 'start_date', 'end_date', 'reminder_at', 'completed', 'is_winner', 'id', 'recursive_count')
            ->withTimestamps();
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPersonalChallengeLogoAttribute()
    {
        return $this->getLogo(['w' => 160, 'h' => 80]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {

        if (!is_null($this->library_image_id)) {
            $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
        } else {
            $media = $this->getFirstMedia('logo');
        }
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $media->getURL();
        }
        return getThumbURL($params, 'personalChallenge', 'logo');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $media = null;
        if (!is_null($this->library_image_id)) {
            $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
        } else {
            $media = $this->getFirstMedia('logo');
        }
        return !empty($media) ? $media->name : 'Choose File';
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

        if ($collection == 'logo') {
            if (!is_null($this->library_image_id)) {
                $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
            } else {
                $media = $this->getFirstMedia('logo');
            }

            if (!is_null($media) && $media->count() > 0) {
                $param['src'] = $media->getURL();
            }
        } else {
            $media = $this->getFirstMedia($collection);
            if (!is_null($media) && $media->count() > 1) {
                $param['src'] = $this->getFirstMediaUrl($collection);
            }
        }

        $return['url'] = getThumbURL($param, 'personalChallenge', $collection);
        return $return;
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
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 3]);
        }

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
                return '<div class="table-img table-img-l"><img src="' . $record->personal_challenge_logo . '"/></div>';
            })
            ->addColumn('title', function ($record) {
                return $record->title;
            })
            ->addColumn('duration', function ($record) {
                return $record->duration;
            })
            ->addColumn('created_by', function ($record) {
                return $record->created_by;
            })
            ->addColumn('challenge_type', function ($record) {
                if ($record->challenge_type == 'routine') {
                    return 'Routine Plan';
                } elseif ($record->challenge_type == 'habit') {
                    return 'Habit Plan';
                } else {
                    return 'Personal Fitness Challenge';
                }
            })
            ->addColumn('type', function ($record) {
                return ucfirst($record->type);
            })
            ->addColumn('joined', function ($record) {
                return $record->personalChallengeUsers()->distinct('user_id')->count();
            })
            ->addColumn('actions', function ($record) {
                $user            = Auth::user();
                $company         = $user->company->first();
                $createdUser     = User::find($record->creator_id);
                $createUserRole  = getUserRole($createdUser);
                $roleGroup       = $createUserRole->slug;
                $activeUserCount = $record->personalChallengeUsers()->distinct('user_id')->count();
                if (!empty($company) && ($record->company_id != $company->id)) {
                    return;
                }
                return view('admin.personalChallenge.listaction', compact('record', 'roleGroup', 'activeUserCount'))->render();
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
        $company   = Auth::user()->company->first();
        $companyId = !empty($company) ? $company->id : null;

        $query = self::with(['personalChallengeUsers'])
            ->select(
                'personal_challenges.id',
                'personal_challenges.creator_id',
                'personal_challenges.company_id',
                'personal_challenges.title',
                'personal_challenges.library_image_id',
                'personal_challenges.duration',
                'personal_challenges.challenge_type',
                'personal_challenges.type',
                'personal_challenges.description',
                'personal_challenges.updated_at',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS created_by"),
                DB::raw('(SELECT count(id) FROM personal_challenge_users WHERE personal_challenge_id = `personal_challenges`.`id` AND start_date <= now() and end_date >= now()) AS active_user_counts')
            )
            ->join('users', 'users.id', '=', 'personal_challenges.creator_id')
            ->where(function ($query) use ($companyId) {
                if (!is_null($companyId)) {
                    return $query->where('personal_challenges.company_id', $companyId);
                } else {
                    return $query->where('personal_challenges.company_id', null);
                }
            })
            ->orderBy('personal_challenges.updated_at', 'DESC');

        if (in_array('challengeName', array_keys($payload)) && !empty($payload['challengeName'])) {
            $query->where('personal_challenges.title', 'like', '%' . $payload['challengeName'] . '%');
        }

        if (in_array('challengeType', array_keys($payload)) && !empty($payload['challengeType'])) {
            $query->where('personal_challenges.challenge_type', $payload['challengeType']);
        }
        if (in_array('subType', array_keys($payload)) && !empty($payload['subType'])) {
            $query->where('personal_challenges.type', $payload['subType']);
        }
        if (in_array('recursive', array_keys($payload)) && !empty($payload['recursive'])) {
            $query->where('personal_challenges.recursive', $payload['recursive']);
        }

        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $user = Auth::user();

        $personalChallengeInput = [
            'creator_id'     => $user->id,
            'company_id'     => !is_null($user->company->first()) ? $user->company->first()->id : null,
            'logo'           => $payload['logo']->getClientOriginalName(),
            'title'          => $payload['name'],
            'duration'       => $payload['duration'],
            'challenge_type' => $payload['challengetype'],
            'type'           => $payload['type'],
            'description'    => $payload['description'],
        ];

        $personalChallengeInput['recursive'] = (isset($payload['is_recursive'])) ? $payload['is_recursive'] : 0;

        if ($payload['challengetype'] == 'challenge') {
            $personalChallengeInput['target_value'] = $payload['target_value'];
        }

        $record = self::create($personalChallengeInput);

        if ($record) {
            if ($payload['challengetype'] == 'routine' || $payload['challengetype'] == 'habit') {
                $personalChallengeTaskInput = [];
                if ($record->type == 'to-do' && $payload['challengetype'] != 'habit') {
                    $tasks = $payload['ingredients'];

                    foreach ($tasks as $value) {
                        $personalChallengeTaskInput[] = [
                            'personal_challenge_id' => $record->id,
                            'task_name'             => $value,
                        ];
                    }
                } else {
                    $personalChallengeTaskInput = [
                        'personal_challenge_id' => $record->id,
                        'task_name'             => $payload['task'],
                    ];
                }

                $record->personalChallengeTasks()->insert($personalChallengeTaskInput);
            }

            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $record->id . '_' . \time();
                $record->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($name)
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            return true;
        }

        return false;
    }

    /**
     * For pre-populating data in edit personal challenge page.
     *
     * @param none
     * @return array
     */
    public function getUpdateData()
    {
        $data = array();

        $data['challengeData'] = $this;

        $data['id']          = $this->id;
        $data['creatorData'] = User::find($this->creator_id);
        $data['tasksData']   = $this->personalChallengeTasks()->pluck('personal_challenge_tasks.task_name', 'personal_challenge_tasks.id')->toArray();

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
        $personalChallengeInput = [
            'title'       => $payload['name'],
            'description' => $payload['description'],
            'description' => $payload['description'],
        ];

        if (isset($payload['logo'])) {
            $personalChallengeInput['logo'] = $payload['logo']->getClientOriginalName();
        }

        $record = $this->update($personalChallengeInput);

        if ($record) {
            if ($this->challenge_type == 'routine' || $this->challenge_type == 'habit') {
                $oldResult = $this->personalChallengeTasks()->where('personal_challenge_id', $this->id)->count();
                if ($oldResult > 0) {
                    $this->personalChallengeTasks()->where('personal_challenge_id', $this->id)->delete();
                }
                $personalChallengeTaskInput = [];
                $tasks                      = $payload['ingredients'];

                foreach ($tasks as $value) {
                    $personalChallengeTaskInput[] = [
                        'personal_challenge_id' => $this->id,
                        'task_name'             => $value,
                    ];
                }

                $this->personalChallengeTasks()->insert($personalChallengeTaskInput);
            }

            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $this->id . '_' . \time();
                $this->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($name)
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
                $this->library_image_id = null;
                $this->save();
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
}
