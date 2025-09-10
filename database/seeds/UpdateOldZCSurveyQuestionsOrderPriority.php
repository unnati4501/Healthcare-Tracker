<?php
namespace Database\Seeders;

use App\Models\ZcSurveyQuestion;
use Illuminate\Database\Seeder;

class UpdateOldZCSurveyQuestionsOrderPriority extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $surveys = ZcSurveyQuestion::select('survey_id')->groupBy('survey_id')->get();
        if ($surveys->count() > 0) {
            $surveys->each(function ($survey) {
                DB::select("UPDATE zc_survey_questions JOIN (SELECT @order_priority := 0) AS r SET order_priority = @order_priority := (@order_priority + 1) WHERE survey_id = ?;", [$survey->survey_id]);
            });
        }
    }
}
