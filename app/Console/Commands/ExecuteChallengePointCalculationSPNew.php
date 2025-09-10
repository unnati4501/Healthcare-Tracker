<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\SpChallengePointCalculationJob;
use App\Models\Challenge;
use App\Models\Company;
use Illuminate\Console\Command;
use Log;

class ExecuteChallengePointCalculationSPNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'execute:challengeSPNew';

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

            $challenges = Challenge::select('challenges.id', 'challenges.challenge_type', 'challenges.company_id', 'challenges.job_finished')
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

                \dispatch(new SpChallengePointCalculationJob($procedureData, $challenge->challenge_type))->onQueue('default');
                
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
