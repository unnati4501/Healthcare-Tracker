<?php
namespace Database\Seeders;

use App\Models\CourseSurveyQuestions;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class SetMCSurveyOptionsScore extends Seeder
{
    /**
     * Run the database seeds to set score of the survey questions
     *
     * @return void
     */
    public function run()
    {
        try {
            $courseSurveyQs = CourseSurveyQuestions::all();
            $courseSurveyQs->each(function ($question) {
                $question->courseSurveyOptions()->each(function ($option, $index) {
                    $option->score = ($index + 1);
                    $option->save();
                });
            });
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
