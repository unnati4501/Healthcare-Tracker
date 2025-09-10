<?php
namespace Database\Seeders;

use App\Models\ZcSurvey;
use Illuminate\Database\Seeder;

class RemoveAllAuditSurveyData extends Seeder
{

    /**
     * Run the database seed to remove all the audit related survey details from the database.
     *
     * @return void
     */
    public function run()
    {
        // get all the audit related survey and delete them one by one
        ZcSurvey::all()->each(function ($survey) {
            $survey->delete();
        });
    }
}
