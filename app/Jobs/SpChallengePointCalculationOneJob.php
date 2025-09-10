<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use DB;

class SpChallengePointCalculationOneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $procedureData;

    public $challengeType;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($procedureData, $challengeType)
    {
        $this->procedureData = $procedureData;
        $this->challengeType = $challengeType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->challengeType == 'company_goal') {
                DB::select('CALL sp_company_challenge_pointcalculation1(?, ?, ?, ?, ?, ?, ?)', $this->procedureData);
            } elseif ($this->challengeType == 'inter_company') {
                DB::select('CALL sp_inter_comp_challenge_pointcalculation1(?, ?, ?, ?, ?, ?, ?)', $this->procedureData);
            } elseif ($this->challengeType == 'team') {
                DB::select('CALL sp_team_challenge_pointcalculation1(?, ?, ?, ?, ?, ?, ?)', $this->procedureData);
            } elseif ($this->challengeType == 'individual') {
                DB::select('CALL sp_individual_challenge_pointcalculation1(?, ?, ?, ?, ?, ?, ?)', $this->procedureData);
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
