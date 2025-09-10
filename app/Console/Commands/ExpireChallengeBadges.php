<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Models\Challenge;
use DB;
use Illuminate\Console\Command;

class ExpireChallengeBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:expirebadges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire challenge badges after 24 hours of challenge finish date.';

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
            \DB::beginTransaction();

            $now = \now(config('app.timezone'))->toDateTimeString();

            $this->challenge->where('end_date', '<=', $now)->whereRaw("TIMESTAMPDIFF(DAY, end_date, ?) >= 1",[$now])->where('cancelled', false)->where('finished', true)->get()->each->expireBadges();

            \DB::commit();
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
