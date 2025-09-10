<?php
namespace Database\Seeders;

use App\Models\UserNpsLogs;
use Illuminate\Database\Seeder;

class OldNpsSurveyDataRemoveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            UserNpsLogs::truncate();
        } catch (Exception $e) {
            report($e);
            $this->command->error($e->getMessage());
        }
    }
}
