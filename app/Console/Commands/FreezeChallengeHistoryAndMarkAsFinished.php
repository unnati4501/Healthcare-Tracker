<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class FreezeChallengeHistoryAndMarkAsFinished extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:freezeandfinish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'freeze challenge data before marking as completed.';

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
            'cron_name' => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        // cron used to dump all data of challenge in hisoty tables and make it as finished and send user notifications of challenge finish and challenge winner
        try {
            $now = \now(config('app.timezone'));

            $this->challenge
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->select('challenges.*', 'challenge_categories.short_name')
                ->where('challenges.end_date', '<', $now->toDateTimeString())
                ->where('challenges.finished', false)
                ->where('challenges.cancelled', false)
                ->get()
                ->each
                ->freezeHistoryAndFinishChallenge();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
