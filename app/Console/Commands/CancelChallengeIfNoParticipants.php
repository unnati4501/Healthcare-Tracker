<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;

class CancelChallengeIfNoParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:noparticipants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel challenge if there are less than 2 participants.';

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

        // cron used to check number of participants after challenge started
        // id no. of participant < 2 then cancel the challenge
        // if challenge is started and no. of participants are greater than 2 then update pending invitation to expired status
        try {
            $now = \now(config('app.timezone'));

            $this->challenge
                ->where('start_date', '<=', $now->toDateTimeString())
                ->where('cancelled', 0)
                ->where('finished', 0)
                ->get()
                ->each
                ->autoCancelChallengeNoParticipant();

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
