<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class SetDefaultSessionDurationForServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            Service::where('name', "Counselling")
            ->where('default', 1)
            ->update([
                'session_duration' => 50,
                'is_counselling'   => 1,
            ]);
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }

    }
}
