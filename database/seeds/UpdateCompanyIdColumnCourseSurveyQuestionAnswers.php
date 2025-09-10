<?php
namespace Database\Seeders;

use App\Models\CourseSurveyQuestionAnswers;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class UpdateCompanyIdColumnCourseSurveyQuestionAnswers extends Seeder
{
    /**
     * Run the database seeds for company id of course survey question answers.
     *
     * @return void
     */
    public function run()
    {
        try {
            $answers = CourseSurveyQuestionAnswers::select('id', 'user_id')->groupBy('user_id')->get();
            $answers->each(function ($answer) {
                $companyId = $answer->user->company()->first()->id;
                CourseSurveyQuestionAnswers::where('user_id', $answer->user_id)->update([
                    'company_id' => $companyId,
                ]);
            });
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
