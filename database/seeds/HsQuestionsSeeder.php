<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\HsQuestions;
use App\Models\HsQuestionsOption;
use Illuminate\Database\Seeder;

/**
 * Class HsQuestionsSeeder
 */
class HsQuestionsSeeder extends Seeder
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
            $this->truncateMultiple(['hs_questions', 'hs_questions_options']);

            $questionList = \json_decode(
                \file_get_contents(
                    __DIR__ . '/data/questions.json'
                ),
                true
            );

            $questions       = [];
            $questionOptions = [];

            $questionId = 1;
            foreach ($questionList as $value) {
                $imageUrl    = asset('app_assets/question_images/' . $questionId . '.png');
                $questions[] = [
                    'category_id'      => $value['category_id'],
                    'sub_category_id'  => $value['sub_category_id'],
                    'question_type_id' => $value['question_type_id'],
                    'title'            => $value['title'],
                    'image'            => $imageUrl,
                    'max_score'        => $value['max_score'],
                ];
                foreach ($value['options'] as $val) {
                    $questionOptions[] = [
                        'question_id' => $questionId,
                        'score'       => $val['score'],
                        'choice'      => $val['choice'],
                    ];
                }
                $questionId++;
            }

            try {
                DB::beginTransaction();
                foreach ($questions as $question) {
                    HsQuestions::create($question);
                }
                foreach ($questionOptions as $questionOption) {
                    HsQuestionsOption::create($questionOption);
                }
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
