<?php
namespace Database\Seeders;

use App\Models\MeditationTrack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MeditationDataTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('meditation_tracks')) {
            DB::statement('UPDATE `meditation_tracks`
            INNER JOIN `sub_categories`
            ON `sub_categories`.`short_name`= `meditation_tracks`.`tag`
            SET  `meditation_tracks`.`sub_category_id` = `sub_categories`.`id`, `meditation_tracks`.`deep_link_uri` = CONCAT("zevolife://zevo/meditation-track/",meditation_tracks.id,"/",sub_categories.id)
            WHERE `sub_categories`.`short_name` IN ("move" , "nourish", "inspire")
            AND `sub_categories`.`category_id` = 4;');
        }
    }
}
