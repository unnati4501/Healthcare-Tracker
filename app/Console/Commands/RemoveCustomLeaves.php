<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemoveCustomLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:removecustomleaves';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the expired custom leaves and update availability status to available';

    /**
     * Feed model object
     *
     * @var User $user
     */
    protected $user;
    
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
            DB::beginTransaction();
            $users = $this->user->select('users.id', 'users.first_name')->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')->where('roles.slug','wellbeing_specialist')->where('users.availability_status', 2)->get();
            $now   = now(config('app.timezone'));
            foreach ($users as $user) {
                 // Get the past dates records and delete it
                 $availability = $user->healthCocahAvailability()
                    ->where('update_from', 'profile')
                    ->where('user_id', $user->id)
                    ->whereRaw("TIMESTAMP(from_date) <= ?", [$now])
                    ->whereRaw("TIMESTAMP(to_date) <= ?", [$now]);
                 if ($availability->count() > 0) {
                    $availability->delete();
                 }

                 // Update the availibility status to available
                $healthCoachavailability = $user->healthCocahAvailability()
                    ->where('update_from', 'profile')
                    ->where('user_id', $user->id)->count();
                if ($healthCoachavailability == 0) {
                    $user->where('id', $user->id)->update(['availability_status' => 1]);
                }
            }
            cronlog($cronData, 1); 
            DB::commit();   
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $e->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $e->getMessage();
            cronlog($cronData, 1);
        }
    }
}
