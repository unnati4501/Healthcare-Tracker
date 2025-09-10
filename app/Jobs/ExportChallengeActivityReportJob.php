<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\ChallengeActivityReportExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportChallengeActivityReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    public $user;
    public $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, User $user)
    {
        $this->queue                  = 'mail';
        $this->payload                = $payload;
        $this->user                   = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            $queryStr    = json_decode($this->payload['queryString'], true);
            
            if (!empty($this->payload['start_date'])) {
                $startDate = $this->payload['start_date'] . '00:00';
                $startDate = Carbon::parse($startDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            if (!empty($this->payload['end_date'])) {
                $endDate   = $this->payload['end_date'] . '23:59:59';
                $endDate   = Carbon::parse($endDate, $timezone)->setTimeZone(config('app.timezone'))->toDateTimeString();
            }
            $challengeId        = $queryStr['challenge'];
                $challenge          = Challenge::find($challengeId);
                $challengeRulesData = $challenge->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();
                $record             = array();

                $pointCalcRules  = [];
                $challengeStatus = $queryStr['challengeStatus'];
            if ($challenge->challenge_type != 'inter_company') {
                if ($challengeStatus == "ongoing") {
                    $companyWiseChallengeSett = $challenge->company->companyWiseChallengeSett();
                } else {
                    $companyWiseChallengeSett = $challenge->challengeHistorySettings();
                }

                if ($companyWiseChallengeSett->count() > 0) {
                    $pointCalcRules = $companyWiseChallengeSett->pluck('value', 'type')->toArray();
                }
            }

            if (empty($pointCalcRules)) {
                $pointCalcRules = config('zevolifesettings.default_limits');
            }
            if ($queryStr['tab'] == "summary") {
                if ($challenge->challenge_type == "individual") {
                    $queryArray = array();
                    foreach ($challengeRulesData as $value) {
                        if ($value->short_name == "steps" || $value->short_name == "distance") {
                            $columnName = "freezed_challenge_steps." . $value->short_name;
        
                            $queryArray[] = DB::table("freezed_challenge_steps")
                                ->select(DB::raw("sum({$columnName})"))
                                ->whereRaw("freezed_challenge_steps.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_challenge_steps.user_id = freezed_challenge_participents.user_id");
                        } elseif ($value->short_name == 'meditations') {
                            $queryArray[] = DB::table("freezed_challenge_inspire")
                                ->select(DB::raw("count(freezed_challenge_inspire.meditation_track_id)"))
                                ->whereRaw("freezed_challenge_inspire.challenge_id = ?",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_challenge_inspire.user_id = freezed_challenge_participents.user_id");
                        } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                            $column = 'freezed_challenge_exercise.duration';
                            if ($value->uom == 'meter') {
                                $column = 'freezed_challenge_exercise.distance';
                            }
        
                            $exerciseQuery = DB::table("freezed_challenge_exercise")
                                ->whereRaw("freezed_challenge_exercise.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_challenge_exercise.exercise_id = ?",[
                                    $value->model_id
                                ])
                                ->whereRaw("freezed_challenge_exercise.user_id = freezed_challenge_participents.user_id");
                            if ($value->uom == "meter") {
                                $exerciseQuery
                                    ->select(DB::raw(" sum({$column})"));
                            } else {
                                $exerciseQuery
                                    ->select(DB::raw("sum({$column}) / 60 "));
                            }
                            $queryArray[] = $exerciseQuery;
                        }
                    }
                    if ($challengeRulesData[0]->short_name != 'exercises') {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("IFNULL(({$queryArray[0]->toSql()}), 0) as valueCount"),
                            DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "steps" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "distance" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "meditations" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' ELSE "500" END),0) AS points'),
                        )
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    } else {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("IFNULL(({$queryArray[0]->toSql()}), 0) as valueCount"),
                            DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" = "meter" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" != "meter" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules['exercises_duration'].' ELSE "500" END),0) AS points'),
                        )
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    }
                   
                    if (!empty($queryStr['team'])) {
                        $firstRuleData->where("teams.id", $queryStr['team']);
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        if ($challengeRulesData[1]->short_name != 'exercises') {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("IFNULL(({$queryArray[1]->toSql()}), 0) as valueCount"),
                                DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "steps" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "distance" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "meditations" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' ELSE "500" END),0) AS points'),
                            )
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        } else {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("IFNULL(({$queryArray[1]->toSql()}), 0) as valueCount"),
                                DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" = "meter" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" != "meter" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules['exercises_duration'].' ELSE "500" END),0) AS points'),
                            )
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        }
                        if (!empty($queryStr['team'])) {
                            $secondRuleData->where("teams.id", $queryStr['team']);
                        }
                    }
                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }
                    $userNPSDataRecords = $record->get()->chunk(100);
                    if ($record->get()->count() == 0) {
                        $messageData = [
                            'data'   => trans('challenges.messages.export_error'),
                            'status' => 0,
                        ];
                        return \Redirect::route('admin.reports.challengeactivityreport')->with('message', $messageData);
                    }

                    $sheetTitle = [
                        'User Name',
                        'Email',
                        'Team Name',
                        'Target Type',
                        'Counts',
                        'points',
                    ];
                } else {
                    $queryArray  = array();
                    $companyGoal = ($challenge->challenge_type == "company_goal") ? 'true' : 'false';
                    
                    foreach ($challengeRulesData as $value) {
                        if ($value->short_name == "steps" || $value->short_name == "distance") {
                            $columnName = "freezed_challenge_steps." . $value->short_name;

                            $queryArray[] = DB::table("freezed_challenge_steps")
                                ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_steps.user_id", "=", "freezed_team_challenge_participents.user_id")
                                ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                                sum({$columnName}),
                                                sum({$columnName})
                                                /
                                                    (
                                                    select count(freezed_team_challenge_participents.user_id)
                                                        from freezed_team_challenge_participents
                                                        where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                        and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                    )
                                                ) "))
                                ->whereRaw("freezed_challenge_steps.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                        } elseif ($value->short_name == 'meditations') {
                            $queryArray[] = DB::table("freezed_challenge_inspire")
                                ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_inspire.user_id", "=", "freezed_team_challenge_participents.user_id")
                                ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                                count(freezed_challenge_inspire.meditation_track_id),
                                                count(freezed_challenge_inspire.meditation_track_id)
                                                /
                                                    (
                                                    select count(freezed_team_challenge_participents.user_id)
                                                    from freezed_team_challenge_participents
                                                    where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                    and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                    )
                                                ) "))
                                ->whereRaw("freezed_challenge_inspire.challenge_id = ?",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                        } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                            $column = 'freezed_challenge_exercise.duration';
                            if ($value->uom == 'meter') {
                                $column = 'freezed_challenge_exercise.distance';
                            }

                            $exerciseQuery = DB::table("freezed_challenge_exercise")
                                ->whereRaw("freezed_challenge_exercise.exercise_id = ?",[
                                    $value->model_id
                                ])
                                ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_exercise.user_id", "=", "freezed_team_challenge_participents.user_id")
                                ->whereRaw("freezed_challenge_exercise.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.challenge_id = ? ",[
                                    $challenge->id
                                ])
                                ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                            if ($value->uom == "meter") {
                                $exerciseQuery
                                    ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                                    sum({$column}),
                                                    sum({$column})
                                                    /
                                                    (
                                                        select count(freezed_team_challenge_participents.user_id)
                                                        from freezed_team_challenge_participents
                                                        where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                        and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                    )
                                                ) "));
                            } else {
                                $exerciseQuery
                                    ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                                sum({$column}),
                                                sum({$column})
                                                /
                                                (
                                                    select count(freezed_team_challenge_participents.user_id)
                                                    from freezed_team_challenge_participents
                                                    where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                    and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                )
                                            ) / 60 "));
                            }
                            $queryArray[] = $exerciseQuery;
                        }
                    }
                    if ($challengeRulesData[0]->short_name != 'exercises') {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            "companies.name as company",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("IFNULL(({$queryArray[0]->toSql()}), 0) as valueCount"),
                            DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "steps" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "distance" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "meditations" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[0]->short_name].' ELSE "500" END), 0) AS points'),
                        )
                        ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    } else {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            "companies.name as company",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("IFNULL(({$queryArray[0]->toSql()}), 0) as valueCount"),
                            DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" = "meter" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" != "meter" THEN '.DB::raw("({$queryArray[0]->toSql()})").'/'.$pointCalcRules['exercises_duration'].' ELSE "500" END), 0) AS points'),
                        )
                        ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    }
                        
                           
                    if ($challenge->challenge_type == "inter_company" && !empty($queryStr['company'])) {
                        $firstRuleData->where("companies.id", $queryStr['company']);
                    }

                    if (!empty($queryStr['team'])) {
                        $firstRuleData->where("teams.id", $queryStr['team']);
                    }
                    
                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        if ($challengeRulesData[1]->short_name != 'exercises') {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                "companies.name as company",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("IFNULL(({$queryArray[1]->toSql()}), 0) as valueCount"),
                                DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "steps" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "distance" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "meditations" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules[$challengeRulesData[1]->short_name].' ELSE "500" END), 0) AS points'),
                            )
                            ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        } else {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                "companies.name as company",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("IFNULL(({$queryArray[1]->toSql()}), 0) as valueCount"),
                                DB::raw('IFNULL((CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" = "meter" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" != "meter" THEN '.DB::raw("({$queryArray[1]->toSql()})").'/'.$pointCalcRules['exercises_duration'].' ELSE "500" END),0) AS points'),
                            )
                            ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        }
                        

                        if ($challenge->challenge_type == "inter_company" && !empty($queryStr['company'])) {
                            $secondRuleData->where("companies.id", $queryStr['company']);
                        }

                        if (!empty($queryStr['team'])) {
                            $secondRuleData->where("teams.id", $queryStr['team']);
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }
                    $userNPSDataRecords = $record->get()->chunk(100);
                    
                    if ($record->get()->count() == 0) {
                        $messageData = [
                            'data'   => trans('challenges.messages.export_error'),
                            'status' => 0,
                        ];
                        return \Redirect::route('admin.reports.challengeactivityreport')->with('message', $messageData);
                    }

                    $sheetTitle = [
                        'Company Name',
                        'Team Name',
                        'Target Type',
                        'Counts',
                        'points',
                    ];
                }
                $dateTimeString = Carbon::now()->toDateTimeString();
                $fileName = "Challenge_activity_summary_".$this->user['full_name']."_".$dateTimeString.'.xlsx';
            } else { //if ($this->payload['tab'] == "details")
                $challengeId        = $queryStr['challenge'];
                $challenge          = Challenge::find($challengeId);
                $challengeRulesData = $challenge->challengeRules()
                    ->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')
                    ->select(
                        'challenge_rules.id',
                        'challenge_rules.challenge_target_id',
                        'challenge_rules.uom',
                        'challenge_rules.model_id',
                        'challenge_rules.model_name',
                        'challenge_targets.short_name',
                        'challenge_targets.name'
                    )
                    ->get();

                $record = array();

                $queryArray = array();
                foreach ($challengeRulesData as $value) {
                    if ($value->short_name == "steps" || $value->short_name == "distance") {
                        $columnName = "freezed_challenge_steps." . $value->short_name;
        
                        $selectQuery = DB::table("freezed_challenge_steps")
                            ->select(
                                DB::raw("'{$value->uom}' as uom"),
                                DB::raw("'0' as model_id"),
                                DB::raw("sum({$columnName}) as userVal"),
                                "freezed_challenge_steps.tracker",
                                "freezed_challenge_steps.log_date",
                                "freezed_challenge_steps.created_at",
                                "freezed_challenge_steps.user_id",
                                DB::raw("'{$value->short_name}' as columnName")
                            )
                            ->whereRaw("freezed_challenge_steps.challenge_id = ?",[
                                $challenge->id
                            ]);
        
                        $queryArray[] = $selectQuery
                            ->groupBy("user_id")
                            ->groupBy(DB::raw("DATE(log_date)"));
                    } elseif ($value->short_name == 'meditations') {
                        $queryArray[] = DB::table("freezed_challenge_inspire")
                            ->select(
                                DB::raw("'{$value->uom}' as uom"),
                                DB::raw("'0' as model_id"),
                                DB::raw("count(freezed_challenge_inspire.meditation_track_id) as userVal"),
                                DB::raw("'NA' as tracker"),
                                "freezed_challenge_inspire.log_date",
                                "freezed_challenge_inspire.created_at",
                                "freezed_challenge_inspire.user_id",
                                DB::raw("'{$value->short_name}' as columnName")
                            )
                            ->whereRaw("freezed_challenge_inspire.challenge_id = ?",[
                                $challenge->id
                            ])
                            ->groupBy("user_id")
                            ->groupBy(DB::raw("DATE(log_date)"));
                    } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                        $column = 'freezed_challenge_exercise.duration';
                        if ($value->uom == 'meter') {
                            $column = 'freezed_challenge_exercise.distance';
                        }
        
                        $exerciseQuery = DB::table("freezed_challenge_exercise")
                            ->whereRaw("freezed_challenge_exercise.challenge_id = ?",[
                                $challenge->id
                            ])
                            ->whereRaw("freezed_challenge_exercise.exercise_id = ?",[
                                $value->model_id
                            ])
                            ->groupBy("user_id")
                            ->groupBy(DB::raw("DATE(start_date)"));
                        if ($value->uom == "meter") {
                            $exerciseQuery
                                ->select(
                                    DB::raw("'{$value->uom}' as uom"),
                                    DB::raw("freezed_challenge_exercise.exercise_id as model_id"),
                                    DB::raw("sum({$column}) userVal"),
                                    "freezed_challenge_exercise.tracker",
                                    "freezed_challenge_exercise.start_date as log_date",
                                    "freezed_challenge_exercise.created_at",
                                    "freezed_challenge_exercise.user_id",
                                    DB::raw("'distance' as columnName")
                                );
                        } else {
                            $exerciseQuery
                                ->select(
                                    DB::raw("'{$value->uom}' as uom"),
                                    DB::raw("freezed_challenge_exercise.exercise_id as model_id"),
                                    DB::raw("ROUND((sum({$column}) / 60), 2) userVal"),
                                    "freezed_challenge_exercise.tracker",
                                    "freezed_challenge_exercise.start_date as log_date",
                                    "freezed_challenge_exercise.created_at",
                                    "freezed_challenge_exercise.user_id",
                                    DB::raw("'duration' as columnName")
                                );
                        }
                        $queryArray[] = $exerciseQuery;
                    }
                }
                if ($challenge->challenge_type == "individual") {
                    if ($challengeRulesData[0]->short_name != 'exercises') {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "companies.name as company",
                            "teams.name as team",
                            "valTable.tracker",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.userVal as counts",
                            DB::raw('(CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "steps" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "distance" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "meditations" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' ELSE "500" END) AS points'),
                            "valTable.log_date",
                            "valTable.created_at",
                        )
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    } else {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "companies.name as company",
                            "teams.name as team",
                            "valTable.tracker",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.userVal as counts",
                            DB::raw('(CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" = "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" != "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_duration'].' ELSE "500" END) AS points'),
                            "valTable.log_date",
                            "valTable.created_at",
                        )
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    }
                   

                    if (!empty($queryStr['team'])) {
                        $firstRuleData->where("teams.id", $queryStr['team']);
                    }

                    if (!empty($startDate) && !empty($endDate)) {
                        $firstRuleData->whereBetween('valTable.log_date', [$startDate, $endDate]);
                    } elseif (!empty($startDate) && empty($endDate)) {
                        $firstRuleData->where('valTable.log_date', '>=', $startDate);
                    } elseif (empty($startDate) && !empty($endDate)) {
                        $firstRuleData->where('valTable.log_date', '<=', $endDate);
                    }

                    if (!empty($queryStr['userrecordsearch'])) {
                        $userrecordsearch = $queryStr['userrecordsearch'];
                        $firstRuleData->where(function ($query) use ($userrecordsearch) {
                            $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                        });
                    }
                    
                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        if ($challengeRulesData[1]->short_name != 'exercises') {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                "companies.name as company",
                                "valTable.tracker",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.userVal as counts",
                                DB::raw('(CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "steps" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "distance" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "meditations" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' ELSE "500" END) AS points'),
                                "valTable.log_date",
                                "valTable.created_at",
                            )
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        } else {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                "companies.name as company",
                                "valTable.tracker",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.userVal as counts",
                                DB::raw('(CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" = "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" != "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_duration'].' ELSE "500" END) AS points'),
                                "valTable.log_date",
                                "valTable.created_at",
                            )
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        }
                        

                        if (!empty($queryStr['team'])) {
                            $secondRuleData->where("teams.id", $queryStr['team']);
                        }

                        if (!empty($startDate) && !empty($endDate)) {
                            $secondRuleData->whereBetween('valTable.log_date', [$startDate, $endDate]);
                        } elseif (!empty($startDate) && empty($endDate)) {
                            $secondRuleData->where('valTable.log_date', '>=', $startDate);
                        } elseif (empty($startDate) && !empty($endDate)) {
                            $secondRuleData->where('valTable.log_date', '<=', $endDate);
                        }

                        if (!empty($queryStr['userrecordsearch'])) {
                            $userrecordsearch = $queryStr['userrecordsearch'];
                            $secondRuleData->where(function ($query) use ($userrecordsearch) {
                                $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                    ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                            });
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }
                    $userNPSDataRecords = $record->get()->chunk(100);
                    if ($record->get()->count() == 0) {
                        $messageData = [
                            'data'   => trans('challenges.messages.export_error'),
                            'status' => 0,
                        ];
                        return \Redirect::route('admin.reports.challengeactivityreport')->with('message', $messageData);
                    }
                } else {
                    if ($challengeRulesData[0]->short_name != 'exercises') {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "companies.name as company",
                            "teams.name as team",
                            "valTable.tracker",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.userVal as counts",
                            DB::raw('(CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "steps" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "distance" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' WHEN "'.$challengeRulesData[0]->short_name.'" = "meditations" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[0]->short_name].' ELSE "500" END) AS points'),
                            "valTable.log_date",
                            "valTable.created_at",
                        )
                        ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    } else {
                        $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "companies.name as company",
                            "teams.name as team",
                            "valTable.tracker",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.userVal as counts",
                            DB::raw('(CASE WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" = "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[0]->short_name.'" = "exercises" AND "'.$challengeRulesData[0]->uom.'" != "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_duration'].' ELSE "500" END) AS points'),
                            "valTable.log_date",
                            "valTable.created_at",
                        )
                        ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                    }
                    
                       
                    if ($challenge->challenge_type == "inter_company" && !empty($queryStr['company'])) {
                        $firstRuleData->where("companies.id", $queryStr['company']);
                    }

                    if (!empty($queryStr['team'])) {
                        $firstRuleData->where("teams.id", $queryStr['team']);
                    }

                    if (!empty($startDate) && !empty($endDate)) {
                        $firstRuleData->whereBetween('valTable.log_date', [$startDate, $endDate]);
                    } elseif (!empty($startDate) && empty($endDate)) {
                        $firstRuleData->where('valTable.log_date', '>=', $startDate);
                    } elseif (empty($startDate) && !empty($endDate)) {
                        $firstRuleData->where('valTable.log_date', '<=', $endDate);
                    }

                    if (!empty($queryStr['userrecordsearch'])) {
                        $userrecordsearch = $queryStr['userrecordsearch'];
                        $firstRuleData->where(function ($query) use ($userrecordsearch) {
                            $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                        });
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        if ($challengeRulesData[1]->short_name != 'exercises') {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "companies.name as company",
                                "teams.name as team",
                                "valTable.tracker",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.userVal as counts",
                                DB::raw('(CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "steps" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "distance" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' WHEN "'.$challengeRulesData[1]->short_name.'" = "meditations" THEN valTable.userVal/'.$pointCalcRules[$challengeRulesData[1]->short_name].' ELSE "500" END) AS points'),
                                "valTable.log_date",
                                "valTable.created_at",
                            )
                            ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        } else {
                            $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "companies.name as company",
                                "teams.name as team",
                                "valTable.tracker",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.userVal as counts",
                                DB::raw('(CASE WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" = "meter" THEN valTable.userVal/'.$pointCalcRules['exercises_distance'].' WHEN "'.$challengeRulesData[1]->short_name.'" = "exercises" AND "'.$challengeRulesData[1]->uom.'" != "meter" valTable.userVal/'.$pointCalcRules['exercises_duration'].' ELSE "500" END) AS points'),
                                "valTable.log_date",
                                "valTable.created_at",
                            )
                            ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);
                        }
                        

                        if ($challenge->challenge_type == "inter_company" && !empty($queryStr['company'])) {
                            $secondRuleData->where("companies.id", $queryStr['company']);
                        }

                        if (!empty($queryStr['team'])) {
                            $secondRuleData->where("teams.id", $queryStr['team']);
                        }

                        if (!empty($startDate) && !empty($endDate)) {
                            $secondRuleData->whereBetween('valTable.log_date', [$startDate, $endDate]);
                        } elseif (!empty($startDate) && empty($endDate)) {
                            $secondRuleData->where('valTable.log_date', '>=', $startDate);
                        } elseif (empty($startDate) && !empty($endDate)) {
                            $secondRuleData->where('valTable.log_date', '<=', $endDate);
                        }

                        if (!empty($queryStr['userrecordsearch'])) {
                            $userrecordsearch = $queryStr['userrecordsearch'];
                            $secondRuleData->where(function ($query) use ($userrecordsearch) {
                                $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                    ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                            });
                        }
                    }
                   
                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }
                    $userNPSDataRecords = $record->get()->chunk(100);
                    if ($record->get()->count() == 0) {
                        $messageData = [
                            'data'   => trans('challenges.messages.export_error'),
                            'status' => 0,
                        ];
                        return \Redirect::route('admin.reports.challengeactivityreport')->with('message', $messageData);
                    }
                }
                $dateTimeString = Carbon::now()->toDateTimeString();

                $sheetTitle = [
                    'User Name',
                    'Email',
                    'Company Name',
                    'Team Name',
                    'Tracker Name',
                    'Target Type',
                    'Counts',
                    'Points',
                    'Sync Date',
                    'Log Date'
                ];
                $fileName = "Challenge_activity_details_".$this->user['full_name']."_".$dateTimeString.'.xlsx';
            }
            
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index, true);
                $index = $index + count($value);
            }

            $writer = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source = fopen($temp_file, 'rb');
            $writer->save($temp_file);
            
            $root     = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            event(new ChallengeActivityReportExportEvent($this->user, $queryStr['tab'], $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
