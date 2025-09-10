<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\HsSubCategories;
use Illuminate\Database\Seeder;

/**
 * Class HsSubCategoryTableSeeder
 */
class HsSubCategoryTableSeeder extends Seeder
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

            // truncate table
            $this->truncate('hs_sub_categories');

            $hsSubCategoriesData = [
                [
                    'name'         => 'physical_activity',
                    'display_name' => 'Physical activity',
                    'category_id'  => 1,
                    'status'       => 1,
                ],
                [
                    'name'         => 'sleep',
                    'display_name' => 'Sleep',
                    'category_id'  => 1,
                    'status'       => 1,
                ],
                [
                    'name'         => 'nutrition',
                    'display_name' => 'Nutrition',
                    'category_id'  => 1,
                    'status'       => 1,
                ],
                [
                    'name'         => 'immunity',
                    'display_name' => 'Immunity',
                    'category_id'  => 1,
                    'status'       => 0,
                ],
                [
                    'name'         => 'positive_emotion',
                    'display_name' => 'Positive emotion',
                    'category_id'  => 2,
                    'status'       => 1,
                ],
                [
                    'name'         => 'engagement',
                    'display_name' => 'Engagement',
                    'category_id'  => 2,
                    'status'       => 1,
                ],
                [
                    'name'         => 'relationships',
                    'display_name' => 'Relationships',
                    'category_id'  => 2,
                    'status'       => 1,
                ],
                [
                    'name'         => 'meaning',
                    'display_name' => 'Meaning',
                    'category_id'  => 2,
                    'status'       => 1,
                ],
                [
                    'name'         => 'achievement',
                    'display_name' => 'Achievement',
                    'category_id'  => 2,
                    'status'       => 1,
                ],
            ];

            HsSubCategories::insert($hsSubCategoriesData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
