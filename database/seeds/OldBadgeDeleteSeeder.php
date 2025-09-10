<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class OldBadgeDeleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            Badge::where("type", "!=", "course")
                    ->where("is_default", 0)
                    ->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
