<?php

namespace App\Jobs;

use App\Models\User;
use App\Jobs\AwardDailyBadgeToUser;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Jobs\AwardOngoingChallengeBadgeToUser;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStepsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var $data
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $data)
    {
        $this->queue   = 'default';
        $this->user    = $user;
        $this->data    = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appTimezone = config('app.timezone');

        foreach ($this->data as $item) {

            if (0 > (int) $item['steps'] && 0 > (int) $item['distance'] && 0 > (int) $item['calories']) {
                continue;
            }

            if (isset($item['steps']) && isset($item['tracker']) && $item['tracker'] == 'zevodefault' && $item['steps'] > 35000) {
                continue;
            }

            $date = Carbon::parse($item['date'], $this->user->timezone)->setTimezone($appTimezone);

            $stepDateInUserTimeZone = Carbon::parse($item['date'], $this->user->timezone);

            if ($item['tracker'] == 'polar') {

                $this->user->steps()
                    ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                        $appTimezone,$this->user->timezone,$stepDateInUserTimeZone->toDateString()
                    ])
                    ->get()->each->delete();

                // add steps into user account
                $this->user->steps()->create([
                    'log_date' => $date->toDateTimeString(),
                    'tracker'  => $item['tracker'],
                    'steps'    => (int) $item['steps'],
                    'distance' => (int) $item['distance'],
                    'calories' => (int) $item['calories'],
                ]);
            } else {
                // Check old records with same date
                $oldRecords = $this->user->steps()
                    ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                        $appTimezone,$this->user->timezone,$stepDateInUserTimeZone->toDateString()
                    ])
                    ->where('tracker', $item['tracker'])
                    ->orderBy('id', 'DESC')
                    ->first();
                
                if (empty($oldRecords)) {
                    // remove all records for the tracker for steps date pair
                    // delete user steps
                    $this->user->steps()
                        ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                            $appTimezone,$this->user->timezone,$stepDateInUserTimeZone->toDateString()
                        ])
                        ->get()->each->delete();

                    // add steps into user account
                    $this->user->steps()->create([
                        'log_date' => $date->toDateTimeString(),
                        'tracker'  => $item['tracker'],
                        'steps'    => (int) $item['steps'],
                        'distance' => (int) $item['distance'],
                        'calories' => (int) $item['calories'],
                    ]);
                } else {
                    if ($item['steps'] > 0 && $item['steps'] > $oldRecords->steps) {
                        $this->user->steps()->where('id', $oldRecords->id)->update([
                            'steps'    => (int) $item['steps']
                        ]);
                    }

                    if ($item['distance'] > 0 && $item['distance'] > $oldRecords->distance) {
                        $this->user->steps()->where('id', $oldRecords->id)->update([
                            'distance' => (int) $item['distance']
                        ]);
                    }

                    $this->user->steps()->where('id', $oldRecords->id)->update([
                        'calories' => (int) $item['calories'],
                        'log_date' => $date->toDateTimeString(),
                    ]);
                }
            }

            if (!empty($item['steps'])) {
                // dispatch job to award Daily badge to user for steps
                dispatch(new AwardDailyBadgeToUser($this->user, $date->toDateTimeString()));

                // dispatch job to award general badge to user for steps
                dispatch(new AwardGeneralBadgeToUser($this->user, 'steps', $date->toDateTimeString()));

                // Dispatch job to award ongoing badge to user for steps
                dispatch(new AwardOngoingChallengeBadgeToUser($this->user, 'steps', $date->toDateTimeString()));
            }

            if (!empty($item['distance'])) {
                // dispatch job to award general badge to user for distance
                dispatch(new AwardGeneralBadgeToUser($this->user, 'distance', $date->toDateTimeString()));

                // Dispatch job to award ongoing badge to user for distance
                dispatch(new AwardOngoingChallengeBadgeToUser($this->user, 'distance', $date->toDateTimeString()));
            }
        }
    }
}
