<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConvertExistingHCSlotsToTheirTimestamp extends Seeder
{
    /**
     * This will convert all existing slots timezone to hc's timezone from UTC
     *
     * @return void
     */
    public function run()
    {
        try {
            $appTimezone = config('app.timezone');
            $statement   = "
                UPDATE `health_coach_slots`
                INNER JOIN `users` ON (`users`.`id` = `health_coach_slots`.`user_id`)
                SET
                    `health_coach_slots`.`start_time` = CONVERT_TZ(CONCAT('2021-02-17', ' ', `health_coach_slots`.`start_time`), '{$appTimezone}', `users`.`timezone`),
                    `health_coach_slots`.`end_time` = CONVERT_TZ(CONCAT('2021-02-17', ' ', `health_coach_slots`.`end_time`), '{$appTimezone}', `users`.`timezone`)
            ";
            DB::statement(trim($statement));
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
