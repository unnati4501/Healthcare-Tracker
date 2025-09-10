<?php
namespace Database\Seeders;

use App\Models\ChallengeImageLibraryTargetType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChallengeImageLibraryTargetTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (Schema::hasTable('challenge_image_library_target_type')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('challenge_image_library_target_type')->truncate();

                $targetTypes            = config('zevolifesettings.challenge_image_library_target_type', []);
                $targetTypesInsertArray = [];

                if (!empty($targetTypes)) {
                    foreach ($targetTypes as $key => $targetType) {
                        $targetTypesInsertArray[] = [
                            'target'     => $targetType,
                            'slug'       => $key,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }

                if (!empty($targetTypesInsertArray)) {
                    ChallengeImageLibraryTargetType::insert($targetTypesInsertArray);
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
