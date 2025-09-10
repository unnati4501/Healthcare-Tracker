<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class AddDefaultCategoryForTags extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $categoriesHasTags = [
                'course',
                'feed',
                'meditation',
                'recipe',
                'webinar',
                'podcast',
                'shorts'
            ];

            // update `has_tags` field to 1 for $categoriesHasTags
            Category::whereIn('short_name', $categoriesHasTags)->update([
                'has_tags' => 1,
            ]);
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
