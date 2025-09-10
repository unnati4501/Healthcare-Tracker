<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\IntercompanySpChallengePointCalculationJob;
use App\Jobs\IntercompanySpChallengePointCalculationOneJob;
use App\Jobs\IntercompanySpChallengePointCalculationTwoJob;
use App\Models\Challenge;
use App\Models\Company;
use DB;
use Illuminate\Console\Command;

class ExecuteChallengePointCalculationSP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'execute:challengeSP';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate challenge data while challenge is ongoing.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge)
    {
        parent::__construct();
        $this->challenge = $challenge;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            $now = \now(config('app.timezone'));

            $challenges = Challenge::select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.start_date', '<=', $now)
                ->where('challenges.end_date', '>=', $now)
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->get();

            foreach ($challenges as $challenge) {
                $company        = Company::find($challenge->company_id);
                $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');

                $procedureData = array();
                $procedureData = [
                    config('app.timezone'),
                    $challenge->id,
                    $pointCalcRules['steps'],
                    $pointCalcRules['distance'],
                    $pointCalcRules['exercises_distance'],
                    $pointCalcRules['exercises_duration'],
                    $pointCalcRules['meditations'],
                ];

                if ($challenge->challenge_type == 'individual') {
                    DB::select('CALL sp_individual_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                } elseif ($challenge->challenge_type == 'team') {
                    DB::select('CALL sp_team_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                } elseif ($challenge->challenge_type == 'company_goal') {
                    DB::select('CALL sp_company_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                } elseif ($challenge->challenge_type == 'inter_company') {
                    DB::select('CALL sp_inter_comp_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                }
            }

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
