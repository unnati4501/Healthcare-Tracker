<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MigrateUsersToWaterWipesEmail extends Seeder
{
    /**
     * Run the database seeds to update irishbreeze.com emails to waterwipes.com on production.
     *
     * @return void
     */
    public function run()
    {
        try {
            $env = config('app.env', 'local');
            if ($env == "production") {
                DB::statement("UPDATE `users` SET `email` = replace(`email`, '@irishbreeze.com', '@waterwipes.com') WHERE `email` LIKE '%@irishbreeze.com';");
            } elseif ($env == "qa") {
                DB::statement("UPDATE `users` INNER JOIN `user_team` ON (`user_team`.`user_id` = `users`.`id`) SET `email` = replace(`email`, '@thespamfather.com', '@yopmail.com') WHERE `users`.`email` LIKE '%@thespamfather.com' AND `user_team`.`company_id` = 69;");
            }
        } catch (\Exception $exception) {
            report($exception);
            $this->command->error("Fatal Error: " . $exception->getMessage() . "\n");
        }
    }
}
