<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Group;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

/**
 * Class GroupDataTransferSeeder
 */
class GroupDataTransferSeeder extends Seeder
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

            $env = config('app.env');

            if ($env == 'production') {
                $oldCategories = [
                    'meditation' => 1,
                    'move'       => 6,
                    'nourish'    => 7,
                    'inspire'    => 8,
                    'recipe'     => 9,
                ];
            }

            if ($env == 'uat') {
                $oldCategories = [
                    'meditation' => 1,
                    'move'       => 3,
                    'nourish'    => 4,
                    'inspire'    => 5,
                    'recipe'     => 6,
                ];
            }

            if ($env == 'qa' || $env == 'local') {
                $oldCategories = [
                    'meditation' => 1,
                    'recipe'     => 9,
                ];
            }

            $subcategories = SubCategory::where(['category_id' => 3])
                ->whereIn('short_name', array_keys($oldCategories))
                ->get()
                ->pluck('id', 'short_name');

            if (!empty($subcategories)) {
                foreach ($subcategories as $tag => $subcategory) {
                    Group::where('sub_category_id', $oldCategories[$tag])
                        ->update(['sub_category_id' => $subcategory]);
                    // DB::statement("UPDATE `groups` SET `sub_category_id` = {$subcategory} WHERE `groups`.`category_id` = '{$oldCategories[$tag]}';");
                }
            }

            if ($env == 'qa' || $env == 'local') {
                Group::whereNotIn('sub_category_id', [7, 8, 9, 10, 11, 12])
                    ->update(['sub_category_id' => 8]);
            }

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
