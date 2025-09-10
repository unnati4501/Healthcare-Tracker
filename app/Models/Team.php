<?php

namespace App\Models;

use App\Jobs\SendTeamChangePushNotification;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\User;
use App\Models\UserTeam;
use App\Observers\TeamObserver;
use App\Jobs\ExportTeamJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;
use DB;

class Team extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'department_id',
        'code',
        'name',
        'default',
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
    protected $casts = [
        'default' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(TeamObserver::class);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function createUniqueCode(): string
    {
        do {
            $code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        } while ((new static)->where('code', '=', $code)->count());

        return $code;
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo('App\Models\Department');
    }

    /**
     * @return BelongsTo
     */
    public function teamlocation(): belongsToMany
    {
        return $this->belongsToMany('App\Models\CompanyLocation', 'team_location', 'team_id', 'company_location_id');
    }

    /**
     * @return HasMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_team', 'team_id', 'user_id')->withPivot('company_id', 'department_id')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function challengeWiseTeamLogData(): HasMany
    {
        return $this->hasMany(ChallengeWiseUserLogData::class);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 320, 'h' => 320]);
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
        return getThumbURL($params, 'team', 'logo');
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();
        $list    = $this->getRecordList($payload, $role);

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('name', function ($team) use ($role, $company) {
                if (($role->group == 'company') || ($role->group == 'reseller' && !is_null($company->parent_id))) {
                    return "<a href='" . route('admin.users.index', ['referrer' => 'teams', 'team' => $team->id]) . "' title='View team members'>{$team->name}</a>";
                } else {
                    return $team->name;
                }
            })
            ->addColumn('logo', function ($team) {
                return '<div class="table-img table-img-l"><img src="' . $team->logo . '" alt=""></div>';
            })
            ->addColumn('actions', function ($team) {
                return view('admin.team.listaction', compact('team'))->render();
            })
            ->rawColumns(['name', 'actions', 'logo'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getRecordList($payload, $role)
    {
        $company = auth()->user()->company()->first();
        $query   = $this
            ->select(
                "teams.*",
                "companies.name AS company_name"
            )
            ->withCount('users')
            ->leftJoin('companies', 'companies.id', '=', 'teams.company_id')
            ->when(($payload['teamName'] ?? null), function ($query, $name) {
                $query->where('teams.name', 'like', "%{$name}%");
            })
            ->when(($payload['company'] ?? null), function ($query, $company) {
                $query->where('companies.id', $company);
            });

        if ($role->group == 'reseller') {
            $query
                ->where(function ($where) use ($company) {
                    $where
                        ->where('teams.company_id', $company->id)
                        ->orWhere('companies.parent_id', $company->id);
                });
        } elseif ($role->group != 'zevo') {
            $query->where('company_id', $company->id);
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderBy('teams.updated_at', 'DESC');
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
        $user    = auth()->user();
        $role    = getUserRole($user);
        $teamInput = [
            'name'          => $payload['name'],
            'company_id'    => $payload['company'],
            'department_id' => $payload['department'],
            'created_at'    => Carbon::now(),
        ];

        $teams = Team::create($teamInput);
        $companyAdded = Company::find($payload['company']);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $teams->id . '_' . \time();
            $teams
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($name)
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($teams) {
            if (array_key_exists('members_selected', $payload) && $role->slug == 'super_admin') {
                $memberSelected = $payload['members_selected'];
                foreach ($memberSelected as $value) {
                    $splitValue = explode('-', $value);
                    $masterId   = $splitValue[0];
                    $contentId  = $splitValue[count($splitValue) - 1];
                    switch ($masterId) {
                        case 1:
                            $masterclass_company[] = [
                                'masterclass_id' => $contentId,
                                'company_id'     => $payload['company'],
                                'created_at'     => Carbon::now(),
                            ];
                            break;
                        case 4:
                            $meditation_companyInput[] = [
                                'meditation_track_id' => $contentId,
                                'company_id'          => $payload['company'],
                                'created_at'          => Carbon::now(),
                            ];
                            break;
                        case 7:
                            $webinar_companyInput[] = [
                                'webinar_id' => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 2:
                            $feed_companyInput[] = [
                                'feed_id'    => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 9:
                            $podcast_companyInput[] = [
                                'podcast_id'     => $contentId,
                                'company_id'     => $payload['company'],
                                'created_at'     => Carbon::now(),
                            ];
                            break;
                        default:
                            $recipe_companyInput[] = [
                                'recipe_id'  => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                    }
                }
                if (!empty($masterclass_company)) {
                    DB::table('masterclass_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($masterclass_company, 1000) as $masterclassCompany) {
                        $companyAdded->masterclassCompany()->sync($masterclassCompany);
                    }
                }
                if (!empty($meditation_companyInput)) {
                    DB::table('meditation_tracks_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($meditation_companyInput, 1000) as $meditationCompany) {
                        $companyAdded->meditationcompany()->sync($meditationCompany);
                    }
                }
                if (!empty($webinar_companyInput)) {
                    DB::table('webinar_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($webinar_companyInput, 1000) as $webinarCompany) {
                        $companyAdded->webinarcompany()->sync($webinarCompany);
                    }
                }
                if (!empty($feed_companyInput)) {
                    DB::table('feed_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($feed_companyInput, 1000) as $feedCompany) {
                        $companyAdded->feedcompany()->sync($feedCompany);
                    }
                }
                if (!empty($recipe_companyInput)) {
                    DB::table('recipe_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($recipe_companyInput, 1000) as $recipeCompany) {
                        $companyAdded->recipecompany()->sync($recipeCompany);
                    }
                }
                if (!empty($podcast_companyInput)) {
                    DB::table('podcast_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($podcast_companyInput, 1000) as $podcastCompany) {
                        $companyAdded->podcastcompany()->sync($podcastCompany);
                    }
                }
            }

            $teamLocationInput[] = [
                'company_location_id' => $payload['teamlocation'],
                'company_id'          => $teams->company_id,
                'department_id'       => $teams->department_id,
                'team_id'             => $teams->id,
                'created_at'          => Carbon::now(),
            ];

            $teams->teamlocation()->sync($teamLocationInput);

            if ($role->slug == 'super_admin') {
                $teamLocation = TeamLocation::where('company_id', $teams->company_id)->where('team_id', $teams->id)->select('team_id')->get()->pluck('team_id')->toArray();
                foreach ($teamLocation as $teamVal) {
                    foreach ($memberSelected as $value) {
                        $splitValue = explode('-', $value);
                        $masterId   = $splitValue[0];
                        $contentId  = $splitValue[count($splitValue) - 1];
                        switch ($masterId) {
                            case 1:
                                $masterclass_teamInput[] = [
                                    'masterclass_id' => $contentId,
                                    'team_id'        => $teamVal,
                                    'created_at'     => Carbon::now(),
                                ];
                                break;
                            case 4:
                                $meditation_teamInput[] = [
                                    'meditation_track_id' => $contentId,
                                    'team_id'             => $teamVal,
                                    'created_at'          => Carbon::now(),
                                ];
                                break;
                            case 7:
                                $webinar_teamInput[] = [
                                    'webinar_id' => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                            case 2:
                                $feed_teamInput[] = [
                                    'feed_id'    => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                            case 9:
                                $podcast_teamInput[] = [
                                    'podcast_id'    => $contentId,
                                    'team_id'       => $teamVal,
                                    'created_at'    => Carbon::now(),
                                ];
                                break;
                            default:
                                $recipe_teamInput[] = [
                                    'recipe_id'  => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                        }
                    }
    
                    if (!empty($masterclass_teamInput)) {
                        DB::table('masterclass_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($masterclass_teamInput, 1000) as $masterclassTeam) {
                            DB::table('masterclass_team')->insert($masterclassTeam);
                        }
                    }
                    if (!empty($meditation_teamInput)) {
                        DB::table('meditation_tracks_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($meditation_teamInput, 1000) as $meditationTeam) {
                            DB::table('meditation_tracks_team')->insert($meditationTeam);
                        }
                    }
                    if (!empty($webinar_teamInput)) {
                        DB::table('webinar_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($webinar_teamInput, 1000) as $webinarTeam) {
                            DB::table('webinar_team')->insert($webinarTeam);
                        }
                    }
                    if (!empty($feed_teamInput)) {
                        DB::table('feed_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($feed_teamInput, 1000) as $feedTeam) {
                            DB::table('feed_team')->insert($feedTeam);
                        }
                    }
                    if (!empty($recipe_teamInput)) {
                        DB::table('recipe_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($recipe_teamInput, 1000) as $recipeTeam) {
                            DB::table('recipe_team')->insert($recipeTeam);
                        }
                    }
                    if (!empty($podcast_teamInput)) {
                        DB::table('podcast_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($podcast_teamInput, 1000) as $podcastTeam) {
                            DB::table('podcast_team')->insert($podcastTeam);
                        }
                    }
                }
            }
            
            $ongoingCompanyGoalChallenges = Challenge::where('company_id', $teams->company_id)
                ->where('challenge_type', 'company_goal')
                ->where('finished', 0)
                ->where('cancelled', 0)
                ->get();

            $ongoingCompanyGoalChallenges->each(function ($query) use ($teams) {
                $query->memberTeams()->attach($teams->id);
            });

            return $teams;
        }
        return false;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity(array $payload, $id)
    {
        $teamData       = Team::find($id);
        $companyAdded   = Company::find($payload['company']);
        $user           = auth()->user();
        $role           = getUserRole($user);
        if (!empty($teamData)) {
            $teamData->name          = $payload['name'];
            $teamData->company_id    = $payload['company'];
            $teamData->department_id = $payload['department'];
            $teamData->updated_at    = Carbon::now();

            $teamData->save();

            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $teamData->id . '_' . \time();
                $teamData
                    ->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($name)
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            if (array_key_exists('members_selected', $payload) && $role->slug == 'super_admin') {
                $memberSelected = $payload['members_selected'];
                foreach ($memberSelected as $value) {
                    $splitValue = explode('-', $value);
                    $masterId   = $splitValue[0];
                    $contentId  = $splitValue[count($splitValue) - 1];
                    switch ($masterId) {
                        case 1:
                            $masterclass_company[] = [
                                'masterclass_id' => $contentId,
                                'company_id'     => $payload['company'],
                                'created_at'     => Carbon::now(),
                            ];
                            break;
                        case 4:
                            $meditation_companyInput[] = [
                                'meditation_track_id' => $contentId,
                                'company_id'          => $payload['company'],
                                'created_at'          => Carbon::now(),
                            ];
                            break;
                        case 7:
                            $webinar_companyInput[] = [
                                'webinar_id' => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 2:
                            $feed_companyInput[] = [
                                'feed_id'    => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 9:
                            $podcast_companyInput[] = [
                                'podcast_id' => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        default:
                            $recipe_companyInput[] = [
                                'recipe_id'  => $contentId,
                                'company_id' => $payload['company'],
                                'created_at' => Carbon::now(),
                            ];
                            break;
                    }
                }
                
                if (!empty($masterclass_company)) {
                    DB::table('masterclass_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($masterclass_company, 1000) as $masterclassCompany) {
                        $companyAdded->masterclassCompany()->sync($masterclassCompany);
                    }
                }
                if (!empty($meditation_companyInput)) {
                    DB::table('meditation_tracks_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($meditation_companyInput, 1000) as $meditationCompany) {
                        $companyAdded->meditationcompany()->sync($meditationCompany);
                    }
                }
                if (!empty($webinar_companyInput)) {
                    DB::table('webinar_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($webinar_companyInput, 1000) as $webinarCompany) {
                        $companyAdded->webinarcompany()->sync($webinarCompany);
                    }
                }
                if (!empty($feed_companyInput)) {
                    DB::table('feed_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($feed_companyInput, 1000) as $feedCompany) {
                        $companyAdded->feedcompany()->sync($feedCompany);
                    }
                }
                if (!empty($recipe_companyInput)) {
                    DB::table('recipe_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($recipe_companyInput, 1000) as $recipeCompany) {
                        $companyAdded->recipecompany()->sync($recipeCompany);
                    }
                }
                if (!empty($podcast_companyInput)) {
                    DB::table('podcast_company')->where('company_id', $payload['company'])->delete();
                    foreach (array_chunk($podcast_companyInput, 1000) as $podcastCompany) {
                        $companyAdded->podcastcompany()->sync($podcastCompany);
                    }
                }
            }
    
            if ($role->slug == 'super_admin') {
                //Content assigned to team
                $teamLocation = TeamLocation::where('company_id', $payload['company'])->where('team_id', $teamData->id)->select('team_id')->get()->pluck('team_id')->toArray();
                foreach ($teamLocation as $teamVal) {
                    foreach ($memberSelected as $value) {
                        $splitValue = explode('-', $value);
                        $masterId   = $splitValue[0];
                        $contentId  = $splitValue[count($splitValue) - 1];
                        switch ($masterId) {
                            case 1:
                                $masterclass_teamInput[] = [
                                    'masterclass_id' => $contentId,
                                    'team_id'        => $teamVal,
                                    'created_at'     => Carbon::now(),
                                ];
                                break;
                            case 4:
                                $meditation_teamInput[] = [
                                    'meditation_track_id' => $contentId,
                                    'team_id'             => $teamVal,
                                    'created_at'          => Carbon::now(),
                                ];
                                break;
                            case 7:
                                $webinar_teamInput[] = [
                                    'webinar_id' => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                            case 2:
                                $feed_teamInput[] = [
                                    'feed_id'    => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                            case 9:
                                $podcast_teamInput[] = [
                                    'podcast_id'    => $contentId,
                                    'team_id'       => $teamVal,
                                    'created_at'    => Carbon::now(),
                                ];
                                break;
                            default:
                                $recipe_teamInput[] = [
                                    'recipe_id'  => $contentId,
                                    'team_id'    => $teamVal,
                                    'created_at' => Carbon::now(),
                                ];
                                break;
                        }
                    }

                    if (!empty($masterclass_teamInput)) {
                        DB::table('masterclass_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($masterclass_teamInput, 1000) as $masterclassTeam) {
                            DB::table('masterclass_team')->insert($masterclassTeam);
                        }
                    }
                    if (!empty($meditation_teamInput)) {
                        DB::table('meditation_tracks_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($meditation_teamInput, 1000) as $meditationTeam) {
                            DB::table('meditation_tracks_team')->insert($meditationTeam);
                        }
                    }
                    if (!empty($webinar_teamInput)) {
                        DB::table('webinar_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($webinar_teamInput, 1000) as $webinarTeam) {
                            DB::table('webinar_team')->insert($webinarTeam);
                        }
                    }
                    if (!empty($feed_teamInput)) {
                        DB::table('feed_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($feed_teamInput, 1000) as $feedTeam) {
                            DB::table('feed_team')->insert($feedTeam);
                        }
                    }
                    if (!empty($recipe_teamInput)) {
                        DB::table('recipe_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($recipe_teamInput, 1000) as $recipeTeam) {
                            DB::table('recipe_team')->insert($recipeTeam);
                        }
                    }
                    if (!empty($podcast_teamInput)) {
                        DB::table('podcast_team')->where('team_id', $teamVal)->delete();
                        foreach (array_chunk($podcast_teamInput, 1000) as $podcastTeam) {
                            DB::table('podcast_team')->insert($podcastTeam);
                        }
                    }
                }
            }
            
            if ($teamData) {
                $teamLocationInput[] = [
                    'company_location_id' => $payload['teamlocation'],
                    'company_id'          => $teamData->company_id,
                    'department_id'       => $teamData->department_id,
                    'team_id'             => $teamData->id,
                    'created_at'          => Carbon::now(),
                ];

                $teamData->teamlocation()->sync($teamLocationInput);

                return $teamData;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * get team edit data.
     *
     * @param  $id
     * @return array
     */

    public function teamEditData($id)
    {
        $data             = array();
        $data['id']       = $id;
        $data['teamData'] = Team::find($id);

        $company = $data['teamData']->company;

        $department = array();
        if (!empty($company->departments)) {
            foreach ($company->departments as $value) {
                $department[$value->id] = $value->name;
            }
        }
        $data['department'] = $department;

        $location = array();

        if (!empty($data['teamData']->department->departmentlocations)) {
            foreach ($data['teamData']->department->departmentlocations as $value) {
                $location[$value->id] = $value->name;
            }
        }
        $data['location']              = $location;
        $data['company'][$company->id] = $company->name;

        return $data;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        if ($this->users->count() > 0) {
            return array('deleted' => 'error');
        }

        $this->clearMediaCollection('logo');
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * duplication check for department data.
     *
     * @param $payload , $id
     * @return array
     */

    public function duplicationCheck($payload, $id = '')
    {
        $data = Team::where("name", $payload['name'])
            ->where("company_id", $payload['company']);
        if (!empty($id)) {
            $data = $data->where("id", "!=", $id);
        }

        return $data->first();
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
        $return['url'] = getThumbURL($param, 'team', $collection);
        return $return;
    }

    public function oldgetAssignmentTeamMembers($teams)
    {
        $teamMembers = [];
        $teams       = explode(',', $teams);
        $toTeam      = $teams[1];
        foreach ($teams as $team) {
            $team = $this->find($team);
            if ($team != null) {
                $members = $team->users;
                $members->each(function ($member) use (&$teamMembers, $team, $toTeam) {
                    $teamMembers[] = [
                        'value'    => $member->getKey(),
                        'user'     => "$member->first_name $member->last_name($member->email)",
                        'selected' => ($team->id == $toTeam),
                        'data'     => [
                            'currTeam' => $team->id,
                            'newTeam'  => $team->id,
                        ],
                    ];
                });
            }
        }
        return $teamMembers;
    }

    /**
     * To update users team and send push notification accordingly
     *
     * @param Array $usersData - user data along with team details
     * @return boolean
     */
    public function oldupdateUsersTeam($usersData)
    {
        $loggedInUser    = auth()->user();
        $fromteammembers = (!empty($usersData['fromteammembers']) ? explode(',', $usersData['fromteammembers']) : []);
        $toteammembers   = (!empty($usersData['toteammembers']) ? explode(',', $usersData['toteammembers']) : []);
        $toteam          = self::find($usersData['toteam']);
        $fromteam        = self::find($usersData['fromteam']);

        if (sizeof($fromteammembers) > 0) {
            foreach ($fromteammembers as $member) {
                UserTeam::where([
                    "user_id" => $member,
                    "department_id" => $usersData['todepartment'],
                    "team_id" => $usersData['toteam']
                ])->update([
                    "department_id" => $usersData['fromdepartment'],
                    "team_id" => $usersData['fromteam']
                ]);

                $user             = User::find($member);
                $role             = getUserRole($user);
                $groupRestriction = $user->company->first()->group_restriction;
                $adminOfGroups    = $user->groups()->get();

                if ($groupRestriction == 2 && $role->slug == 'user') {
                    foreach ($adminOfGroups as $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$user->id])->first();
                        $user->groups()->where('id', $value->id)->first()->members()->detach([$user->id]);
                        if (!empty($randomGroupMember)) {
                            $user->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }

                if ($fromteam->default) {
                    removeUserFromChallengeTypeGroups($user, $fromteam->company_id);
                }

                \dispatch(new SendTeamChangePushNotification($user, $fromteam->name, $loggedInUser->id, $usersData['fromteam']));
            }
        }

        if (sizeof($toteammembers) > 0) {
            foreach ($toteammembers as $member) {
                UserTeam::where(["user_id" => $member, "department_id" => $usersData['fromdepartment'], "team_id" => $usersData['fromteam']])
                    ->update(["department_id" => $usersData['todepartment'], "team_id" => $usersData['toteam']]);

                $user             = User::find($member);
                $role             = getUserRole($user);
                $groupRestriction = $user->company->first()->group_restriction;
                $adminOfGroups    = $user->groups()->get();

                if ($groupRestriction == 2 && $role->slug == 'user') {
                    foreach ($adminOfGroups as $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$user->id])->first();
                        $user->groups()->where('id', $value->id)->first()->members()->detach([$user->id]);
                        if (!empty($randomGroupMember)) {
                            $user->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }

                if ($toteam->default) {
                    removeUserFromChallengeTypeGroups($user, $toteam->company_id);
                }

                \dispatch(new SendTeamChangePushNotification($user, $toteam->name, $loggedInUser->id, $usersData['toteam']));
            }
        }
        return true;
    }

    /**
     * To get team members(can pass multiple team id by ,)
     *
     * @param String $teams
     * @return Json
     */
    public function getAssignmentTeamMembers($teams)
    {
        $user    = auth()->user();
        $company = $user->company()->select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')->first();
        $data    = ['limit' => 0];
        $teams   = explode(',', $teams);

        if ($company->auto_team_creation) {
            $data['limit'] = $company->team_limit;
        }

        foreach ($teams as $team) {
            $membersHtml = "";
            $team        = $this->find($team);
            if ($team != null) {
                $members = $team->users;
                $members->each(function ($member) use (&$membersHtml, $team) {
                    $membersHtml .= "<li data-id='{$member->id}' data-team='{$team->id}'><i class='fal fa-bars mr-2'></i>$member->full_name($member->email)</li>";
                });
                $data[$team->id]            = $membersHtml;
                $data['default'][$team->id] = $team->default;
            }
        }
        return $data;
    }

    /**
     * To update users team and send push notification accordingly
     *
     * @param Array $usersData - user data along with team details
     * @return boolean
     */
    public function updateUsersTeam($usersData)
    {
        $loggedInUser = auth()->user();
        $company      = $loggedInUser->company()
            ->select('companies.id', 'companies.group_restriction', 'companies.auto_team_creation', 'companies.team_limit')
            ->first();
        $survey = $company->survey()
            ->select('zc_survey_settings.id', 'zc_survey_settings.survey_to_all', 'zc_survey_settings.team_ids')
            ->first();
        $fromteammembers      = (!empty($usersData['fromteammembers']) ? explode(',', $usersData['fromteammembers']) : []);
        $toteammembers        = (!empty($usersData['toteammembers']) ? explode(',', $usersData['toteammembers']) : []);
        $fromTeammembersCount = sizeof($fromteammembers);
        $toteaMmembersCount   = sizeof($toteammembers);
        $toteam               = $this->withCount('users')->where('id', $usersData['toteam'])->first();
        $fromteam             = $this->withCount('users')->where('id', $usersData['fromteam'])->first();
        $fromTeamExistInTeams = $toTeamExistInTeams = true;

        // check if survey_to_all set to false and team isn't selected in config then remove user from survey users
        if (!empty($survey) && !$survey->survey_to_all) {
            $fromTeamExistInTeams = in_array($usersData['fromteam'], $survey->team_ids);
            $toTeamExistInTeams   = in_array($usersData['toteam'], $survey->team_ids);
        }

        // validate team limit if auto_team_creation is enabled
        if ($company->auto_team_creation && !$fromteam->default && ((($fromTeammembersCount + $fromteam->users_count) - $toteaMmembersCount) > $company->team_limit)) {
            return [
                'status'  => false,
                'message' => "{$fromteam->name} team has reached to team limit.",
            ];
        }

        // validate team limit if auto_team_creation is enabled
        if ($company->auto_team_creation && !$toteam->default && ((($toteaMmembersCount + $toteam->users_count) - $fromTeammembersCount) > $company->team_limit)) {
            return [
                'status'  => false,
                'message' => "{$toteam->name} team has reached to team limit.",
            ];
        }

        if ($fromTeammembersCount > 0) {
            foreach ($fromteammembers as $member) {
                $user = User::select('id', 'timezone')->find($member);
                $role = getUserRole($user);

                UserTeam::where([
                    "user_id" => $member,
                    "department_id" => $usersData['todepartment'],
                    "team_id" => $usersData['toteam']
                ])->update([
                    "department_id" => $usersData['fromdepartment'],
                    "team_id" => $usersData['fromteam']
                ]);

                if (!$fromTeamExistInTeams && $toTeamExistInTeams) {
                    // if user is being moved to team which isn't present in survey teams then remove from survey users
                    $company->surveyUsers()->detach($member);
                } elseif ($fromTeamExistInTeams && !$toTeamExistInTeams) {
                    // if user is being moved to team which is part of survey teams then add user to survey users
                    $company->surveyUsers()->attach($member);
                }

                if ($company->group_restriction == 2 && $role->slug == 'user') {
                    $adminOfGroups = $user->groups()->get();
                    foreach ($adminOfGroups as $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$user->id])->first();
                        $user->groups()->where('id', $value->id)->first()->members()->detach([$user->id]);
                        if (!empty($randomGroupMember)) {
                            $user->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }

                if ($fromteam->default) {
                    removeUserFromChallengeTypeGroups($user, $fromteam->company_id);
                }

                \dispatch(new SendTeamChangePushNotification($user, $fromteam->name, $loggedInUser->id, $usersData['fromteam']));
            }

            if ($company->auto_team_creation && !$fromteam->default) {
                $lastMember = last($fromteammembers);
                $lastMember = UserTeam::where('user_id', $lastMember)->first();
                event('eloquent.created: App\Models\UserTeam', $lastMember);
            }
        }

        if ($toteaMmembersCount > 0) {
            foreach ($toteammembers as $member) {
                $user = User::select('id', 'timezone')->find($member);
                $role = getUserRole($user);

                UserTeam::where([
                    "user_id" => $member,
                    "department_id" => $usersData['fromdepartment'],
                    "team_id" => $usersData['fromteam']
                ])->update([
                    "department_id" => $usersData['todepartment'],
                    "team_id" => $usersData['toteam']
                ]);

                if ($fromTeamExistInTeams && !$toTeamExistInTeams) {
                    // if user is being moved to team which isn't present in survey teams then remove from survey users
                    $company->surveyUsers()->detach($member);
                } elseif (!$fromTeamExistInTeams && $toTeamExistInTeams) {
                    // if user is being moved to team which is part of survey teams then add user to survey users
                    $company->surveyUsers()->attach($member);
                }

                if ($company->group_restriction == 2 && $role->slug == 'user') {
                    $adminOfGroups = $user->groups()->get();
                    foreach ($adminOfGroups as $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$user->id])->first();
                        $user->groups()->where('id', $value->id)->first()->members()->detach([$user->id]);
                        if (!empty($randomGroupMember)) {
                            $user->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }

                if ($toteam->default) {
                    removeUserFromChallengeTypeGroups($user, $toteam->company_id);
                }

                \dispatch(new SendTeamChangePushNotification($user, $toteam->name, $loggedInUser->id, $usersData['toteam']));
            }

            if ($company->auto_team_creation && !$toteam->default) {
                $lastMember = last($toteammembers);
                $lastMember = UserTeam::where('user_id', $lastMember)->first();
                event('eloquent.created: App\Models\UserTeam', $lastMember);
            }
        }

        return [
            'status'  => true,
            'message' => '',
        ];
    }

    public function exportTeamDataEntity($payload)
    {
        $user        = auth()->user();
        \dispatch(new ExportTeamJob($payload, $user));
        return true;
    }
}
