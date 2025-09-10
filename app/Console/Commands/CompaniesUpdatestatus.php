<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class CompaniesUpdatestatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:updatestatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily update expire / expired soon / inactive company status';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $nowInUTC         = now(config('app.timezone'))->toDateTimeString();
        $featureDaysInUTC = now(config('app.timezone'))->addDay(10)->toDateTimeString();
        $now              = now($timezone);

        $companyRecords = Company::select('id', 'subscription_start_date', 'subscription_end_date', 'plan_status')->where('subscription_end_date', '>=', $nowInUTC)
            ->where('subscription_end_date', '<', $featureDaysInUTC)->get();

        foreach ($companyRecords as $companyValue) {
            $diff         = $now->diffInHours($companyValue->subscription_end_date, false);
            $startDayDiff = $now->diffInHours($companyValue->subscription_start_date, false);
            $days         = (int) ceil($diff / 24);

            if ($startDayDiff > 0) {
                $planStatus = 'inactive';
            } elseif ($days < 10) {
                $planStatus = 'expired_soon';
            } elseif ($days <= 0) {
                $planStatus = 'expired';
            } else {
                $planStatus = 'expired';
            }

            Company::where('id', $companyValue->id)->update(['plan_status' => $planStatus]);
        }
    }
}
