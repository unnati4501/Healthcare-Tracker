<?php

namespace App\Console\Commands;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChangeCompanyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:inactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status to inactive if company subscription end date is gone.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        parent::__construct();
        $this->company = $company;
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
            $status = "status";
            $this->company
                ->where('subscription_start_date', '<=', Carbon::now())
                ->where('subscription_end_date', '>=', Carbon::now())
                ->where($status, 0)
                ->update([$status => 1]);

            $this->company
                ->where('subscription_end_date', '<=', Carbon::now())
                ->where($status, 1)
                ->update([$status => 0]);

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
