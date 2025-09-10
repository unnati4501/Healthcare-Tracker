<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class DisableSurveyForNonBindedCompany extends Seeder
{
    /**
     * Run the database seeds to disable survey for those company which survey is enabled but not binded/assigned any survey
     *
     * @return void
     */
    public function run(Company $company)
    {
        try {
            $company
                ->select('companies.id')
                ->withCount('survey')
                ->where('enable_survey', true)
                ->having('survey_count', '<=', 0)
                ->get()
                ->each(function ($comp) {
                    $comp->enable_survey = false;
                    $comp->save();
                });
        } catch (\Exception $exception) {
            dd($exception);
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
