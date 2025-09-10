<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RunningChallengeDetailResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    protected $pointCalcRules;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $pointCalcRules = false)
    {
        $this->pointCalcRules = $pointCalcRules;

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
        $user         = $this->user();
        $userTimezone = $user->timezone;
        $appTimezone  = config('app.timezone');

        $currentDateTime = now($userTimezone);
        $startDate       = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
        $endDate         = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);

        $daysRange = \createDateRange($startDate, $endDate);

        $badgeData = $this->challengeBadges()->join('badge_user', 'badge_user.badge_id', '=', 'challenge_badges.badge_id')->where("challenge_badges.challenge_id", $this->id)->where("badge_user.user_id", $user->id)->where("badge_user.model_id", $this->id)->where("badge_user.model_name", 'challenge')->get();

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
        $totalPoints = 0;
        foreach ($challengeRulesData as $outer => $rule) {
            $challengeRulesArray = [];

            $exercisesName = "";

            if ($rule->challenge_target_id == 4 && !empty($rule->model_id)) {
                $exerciseObj = \App\Models\Exercise::where("id", $rule->model_id)->first();
                if (!empty($exerciseObj)) {
                    $exercisesName = " (".$exerciseObj->title.")";
                }
            }

            $challengeRulesArray['targetId']  = $rule->challenge_target_id;
            $challengeRulesArray['name']      = $rule->name.$exercisesName;
            $challengeRulesArray['targetUOM'] = $rule->uom;
            $challengeRulesArray['goal']      = $rule->target;

            $challengeRulesArray['data'] = [];
            $totalCount                  = 0;

            $dayWiseData = [];

            foreach ($daysRange as $inner => $day) {
                if ($day->toDateString() > now($userTimezone)->toDateString()) {
                    continue;
                }

                $daysData         = [];
                $daysData['date'] = $day->toDateString();

                if ($rule->short_name == 'distance') {
                    $count  = $user->getDistance($daysData['date'], $appTimezone, $userTimezone);
                    $points = round($count / $this->pointCalcRules['distance'], 1);
                } elseif ($rule->short_name == 'steps') {
                    $count  = $user->getSteps($daysData['date'], $appTimezone, $userTimezone);
                    $points = round($count / $this->pointCalcRules['steps'], 1);
                } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                    $count = $user->getExercises($daysData['date'], $appTimezone, $userTimezone, $rule->uom, $rule->model_id);

                    $point = 'exercises_duration';
                    if ($rule->uom == 'meter') {
                        $point = 'exercises_distance';
                    }

                    $points = round($count / $this->pointCalcRules[$point], 1);
                } elseif ($rule->short_name == 'meditations') {
                    $count  = $user->getMeditation($daysData['date'], $appTimezone, $userTimezone, $rule->uom);
                    $points = round($count / $this->pointCalcRules['meditations'], 1);
                }

                $daysData['value'] = (int) $count;

                $dayWiseData[$day->toDateString()] = (int) $count;

                $totalCount += $daysData['value'];
                $totalPoints += $points;

                array_push($challengeRulesArray['data'], $daysData);
            }

            if (strtolower($this->challengeCatName) == 'streak') {
                $totalCount = $dayWiseData[now($userTimezone)->toDateString()];
            }

            $challengeRulesArray['total'] = $totalCount;

            if ($this->challengeCatShortName == 'most') {
                $challengeRulesArray['remaining'] = ($totalCount);
            } else {
                $challengeRulesArray['remaining'] = ($rule->target - $totalCount);
            }

            array_push($targetArray, $challengeRulesArray);
        }

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => (!empty($this->description)) ? $this->description : "",
            'image'             => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'startDateTime'     => Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'endDateTime'       => Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'members'           => (!empty($this->members)) ? $this->members : 0,
            'isStarted'         => $isStarted,
            'isCompleted'       => $isCompleted,
            'creator'           => $this->getCreatorData(),
            'badges'            => ($badgeData->count() > 0) ? new BadgeListCollection($badgeData) : [],
            'challengeCategory' => array("id" => $this->challenge_category_id, "name" => $this->challengeCatName),
            'targets'           => $targetArray,
            'timerData'         => $timerData,
            'rank'              => $this->getUserRankInChallenge($this->pointCalcRules, $user),
            'points'            => round(($totalPoints + $usersExtraPoints), 1),
            'cancelled'         => ($this->cancelled) ,
        ];
    }
}
