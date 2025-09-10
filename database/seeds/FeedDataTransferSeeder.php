<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class FeedDataTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::statement("UPDATE feeds inner join sub_categories on sub_categories.short_name = feeds.tag set feeds.sub_category_id = sub_categories.id where sub_categories.short_name in ('move', 'nourish', 'inspire') and sub_categories.category_id = 2;");
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
