<?php
namespace Database\Seeders;

use App\Models\ZcSurveyResponse;
use Illuminate\Database\Seeder;

class UpdateMaxScoreToZcSurveyResponses extends Seeder
{
    /**
     * Run the database seeds to add Max score of the question's option for each answers.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();

            // get all the responses
            $zcSurveyResponse = ZcSurveyResponse::select(
                'zc_survey_responses.id',
                'zc_survey_responses.question_id',
                'zc_survey_responses.max_score',
                'zc_questions.question_type_id'
            )
                ->join('zc_questions', 'zc_questions.id', '=', 'zc_survey_responses.question_id')
                ->orderby('id')
                ->groupBy('zc_survey_responses.question_id')
                ->get();

            // find max score of the question's option and update max score
            $zcSurveyResponse->each(function ($ansewr) {
                if ($ansewr->question_type_id == 1) {
                    ZcSurveyResponse::where('question_id', $ansewr->question_id)->update([
                        'score'     => null,
                        'max_score' => null,
                    ]);
                } elseif ($ansewr->question_type_id == 2) {
                    $maxScore = $ansewr->question->questionoptions->max('score');
                    ZcSurveyResponse::where('question_id', $ansewr->question_id)->update([
                        'answer_value' => null,
                        'max_score'    => $maxScore,
                    ]);
                }
            });

            // update option id for all the choice type answre as per the score
            ZcSurveyResponse::select(
                'zc_survey_responses.id',
                'zc_survey_responses.question_id',
                'zc_survey_responses.score'
            )
                ->join('zc_questions', 'zc_questions.id', '=', 'zc_survey_responses.question_id')
                ->where('zc_questions.question_type_id', 2)
                ->orderby('id')
                ->each(function ($answer) {
                    $option = $answer->question->questionoptions()
                        ->select('zc_questions_options.id')
                        ->where('score', $answer->score)
                        ->where('choice', '!=', 'meta')
                        ->first();
                    if (is_null($option)) {
                        $option = $answer->question->questionoptions()
                            ->select('zc_questions_options.id')
                            ->where('choice', '!=', 'meta')
                            ->first();

                        $answer->option_id = $option->id;
                        $answer->save();
                    } else {
                        $answer->option_id = $option->id;
                        $answer->save();
                    }
                });
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollback();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
