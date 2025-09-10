<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class CheckTargetForFirstToXChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:firsttox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check target for first to X type of challenge and make users winner if applicable.';

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

        // cron used to dump all data of challenge in hisoty tables and make it as finished and send user notifications of challenge finish and challenge winner
        try {
            $now = \now(config('app.timezone'));

            $this->challenge
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->select('challenges.*', 'challenge_categories.short_name')
                ->where('challenges.start_date', '<=', $now->toDateTimeString())
                ->where('challenges.finished', false)
                ->where('challenges.cancelled', false)
                ->where('challenge_categories.short_name', 'first_to_reach')
                ->get()
                ->each
                ->firstToReachTargetAndFinish();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::info($exception->getMessage());
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
