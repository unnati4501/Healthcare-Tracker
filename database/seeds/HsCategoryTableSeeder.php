<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\HsCategories;
use Illuminate\Database\Seeder;

/**
 * Class HsCategoryTableSeeder
 */
class HsCategoryTableSeeder extends Seeder
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
            $this->truncate('hs_categories');

            $hsCategoriesData = [
                [
                    'name'         => 'physical',
                    'display_name' => 'Physical',
                ],
                [
                    'name'         => 'psychological',
                    'display_name' => 'Psychological',
                ],
            ];

            HsCategories::insert($hsCategoriesData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
