<?php
namespace Database\Seeders;

use App\Models\ConsentForm;
use App\Models\ConsentFormQuestions;
use Illuminate\Database\Seeder;

class AddOnlineConsentFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            $data = ConsentForm::create([
                'title'       => trans('Cronofy.consent_form.static_data.title'),
                'description' => trans('Cronofy.consent_form.static_data.description'),
                'category'    => 1 // Offline
            ]);

            if(!empty($data)){
                ConsentFormQuestions::create([
                    'consent_id'  => $data->id,
                    'title'       => trans('Cronofy.consent_form.static_data.question'),
                    'description' => trans('Cronofy.consent_form.static_data.question_description'),
                ]);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }
}
