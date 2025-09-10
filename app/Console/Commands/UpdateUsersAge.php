<?php

namespace App\Console\Commands;

use App\Jobs\UpdateUsersAgeJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateUsersAge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:updateage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update age of each users based on their date of birth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];
        cronlog($cronData);

        try {
            $userChunks = $this->user->get()->chunk(500);
            foreach ($userChunks as $chunk) {
                dispatch(new UpdateUsersAgeJob($chunk));
            }
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
