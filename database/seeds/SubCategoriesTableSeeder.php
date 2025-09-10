<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Class SubCategoriesTableSeeder
 */
class SubCategoriesTableSeeder extends Seeder
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

            $now = Carbon::now();

            // $subCategoryData = [
            //     [
            //         'category_id' => 1,
            //         'name'        => 'Move',
            //         'short_name'  => strtolower('Move'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 1,
            //         'name'        => 'Nourish',
            //         'short_name'  => strtolower('Nourish'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 1,
            //         'name'        => 'Inspire',
            //         'short_name'  => strtolower('Inspire'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 2,
            //         'name'        => 'Move',
            //         'short_name'  => strtolower('Move'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 2,
            //         'name'        => 'Nourish',
            //         'short_name'  => strtolower('Nourish'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 2,
            //         'name'        => 'Inspire',
            //         'short_name'  => strtolower('Inspire'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Other',
            //         'short_name'  => strtolower('Other'),
            //         'status'      => 0,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Move',
            //         'short_name'  => strtolower('Move'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Nourish',
            //         'short_name'  => strtolower('Nourish'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Inspire',
            //         'short_name'  => strtolower('Inspire'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Meditation',
            //         'short_name'  => strtolower('Meditation'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Recipe',
            //         'short_name'  => strtolower('Recipe'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 4,
            //         'name'        => 'Move',
            //         'short_name'  => strtolower('Move'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 4,
            //         'name'        => 'Nourish',
            //         'short_name'  => strtolower('Nourish'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 4,
            //         'name'        => 'Inspire',
            //         'short_name'  => strtolower('Inspire'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 5,
            //         'name'        => 'Breakfast',
            //         'short_name'  => strtolower('Breakfast'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 5,
            //         'name'        => 'Snack',
            //         'short_name'  => strtolower('Snack'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 5,
            //         'name'        => 'Lunch',
            //         'short_name'  => strtolower('Lunch'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 5,
            //         'name'        => 'Dinner',
            //         'short_name'  => strtolower('Dinner'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            // ];

            // $subCategoryData = [
            //     [
            //         'category_id' => 3,
            //         'name'        => 'Public',
            //         'short_name'  => strtolower('Public'),
            //         'status'      => 0,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ]
            // ];

            // $subCategoryData = [
            //     [
            //         'category_id' => 9,
            //         'name'        => 'Self Care',
            //         'short_name'  => strtolower('Selfcare'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 9,
            //         'name'        => 'Wellbeing',
            //         'short_name'  => strtolower('Welbeing'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ],
            //     [
            //         'category_id' => 9,
            //         'name'        => 'Resilience',
            //         'short_name'  => strtolower('Resilience'),
            //         'status'      => 1,
            //         'is_excluded' => 0,
            //         'default'     => 1,
            //         'created_at'  => $now,
            //         'updated_at'  => $now,
            //     ]
            // ];

            $subCategoryData = [
                [
                    'category_id' => 10,
                    'name'        => 'Shorts',
                    'short_name'  => strtolower('Shorts'),
                    'status'      => 1,
                    'is_excluded' => 0,
                    'default'     => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            ];

            SubCategory::insert($subCategoryData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
