<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\MeditationTrack;
use Illuminate\Database\Seeder;

/**
 * Class GroupDataTransferSeeder
 */
class MeditationDataUpdateSeeder extends Seeder
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

            MeditationTrack::where('type', 1)
                    ->update(['audio_type' => 1]);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
