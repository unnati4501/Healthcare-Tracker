<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class ChallengeAutoCreationRecurringLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:recurringchallengeautocreation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create logic for recurring challenge for open challenge';

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
     * @return mixed
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        // cron used to create recurring challenge which is opened
        try {
            $this->challenge->whereNull("parent_id")
                ->where("close", false)
                ->where("recurring", true)
                ->where("recurring_completed", false)
                ->where('cancelled', 0)
                ->get()
                ->each
                ->autoCreateRecurringChallenge();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
