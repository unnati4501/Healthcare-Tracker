<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Course;
use App\Models\Badge;
use App\Models\GroupMessage;
use Illuminate\Database\Seeder;

/**
 * Class CourseDataRemoveSeeder
 */
class CourseDataRemoveSeeder extends Seeder
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
            $env = config('app.env');

            if ($env == 'production') {
                $courseData = Course::get()->pluck("id")->toArray();

                Badge::where("model_name", "course")
                    ->delete();

                GroupMessage::where("model_name", "course")
                    ->delete();
            }

            if ($env == 'uat') {
                $courseData = Course::where('id', "<=", 3)->get()->pluck("id")->toArray();

                Badge::where("model_name", "course")
                    ->where("model_id", "<=", 3)
                    ->delete();

                GroupMessage::where("model_name", "course")
                    ->where("model_id", "<=", 3)
                    ->delete();
            }

            if ($env == 'qa' || $env == 'local') {
                $courseData = Course::where('id', "<=", 93)->get()->pluck("id")->toArray();

                Badge::where("model_name", "course")
                    ->where("model_id", "<=", 93)
                    ->delete();

                GroupMessage::where("model_name", "course")
                    ->where("model_id", "<=", 93)
                    ->delete();
            }

            Course::destroy($courseData);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
