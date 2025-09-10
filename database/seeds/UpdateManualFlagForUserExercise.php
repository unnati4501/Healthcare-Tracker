<?php
namespace Database\Seeders;

use App\Models\UserExercise;
use Illuminate\Database\Seeder;
use \Illuminate\Database\QueryException;

class UpdateManualFlagForUserExercise extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            UserExercise::where('exercise_key', 'LIKE', 'ZevoLife_%')->update([
                'is_manual' => 1,
            ]);
        } catch (QueryException $e) {
            dd($e->getMessage());
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
