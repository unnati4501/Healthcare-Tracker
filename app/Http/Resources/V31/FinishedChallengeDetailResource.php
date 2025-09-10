<?php

namespace App\Http\Resources\V31;

use App\Http\Collections\V27\ChallengeBadgeCollection;
use App\Http\Collections\V30\ChallengeMapCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use App\Models\Group;
use Carbon\Carbon;
use DB;
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

        $challengeType = $this->challenge_type;

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

        $currentAppDateTime = now(config('app.timezone'))->toDateTimeString();
        // Get challenge Badge Records with user achivement count
        $badgeData = Badge::select(
            'badges.id',
            'badges.title',
            'badges.type',
            'badges.description',
            'challenge_ongoing_badges.target as targetCount',
            'challenge_rules.uom as targetUnit',
            'challenge_ongoing_badges.in_days as challengeTargetDays',
            'challenges.end_date as challengeEndDate',
            'challenges.start_date as challengeStartDate',
            DB::raw('"' . $user->timezone . '" as userTimezone'),
            DB::raw("(SELECT id FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS badgeUserId"),
            DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS assignCount")
        )
            ->leftJoin('challenge_ongoing_badges', 'challenge_ongoing_badges.badge_id', '=', 'badges.id')
            ->leftJoin('challenges', 'challenges.id', '=', 'challenge_ongoing_badges.challenge_id')
            ->leftJoin('challenge_rules', 'challenge_rules.challenge_id', '=', 'challenges.id')
            ->where(function ($query) use ($challengeType) {
                $query->where('badges.challenge_type_slug', $challengeType)
                    ->where('badges.is_default', 1)
                    ->where('badges.type', 'challenge');
            })
            ->orWhere('challenge_ongoing_badges.challenge_id', $this->id)
            ->whereraw("IF ((badges.type !=  'challenge' and (SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') = 0), DATE_ADD(challenges.start_date, INTERVAL challenge_ongoing_badges.in_days DAY) > '$currentAppDateTime', challenges.start_date IS NOT NULL) ");

        //If challenge is completed then show the achived badges only
        if ($isCompleted ) {
            $badgeData = $badgeData->having("assignCount", '>', 0);
        }
        $badgeData = $badgeData->get();

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

        $checkChallengeTargetType = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_targets.short_name')->first();

        if ($checkChallengeTargetType->short_name == 'meditations') {
            $challenge_rule_description = config('zevolifesettings.challenge_rule_description_meditation');
        }

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

        //Show the team image for map challenge
        if ($this->challenge_type == 'team') {
            $team         = $user->teams()->first();
            $profileImage = $team->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]);
        } else {
            $profileImage = $user->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]);
        }
        
        // unset($timerData['hour']);
        // unset($timerData['minute']);
        // $timerData['day'] = $timerData['day'] + 1;

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

        $leaderBoard  = $this->getLeaderboard($this);
        $memberImages = $this->getMemberImages($this, $isStarted, $isCompleted);

        // Map challenge Code
        $mapProperty     = [];
        $mapChalengeFlag = false;
        if ($this->map_id != null) {
            $mapChallenge = $this->mapLibrary()->first();
            $mapProperty  = $mapChallenge->mapProperties()
                ->select(
                    'map_properties.*',
                    \DB::raw("'{$rule->short_name}' as shortName"),
                    \DB::raw("'{$targetArray[0]['total']}' as totalCount")
                )
                ->get();
            $mapChalengeFlag = true;
        }

        $remainingDistance = 0;
        $remainingStep     = 0;
        if (!empty($mapChallenge)) {
            $totalCount        = $targetArray[0]['total'];
            $totalDistance     = $mapChallenge->total_distance * 1000;
            $remainingDistance = ($totalCount > $totalDistance) ? 0 : (int) number_format(($totalDistance - $totalCount), 1, '.', '');
            $remainingStep     = ($totalCount > $mapChallenge->total_steps) ? 0 : (int) number_format(($mapChallenge->total_steps - $totalCount), 1, '.', '');

            if (!empty($mapProperty)) {
                $mapShortName = $checkChallengeTargetType->short_name;
                if ($totalCount >= $totalDistance && $mapShortName == 'distance') {
                    $isCompleted = true;
                } elseif ($totalCount > $mapChallenge->total_steps && $mapShortName == 'steps') {
                    $isCompleted = true;
                }
            }
        }

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
            'badges'                => ($badgeData->count() > 0) ? new ChallengeBadgeCollection($badgeData) : [],
            'challengeCategory'     => array("id" => $this->challenge_category_id, "name" => $this->challengeCatName),
            'targets'               => $targetArray,
            'timerData'             => $timerData,
            'rank'                  => (!empty($pointsRecord->rank)) ? $pointsRecord->rank : 0,
            'points'                => round($point, 1),
            'cancelled'             => ($this->cancelled) ? true : false,
            'type'                  => $this->challenge_type,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
            'winnerList'            => $winnerListResponse->original['result']['data'],
            'leaderboard'           => $this->when(!empty($leaderBoard), $leaderBoard),
            'memberImages'          => $memberImages,
            'isMapChallenge'        => $mapChalengeFlag,
            'mapLocations'          => $this->when(!empty($mapChallenge), [
                'totalSteps'        => (!empty($mapChallenge)) ? (int) $mapChallenge->total_steps : 0,
                'totalDistance'     => (!empty($mapChallenge)) ? (int) $mapChallenge->total_distance * 1000 : 0,
                'remainingSteps'    => (!empty($mapChallenge) && $checkChallengeTargetType->short_name == 'steps') ? (int) $remainingStep : 0,
                'remainingDistance' => (!empty($mapChallenge) && $checkChallengeTargetType->short_name == 'distance') ? (int) $remainingDistance : 0,
                'profileImage'      => $profileImage,
                'locations'         => (!empty($mapProperty)) ? (new ChallengeMapCollection($mapProperty)) : [],
            ]),
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

    /**
     * Get Leaderboard first object depand on challenge type.
     *
     * @param  $challenge
     * @return array
     */
    private function getLeaderboard($challenge)
    {
        $participantId = null;
        $userRecord    = [];
        $participant   = [];
        if ($challenge->challenge_type == 'individual') {
            $challengeParticipantsWithPoints = $challenge->challengeWiseUserPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                ->select('freezed_challenge_participents.user_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                ->where('challenge_wise_user_ponits.challenge_id', $challenge->id)
                ->where(DB::raw("round(challenge_wise_user_ponits.points,1)"), '>', 0)
                ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                ->orderBy('challenge_wise_user_ponits.user_id', 'ASC')
                ->groupBy('challenge_wise_user_ponits.user_id')
                ->first();

            if ($challengeParticipantsWithPoints) {
                $participantId = $challengeParticipantsWithPoints->user_id;
                $userRecord    = \App\Models\User::find($challengeParticipantsWithPoints->user_id);
                $name          = (!empty($userRecord->first_name) && !empty($userRecord->last_name)) ? $userRecord->first_name . ' ' . $userRecord->last_name : '';
            }
        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
            $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                ->select(
                    'freezed_challenge_participents.team_id',
                    'freezed_challenge_participents.participant_name',
                    'challenge_wise_team_ponits.challenge_id',
                    'challenge_wise_team_ponits.points',
                    'challenge_wise_team_ponits.rank'
                )
                ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                ->groupBy('challenge_wise_team_ponits.team_id')
                ->first();

            if ($challengeParticipantsWithPoints) {
                $participantId = $challengeParticipantsWithPoints->team_id;
                $userRecord    = \App\Models\Team::find($challengeParticipantsWithPoints->team_id);
                $name          = (!empty($userRecord->name) ? $userRecord->name : '');
            }
        } elseif ($challenge->challenge_type == 'inter_company') {
            $challengeParticipantsWithPoints = $challenge->challengeWiseCompanyPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.company_id', '=', 'challenge_wise_company_points.company_id')
                ->select(
                    'freezed_challenge_participents.company_id',
                    'freezed_challenge_participents.participant_name',
                    'challenge_wise_company_points.challenge_id',
                    'challenge_wise_company_points.points',
                    'challenge_wise_company_points.rank'
                )
                ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                ->where('challenge_wise_company_points.challenge_id', $challenge->id)
                ->where(DB::raw("round(challenge_wise_company_points.points,1)"), '>', 0)
                ->orderBy('challenge_wise_company_points.rank', 'ASC')
                ->orderBy('challenge_wise_company_points.company_id', 'ASC')
                ->groupBy('challenge_wise_company_points.company_id')
                ->first();

            if ($challengeParticipantsWithPoints) {
                $participantId = $challengeParticipantsWithPoints->company_id;
                $userRecord    = \App\Models\Company::find($challengeParticipantsWithPoints->company_id);
                $name          = (!empty($userRecord->name) ? $userRecord->name : '');
            }
        }

        if ($challengeParticipantsWithPoints && $userRecord) {
            $participant['id']    = $participantId;
            $participant['name']  = $name;
            $participant['image'] = $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $participant;
    }

    /**
     * Get Member images depand on challenge type and challenge started or ongoing status
     *
     * @param  $challenge
     * @return array
     */
    private function getMemberImages($challenge, $isStarted, $isCompleted)
    {
        $memberImages = [];
        if ($challenge->challenge_type == 'individual') {
            if (!$isStarted && !$isCompleted) {
                //Get image of members
                $participants = $challenge->members()->wherePivot("status", "Accepted")->get();
                if (!empty($participants)) {
                    foreach ($participants as $key => $value) {
                        if (!empty($value)) {
                            $participantImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'mI' => 1]);
                            array_push($memberImages, $participantImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseUserPoints()
                    ->with(['user'])->where('challenge_wise_user_ponits.challenge_id', $challenge->id)->orderby('points', 'Desc')->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $user = $value->user;
                        if (!empty($user)) {
                            $topScrorerImage = $user->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'mI' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
            if (!$isStarted && !$isCompleted) {
                //Get image of teams
                $participantTeams = $challenge->memberTeams()->get();
                if (!empty($participantTeams)) {
                    foreach ($participantTeams as $key => $value) {
                        if (!empty($value)) {
                            $participantTeamImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $participantTeamImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseTeamPoints()
                    ->with(['team'])->where('challenge_wise_team_ponits.challenge_id', $challenge->id)->orderby('points', 'Desc')->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $team = $value->team;
                        if (!empty($team)) {
                            $topScrorerImage = $team->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        } elseif ($challenge->challenge_type == 'inter_company') {
            if (!$isStarted && !$isCompleted) {
                //Get image of teams
                $participantsCompanies = $challenge->memberCompanies()->distinct('company_id')->groupBy('company_id')->get();
                if (!empty($participantsCompanies)) {
                    foreach ($participantsCompanies as $key => $value) {
                        if (!empty($value)) {
                            $participantsCompanyImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $participantsCompanyImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseCompanyPoints()
                    ->with(['company'])
                    ->select('challenge_wise_company_points.company_id')
                    ->where('challenge_wise_company_points.challenge_id', $challenge->id)
                    ->groupBy('challenge_wise_company_points.company_id')
                    ->orderBy('challenge_wise_company_points.points', 'Desc')
                    ->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $company = $value->company;
                        if (!empty($company)) {
                            $topScrorerImage = $company->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        }
        $topFourMembers = array_slice($memberImages, 0, 4);
        return $topFourMembers;
    }
}
