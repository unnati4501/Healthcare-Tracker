<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Badge;

class ChangeBadgeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badge:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status to expired if badge is expired.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Badge $badge)
    {
        parent::__construct();
        $this->badge = $badge;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name' => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            \DB::beginTransaction();

            $this->badge->join('badge_user', 'badges.id', '=', 'badge_user.badge_id')
                ->join('users', 'users.id', '=', 'badge_user.user_id')
                ->select('badge_user.id as pivotId', 'users.id as recepient_id', 'users.timezone', 'badge_user.status', 'badge_user.badge_id', 'badge_user.created_at', 'badges.expires_after')
                ->where('badge_user.status', 'Active')
                ->where('badges.can_expire', true)
                ->whereNotNull('badges.expires_after')
                ->where('badges.type', 'general')
                ->get()->each->expireBadgeForUser();

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
