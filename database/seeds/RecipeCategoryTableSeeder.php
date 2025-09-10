<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\RecipeCategories;
use Illuminate\Database\Seeder;

class RecipeCategoryTableSeeder extends Seeder
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
            $this->truncate('recipe_categories');

            $recipeCategoriesData = [
                [
                    'name'         => 'breakfast',
                    'display_name' => 'Breakfast',
                ],
                [
                    'name'         => 'snack',
                    'display_name' => 'Snack',
                ],
                [
                    'name'         => 'lunch',
                    'display_name' => 'Lunch',
                ],
                [
                    'name'         => 'dinner',
                    'display_name' => 'Dinner',
                ],
            ];

            RecipeCategories::insert($recipeCategoriesData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
