<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Mood;
use Illuminate\Database\Seeder;

/**
 * Class MoodsTableSeeder
 */
class MoodsTableSeeder extends Seeder
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

            // truncate tables
            $this->truncateMultiple(['moods']);

            $moods = [
                'Ecstatic',
                'Happy',
                'Excited',
                'Grateful',
                'Relaxed',
                'Content',
                'Unsure',
                'Bored',
                'Anxious',
                'Angry',
                'Stressed',
                'Sad',
            ];

            $moodData = [];
            foreach ($moods as $value) {
                $imageUrl   = asset('app_assets/mood_images/' . lcfirst($value) . '.png');
                $moodData[] = [
                    'title' => $value,
                    'image' => $imageUrl,
                ];
            }

            try {
                DB::beginTransaction();
                Mood::insert($moodData);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                echo $exception->getMessage();
            }

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
