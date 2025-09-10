<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DisableRecipesNotificaionToggleForAllUsers extends Seeder
{
    /**
     * Run the database seed to disable recipes notificaion toggle for all users.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement("UPDATE `user_notification_settings` SET `flag` = '0' WHERE 1;");
    }
}
