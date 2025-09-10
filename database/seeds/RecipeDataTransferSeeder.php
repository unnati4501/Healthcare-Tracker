<?php
namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecipeDataTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('recipe_category')) {
            $oldCategories = [
                'breakfast' => 1,
                'snack'     => 2,
                'lunch'     => 3,
                'dinner'    => 4,
            ];
            $subcategories = SubCategory::where(['category_id' => 5])->whereIn('short_name', array_keys($oldCategories))->get()->pluck('id', 'short_name');
            if (!empty($subcategories)) {
                foreach ($subcategories as $tag => $subcategory) {
                    DB::statement("UPDATE `recipe_category` SET `sub_category_id` = {$subcategory} WHERE `recipe_category`.`sub_category_id` = '{$oldCategories[$tag]}';");
                }
            }
        }
    }
}
