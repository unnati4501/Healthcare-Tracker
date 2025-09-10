<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\HsQuestionType;
use Illuminate\Database\Seeder;

/**
 * Class HsQuestionTypeTableSeeder
 */
class HsQuestionTypeTableSeeder extends Seeder
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
            $this->truncate('hs_question_type');

            $hsQuestionTypeData = [
                [
                    'name'         => 'single_choice',
                    'display_name' => 'Single choice',
                    'status'       => 1,
                ],
                [
                    'name'         => 'single_choice_slider',
                    'display_name' => 'Single choice slider',
                    'status'       => 1,
                ],
                [
                    'name'         => 'multiple_choices',
                    'display_name' => 'Multiple choices',
                    'status'       => 0,
                ],
                [
                    'name'         => 'yes_no',
                    'display_name' => 'Yes / No',
                    'status'       => 0,
                ],
            ];

            HsQuestionType::insert($hsQuestionTypeData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
