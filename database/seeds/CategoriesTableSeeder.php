<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Class CategoriesTableSeeder
 */
class CategoriesTableSeeder extends Seeder
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
            $this->truncate('categories');

            $categoryData = [
                [
                    'name'              => 'Masterclass',
                    'short_name'        => 'course',
                    'description'       => 'course',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Feed',
                    'short_name'        => 'feed',
                    'description'       => 'feed',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Group',
                    'short_name'        => 'group',
                    'description'       => 'group',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Meditation',
                    'short_name'        => 'meditation',
                    'description'       => 'meditation',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Recipe',
                    'short_name'        => 'recipe',
                    'description'       => 'recipe',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Expertise',
                    'short_name'        => 'expertise',
                    'description'       => 'expertise',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Webinar',
                    'short_name'        => 'webinar',
                    'description'       => 'webinar',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Counsellor Skills',
                    'short_name'        => 'skills',
                    'description'       => 'skills',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Podcast',
                    'short_name'        => 'podcast',
                    'description'       => 'podcast',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Shorts',
                    'short_name'        => 'shorts',
                    'description'       => 'shorts',
                    'in_activity_level' => 1,
                    'default'           => 1,
                    'is_excluded'       => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
            ];

             //Category::insert($categoryData);

             foreach ($categoryData as $value) {
                Category::updateOrCreate(
                    ['short_name' => $value['short_name']],
                    $value
                );
            }


            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
