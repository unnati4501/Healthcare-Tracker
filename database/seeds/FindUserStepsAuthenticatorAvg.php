<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\UsersStepsAuthenticatorAvg;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Class Find User Authenticator Averge
 */
class FindUserStepsAuthenticatorAvg extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            // Disable foreign key checks!
            $this->disableForeignKeys();

            UsersStepsAuthenticatorAvg::truncate();

            $stepAuthenticatorDate               = config('zevolifesettings.stepAuthenticatorDate');
            $stepAuthenticatorDayCount           = config('zevolifesettings.stepAuthenticatorPrevDay');
            $previousStepAuthenticatorDate       = Carbon::parse($stepAuthenticatorDate);
            $previousStepAuthenticatorDateSecond = Carbon::now()->subDays($stepAuthenticatorDayCount);

            if ($previousStepAuthenticatorDateSecond->toDateString() <= $previousStepAuthenticatorDate->toDateString()) {
                $previousStepAuthenticatorDateSecond = $previousStepAuthenticatorDate;
            }

            $todayDate = Carbon::now()->toDateString();
            $diff      = $previousStepAuthenticatorDateSecond->diffInDays($todayDate);

            for ($i = 1; $i <= $diff; $i++) {
                $date          = Carbon::now()->subDays($i)->toDateString();
                $logDate       = $date . ' 00:00:00';
                $procedureData = array();
                $procedureData = [
                    $logDate,
                ];

                DB::select('CALL sp_calculate_user_step_avg(?)', $procedureData);
            }

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
