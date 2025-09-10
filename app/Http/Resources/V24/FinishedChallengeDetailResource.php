<?php

namespace App\Http\Resources\V24;

use App\Http\Collections\V18\BadgeListCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FinishedChallengeDetailResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user                       = $this->user();
        $teamData                   = $user->teams()->first()->users()->get();
        $companyData                = $user->company()->first()->members()->get();
        $companyDataUserIdArray     = $companyData->pluck('id')->toArray();
        $userTimezone               = $user->timezone;
        $appTimezone                = config('app.timezone');
        $challenge_rule_description = config('zevolifesettings.challenge_rule_description');

        if ($this->finished ) {
            $freezedUserTeamId = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();
            if (empty($freezedUserTeamId)) {
                $freezedTeamUserList = $this->challengeWiseUserPoints()->where('team_id', $user->teams()->first()->id)->get()->pluck('user_id')->toArray();
            } else {
                $freezedTeamUserList = $this->challengeWiseUserPoints()->where('team_id', $freezedUserTeamId->team_id)->get()->pluck('user_id')->toArray();
            }
        }

        $teamCount = $this->finished  ? count($freezedTeamUserList) : $teamData->count();

        $currentDateTime = now($userTimezone);
        $startDate       = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
        $endDate         = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);

        $daysRange = \createDateRange($startDate, $endDate);

        $badgeData = Badge::select(
            'badges.id',
            'badges.title',
            'badges.description',
            'badge_user.id as badgeUserId',
            'badge_user.user_id',
            'badge_user.model_id',
            'badge_user.model_name'
        )->leftJoin('badge_user', 'badge_user.badge_id', '=', 'badges.id')
            ->where('badge_user.user_id', $user->id)
            ->where('badge_user.model_id', $this->id)
            ->where('badge_user.model_name', 'challenge')
            ->get();

        $isStarted   = false;
        $isCompleted = false;

        $startDate = $startDate->toDateTimeString();
        $endDate   = $endDate->toDateTimeString();

        if ($currentDateTime->between($startDate, $endDate)) {
            $isStarted = true;
        }

        if ($currentDateTime->greaterThan($endDate)) {
            $isCompleted = true;
        }

        $timerData = [];
        $timerData = calculatDayHrMin($currentDateTime->toDateTimeString(), $endDate);

        $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

        $usersExtraPoints = $this->challengeWiseManualPoints()->where('user_id', $user->getKey())->sum('points');

        $i = 0;

        $targetArray = [];
        foreach ($challengeRulesData as $outer => $rule) {
            $challengeRulesArray = [];
            $exercisesName       = "";

            if ($rule->challenge_target_id == 4 && !empty($rule->model_id)) {
                $exerciseObj = \App\Models\Exercise::where("id", $rule->model_id)->first();
                if (!empty($exerciseObj)) {
                    $exercisesName = $exerciseObj->title;
                }
            }

            $challengeRulesArray['targetId'] = $rule->challenge_target_id;
            if (!empty($exerciseObj) && $rule->challenge_target_id == 4) {
                $challengeRulesArray['name'] = $exercisesName;
            } else {
                $challengeRulesArray['name'] = $rule->name;
            }
            $challengeRulesArray['targetUOM'] = $rule->uom;
            $challengeRulesArray['goal']      = $rule->target;

            $ruleType = $rule->short_name;
            if ($rule->short_name == 'exercises') {
                if ($rule->uom == 'meter') {
                    $ruleType = 'exercises_distance';
                } else {
                    $ruleType = 'exercises_duration';
                }
            }

            if ($this->finished ) {
                $challengeSettingValue = $this->leftJoin('freezed_challenge_settings', 'freezed_challenge_settings.challenge_id', '=', 'challenges.id')
                    ->where('freezed_challenge_settings.type', $ruleType)
                    ->where('freezed_challenge_settings.challenge_id', $this->id)
                    ->pluck('freezed_challenge_settings.value')
                    ->first();
            } else {
                $challengeSettingValue = $this->leftJoin('companies', 'challenges.company_id', '=', 'companies.id')
                    ->leftJoin('company_wise_challenge_settings', 'company_wise_challenge_settings.company_id', '=', 'companies.id')
                    ->where('company_wise_challenge_settings.type', $ruleType)
                    ->where('company_wise_challenge_settings.company_id', $this->company_id)
                    ->pluck('company_wise_challenge_settings.value')
                    ->first();
            }

            if (empty($challengeSettingValue)) {
                $challengeSettingValues = config('zevolifesettings.default_limits');
                $challengeSettingValue  = $challengeSettingValues[$ruleType];
            }

            $challengeSettingTargetUOM = $challengeRulesArray['targetUOM'] == 'count' ? '' : ucfirst($challengeRulesArray['targetUOM']) . " of ";

            $challengeRulesArray['ruleText'] = $challengeSettingValue . " " . $challengeSettingTargetUOM . $challengeRulesArray['name'] . ' = 1 Point';

            $challengeRulesArray['goal'] = $rule->target;

            $challengeRulesArray['data'] = [];
            $totalCount                  = 0;
            $currentDayValueForStreak    = 0;
            $targetDayValueForStreak     = 0;
            foreach ($daysRange as $inner => $day) {
                if ($day->toDateString() > now($userTimezone)->toDateString()) {
                    continue;
                }

                $daysData         = [];
                $daysData['date'] = $day->toDateString();

                if ($rule->short_name == 'distance') {
                    if ($this->challenge_type == 'individual') {
                        $count = $user->getDistanceHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                    } elseif ($this->challenge_type == 'team') {
                        $count = 0;
                        if ($this->finished ) {
                            if (!empty($freezedTeamUserList)) {
                                $count = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $freezedTeamUserList)
                                    ->sum('distance');
                            }
                        } else {
                            foreach ($teamData as $key => $value) {
                                $count += $value->getDistanceHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                            }
                        }
                    } elseif ($this->challenge_type == 'company_goal') {
                        /*$count = 0;
                        foreach ($companyData as $key => $value) {
                        $count += $value->getDistanceHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                        }*/
                        $count = 0;
                        $count = $this->challengeHistorySteps()
                            ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                            ->whereIn('user_id', $companyDataUserIdArray)
                            ->sum('distance');
                    } elseif ($this->challenge_type == 'inter_company') {
                        $count = 0;
                        if ($this->finished ) {
                            $participantTeamIds = $this->memberTeamsHistory()->wherePivot('company_id', $user->company()->first()->getKey())->pluck('team_id')->toArray();

                            foreach ($participantTeamIds as $key => $value) {
                                $participantUserIds = $this->challengeWiseUserPoints()->where('team_id', $value)->get()->pluck('user_id')->toArray();

                                $participantUserCount = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $participantUserIds)
                                    ->sum('distance');

                                $count += count($participantUserIds) > 0 ? ($participantUserCount / count($participantUserIds)) : 0;
                            }
                            $count = (count($participantTeamIds) > 0 && !empty($count)) ? ($count / count($participantTeamIds)) : 0;
                        } else {
                            $participantTeamIds = $this->memberTeams()->wherePivot('company_id', $user->company()->first()->getKey())->pluck('team_id')->toArray();

                            foreach ($participantTeamIds as $key => $value) {
                                $participantUserIds = \App\Models\User::leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                                    ->where('user_team.team_id', $value)
                                    ->pluck('users.id')
                                    ->toArray();

                                $participantUserCount = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $participantUserIds)
                                    ->sum('distance');

                                $count += count($participantUserIds) > 0 ? ($participantUserCount / count($participantUserIds)) : 0;
                            }
                            $count = (count($participantTeamIds) > 0 && !empty($count)) ? ($count / count($participantTeamIds)) : 0;
                        }
                    }
                    //$points = round($count / $this->pointCalcRules['distance'], 1);
                } elseif ($rule->short_name == 'steps') {
                    if ($this->challenge_type == 'individual') {
                        $count = $user->getStepsHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                    } elseif ($this->challenge_type == 'team') {
                        $count = 0;
                        if ($this->finished ) {
                            if (!empty($freezedTeamUserList)) {
                                $count = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $freezedTeamUserList)
                                    ->sum('steps');
                            }
                        } else {
                            foreach ($teamData as $key => $value) {
                                $count += $value->getStepsHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                            }
                        }
                    } elseif ($this->challenge_type == 'company_goal') {
                        /*$count = 0;
                        foreach ($companyData as $key => $value) {
                        $count += $value->getStepsHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                        }*/
                        $count = 0;
                        $count = $this->challengeHistorySteps()
                            ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                            ->whereIn('user_id', $companyDataUserIdArray)
                            ->sum('steps');
                    } elseif ($this->challenge_type == 'inter_company') {
                        $count = 0;
                        if ($this->finished ) {
                            $participantTeamIds = $this->memberTeamsHistory()->wherePivot('company_id', $user->company()->first()->getKey())->pluck('team_id')->toArray();

                            foreach ($participantTeamIds as $key => $value) {
                                $participantUserIds = $this->challengeWiseUserPoints()->where('team_id', $value)->get()->pluck('user_id')->toArray();

                                $participantUserCount = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $participantUserIds)
                                    ->sum('steps');

                                $count += count($participantUserIds) > 0 ? ($participantUserCount / count($participantUserIds)) : 0;
                            }
                            $count = (count($participantTeamIds) > 0 && !empty($count)) ? ($count / count($participantTeamIds)) : 0;
                        } else {
                            $participantTeamIds = $this->memberTeams()->wherePivot('company_id', $user->company()->first()->getKey())->pluck('team_id')->toArray();

                            foreach ($participantTeamIds as $key => $value) {
                                $participantUserIds = \App\Models\User::leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                                    ->where('user_team.team_id', $value)
                                    ->pluck('users.id')
                                    ->toArray();

                                $participantUserCount = $this->challengeHistorySteps()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $participantUserIds)
                                    ->sum('steps');

                                $count += count($participantUserIds) > 0 ? ($participantUserCount / count($participantUserIds)) : 0;
                            }
                            $count = (count($participantTeamIds) > 0 && !empty($count)) ? ($count / count($participantTeamIds)) : 0;
                        }
                    }

                    //$points = round($count / $this->pointCalcRules['steps'], 1);
                } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                    if ($this->challenge_type == 'individual') {
                        $count = $user->getExercisesHistory($this, $daysData['date'], $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                    } elseif ($this->challenge_type == 'team') {
                        $count = 0;
                        if ($this->finished ) {
                            if (!empty($freezedTeamUserList)) {
                                $column = 'duration';
                                if ($rule->uom == 'meter') {
                                    $column = 'distance';
                                }
                                $date  = $daysData['date'];
                                $count = $this->challengeHistoryExercises()
                                    ->where(function ($q) use ($date, $appTimezone, $userTimezone, $freezedTeamUserList) {
                                        $q->whereDate(\DB::raw("CONVERT_TZ(freezed_challenge_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $date)
                                            ->whereDate(\DB::raw("CONVERT_TZ(freezed_challenge_exercise.end_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $date);
                                    })
                                    ->whereIn('user_id', $freezedTeamUserList)
                                    ->where('exercise_id', $rule->model_id)
                                    ->sum($column);

                                if ($column == 'duration') {
                                    $count = ($count / 60);
                                }
                            }
                        } else {
                            foreach ($teamData as $key => $value) {
                                $count += $value->getExercisesHistory($this, $daysData['date'], $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                            }
                        }
                    } else {
                        /*$count = 0;
                        foreach ($companyData as $key => $value) {
                        $count += $value->getExercisesHistory($this, $daysData['date'], $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                        }*/

                        $count  = 0;
                        $date   = $daysData['date'];
                        $column = 'duration';
                        if ($rule->uom == 'meter') {
                            $column = 'distance';
                        }
                        $count = $this->challengeHistoryExercises()
                            ->where(function ($q) use ($date, $appTimezone, $userTimezone) {
                                $q->whereDate(\DB::raw("CONVERT_TZ(freezed_challenge_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $date)
                                    ->whereDate(\DB::raw("CONVERT_TZ(freezed_challenge_exercise.end_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $date);
                            })
                            ->whereIn('user_id', $companyDataUserIdArray)
                            ->where('exercise_id', $rule->model_id)
                            ->sum($column);

                        if ($column == 'duration') {
                            $count = ($count / 60);
                        }
                    }

                    /*$point = 'exercises_duration';
                if ($rule->uom == 'meter') {
                $point = 'exercises_distance';
                }

                $points = round($count / $this->pointCalcRules[$point], 1);*/
                } elseif ($rule->short_name == 'meditations') {
                    if ($this->challenge_type == 'individual') {
                        $count = $user->getMeditationHistory($this, $daysData['date'], $appTimezone, $userTimezone, $rule->uom);
                    } elseif ($this->challenge_type == 'team') {
                        $count = 0;
                        if ($this->finished ) {
                            if (!empty($freezedTeamUserList)) {
                                $count = $this->challengeHistoryInspires()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_inspire.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                                    ->whereIn('user_id', $freezedTeamUserList)
                                    ->count();
                            }
                        } else {
                            foreach ($teamData as $key => $value) {
                                $count += $value->getMeditationHistory($this, $daysData['date'], $appTimezone, $userTimezone, $rule->uom);
                            }
                        }
                    } else {
                        /*$count = 0;
                        foreach ($companyData as $key => $value) {
                        $count += $value->getMeditationHistory($this, $daysData['date'], $appTimezone, $userTimezone);
                        }*/

                        $count = 0;
                        $count = $this->challengeHistoryInspires()
                            ->where(\DB::raw("DATE(CONVERT_TZ(freezed_challenge_inspire.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $daysData['date'])
                            ->whereIn('user_id', $companyDataUserIdArray)
                            ->count();
                    }
                    //$points = round($count / $this->pointCalcRules['meditations'], 1);
                }

                if ($this->challenge_type == 'team') {
                    $daysData['value'] = $teamCount > 0 ? (float) number_format(($count / $teamCount), 1, '.', '') : 0;
                } else {
                    $daysData['value'] = (float) number_format($count, 1, '.', '');
                }

                $totalCount += $daysData['value'];

                $currentDayValueForStreak = $daysData['value'];
                $targetDayValueForStreak += $challengeRulesArray['goal'];

                array_push($challengeRulesArray['data'], $daysData);
            }

            if ($this->challenge_type == 'team') {
                $challengeRulesArray['total'] = $teamCount > 0 ? (float) number_format(($totalCount), 1, '.', '') : 0;
                if ($this->challengeCatShortName == 'streak') {
                    if ($this->finished ) {
                        $currentDayValueForStreakCount = $teamCount > 0 ? ($totalCount) : 0;

                        $challengeRulesArray['remaining'] = (float) number_format(($targetDayValueForStreak - $currentDayValueForStreakCount), 1, '.', '');
                    } else {
                        $currentDayValueForStreakCount    = $teamCount > 0 ? ($currentDayValueForStreak) : 0;
                        $challengeRulesArray['remaining'] = (float) number_format(($rule->target - $currentDayValueForStreakCount), 1, '.', '');
                    }
                } elseif ($this->challengeCatShortName == 'most') {
                    $challengeRulesArray['remaining'] = $teamCount > 0 ? (float) number_format(($totalCount), 1, '.', '') : 0;
                } else {
                    $currentDayValueForStreakCount    = $teamCount > 0 ? ($totalCount) : 0;
                    $challengeRulesArray['remaining'] = (float) number_format(($rule->target - $currentDayValueForStreakCount), 1, '.', '');
                }
            } else {
                $challengeRulesArray['total'] = (float) number_format($totalCount, 1, '.', '');
                if ($this->challengeCatShortName == 'streak') {
                    if ($this->finished ) {
                        $challengeRulesArray['remaining'] = (float) number_format(($targetDayValueForStreak - $totalCount), 1, '.', '');
                    } else {
                        $challengeRulesArray['remaining'] = (float) number_format(($rule->target - $currentDayValueForStreak), 1, '.', '');
                    }
                } elseif ($this->challengeCatShortName == 'most') {
                    $challengeRulesArray['remaining'] = (float) number_format(($totalCount), 1, '.', '');
                } else {
                    $challengeRulesArray['remaining'] = (float) number_format(($rule->target - $totalCount), 1, '.', '');
                }
            }

            if ($this->challengeCatShortName == 'first_to_reach') {
                $challengeRulesArray['progressPercent'] = ($challengeRulesArray['total'] * 100) / $challengeRulesArray['goal'];
                $challengeRulesArray['progressPercent'] = $challengeRulesArray['progressPercent'] > 100 ? 100 : $challengeRulesArray['progressPercent'];
                $challengeRulesArray['progressPercent'] = (float) number_format($challengeRulesArray['progressPercent'], 1, '.', '');
            }
            array_push($targetArray, $challengeRulesArray);
        }

        $description = "";
        $ruleText    = "";

        $description = (!empty($this->description)) ? $this->description : "";

        if (!empty($this->challenge_category_id) && !empty($challenge_rule_description[$this->challenge_type]) && $challenge_rule_description[$this->challenge_type][$this->challenge_category_id]) {
            // $description .= "<br/><br/>Rule : " . $challenge_rule_description[$this->challenge_type][$this->challenge_category_id];
            $ruleText .= $challenge_rule_description[$this->challenge_type][$this->challenge_category_id];
        }

        if ($this->challenge_type == 'individual') {
            $pointsRecord = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $freezedTeamData = $this->challengeWiseUserPoints()->where('user_id', $user->id)->first();

            if (empty($freezedTeamData)) {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $user->teams()->first()->id)->first();
            } else {
                $pointsRecord = $this->challengeWiseTeamPoints()->where('team_id', $freezedTeamData->team_id)->first();
            }
        } elseif ($this->challenge_type == 'inter_company') {
            $pointsRecord = $this->challengeWiseCompanyPoints()->where('company_id', $user->company()->first()->id)->first();
        }

        $point = (!empty($pointsRecord) && !empty($pointsRecord->points)) ? $pointsRecord->points : 0;

        if ($this->finished ) {
            if ($this->challenge_type != 'inter_company') {
                $members = \DB::table('freezed_challenge_participents')->where('challenge_id', $this->id)->count();
            } else {
                $members = \DB::table('freezed_challenge_participents')->where('challenge_id', $this->id)->distinct('company_id')->pluck('company_id')->count();
            }
        } else {
            if ($this->challenge_type == 'individual') {
                $members = $this->members()->wherePivot('status', 'Accepted')->count();
            } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
                $members = $this->memberTeams()->count();
            } elseif ($this->challenge_type == 'inter_company') {
                $members = $this->memberCompanies()->distinct('company_id')->pluck('company_id')->count();
            }
        }

        unset($timerData['hour']);
        unset($timerData['minute']);
        $timerData['day'] = $timerData['day'] + 1;

        $headers = $request->headers->all();

        $version = config('zevolifesettings.version.api_version');
        // To call internal login API - creates request object
        $winnerListRequest = \Request::create('api/' . $version . '/challenge/winners/' . $this->id, 'GET', $headers);
        // dispatch created request to requested route  to get response
        $winnerListResponse = \Route::dispatch($winnerListRequest);

        $modelId     = !empty($this->parent_id) ? $this->parent_id : $this->id;
        $mappedGroup = Group::where('model_name', 'challenge')
            ->where('model_id', $modelId)
            ->where('is_visible', 1)
            ->where('is_archived', 0)
            ->first();

        $return = [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => $description,
            'ruleText'              => $ruleText,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'startDateTime'         => Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'endDateTime'           => Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'members'               => (!empty($members)) ? $members : 0,
            'isStarted'             => $isStarted,
            'isCompleted'           => $isCompleted,
            'creator'               => $this->getCreatorData(),
            'badges'                => ($badgeData->count() > 0) ? new BadgeListCollection($badgeData, false) : [],
            'challengeCategory'     => array("id" => $this->challenge_category_id, "name" => $this->challengeCatName),
            'targets'               => $targetArray,
            'timerData'             => $timerData,
            'rank'                  => (!empty($pointsRecord->rank)) ? $pointsRecord->rank : 0,
            'points'                => round($point, 1),
            'cancelled'             => ($this->cancelled) ? true : false,
            'type'                  => $this->challenge_type,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
            'winnerList'            => $winnerListResponse->original['result']['data'],
        ];

        if (isset($mappedGroup)) {
            $isMember = $mappedGroup->members()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            $isReported = $mappedGroup->groupReports()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            if (!$isReported) {
                $return['groupInfo'] = [
                    'groupId'  => $mappedGroup->id,
                    'isMember' => !empty($isMember),
                ];
            }
        }

        return $return;
    }
}
