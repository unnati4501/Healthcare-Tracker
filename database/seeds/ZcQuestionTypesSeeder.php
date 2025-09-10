<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\ZcQuestionType;
use Illuminate\Database\Seeder;

class ZcQuestionTypesSeeder extends Seeder
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
            $this->disableForeignKeys();

            $this->truncate('zc_question_types');

            $questionTypeData = [
                [
                    'name'         => 'free-text',
                    'display_name' => 'Free Text',
                ],
                [
                    'name'         => 'choice',
                    'display_name' => 'Choice',
                ],
            ];

            ZcQuestionType::insert($questionTypeData);

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
