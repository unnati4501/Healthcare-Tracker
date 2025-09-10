<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\AppSlide;
use Illuminate\Database\Seeder;

/**
 * Class SetOnboardingScreenPrioritySeeder
 */
class SetOnboardingScreenPrioritySeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Disable foreign key checks!
            $this->disableForeignKeys();

            $subcategories = AppSlide::orderBy("id", "ASC")->get();

            if (!empty($subcategories)) {
                $i = 1;
                foreach ($subcategories as $value) {
                    $value->order_priority = $i++;
                    $value->save();
                }
            }

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
