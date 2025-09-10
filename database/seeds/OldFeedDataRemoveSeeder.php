<?php
namespace Database\Seeders;

use App\Models\Feed;
use Illuminate\Database\Seeder;

class OldFeedDataRemoveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $feeds = Feed::get()->pluck("id")->toArray();
            Feed::destroy($feeds);
        } catch (Exception $e) {
            report($e);
            $this->command->error($e->getMessage());
        }
    }
}
