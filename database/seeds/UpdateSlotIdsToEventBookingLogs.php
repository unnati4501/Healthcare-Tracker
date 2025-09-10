<?php
namespace Database\Seeders;

use App\Models\EventBookingLogs;
use App\Models\HealthCoachSlots;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateSlotIdsToEventBookingLogs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $eventBookingLogs = EventBookingLogs::get();
            $appTimezone      = config('app.timezone');
            $eventBookingLogs->each(function ($eventBookingLogsChunk) use ($appTimezone) {
                if (!empty($eventBookingLogsChunk->presenter_user_id) && $eventBookingLogsChunk->presenter_user_id != null) {
                    $userData     = User::find($eventBookingLogsChunk->presenter_user_id);
                    $userTimezone = !empty($userData->timezone) ? $userData->timezone : $appTimezone;
                    $dayname      = Carbon::parse($eventBookingLogsChunk->booking_date . ' ' . $eventBookingLogsChunk->start_time, $appTimezone)->setTimezone($userTimezone)->format('D');

                    $slots = HealthCoachSlots::select('id')
                        ->where('user_id', $eventBookingLogsChunk->presenter_user_id)
                        ->where('day', strtolower($dayname))
                        ->get()->first();
                    if (!empty($slots)) {
                        $eventBookingLogsChunk->update(['slot_id' => $slots->id]);
                    }
                }
            });
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
