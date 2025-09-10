<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\TrackerExercise;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class TrackerExercisesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('tracker_exercises')->truncate();

        $exercises = \json_decode(
            \file_get_contents(
                __DIR__ . '/data/tracker_exercises.json'
            ),
            true
        );

        foreach ($exercises as $exercise) {
            TrackerExercise::create([
                'tracker'       => $exercise['tracker_name'],
                'tracker_title' => ($exercise['tracker_name'] == 'shealth') ? 'Samsung Health' : ucfirst($exercise['tracker_name']),
                'key'           => $exercise['exercise_code'] ?? null,
                'name'          => $exercise['exercise_name'],
            ]);
        }

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
