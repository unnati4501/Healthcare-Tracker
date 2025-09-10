<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\CpPlan;
use Illuminate\Database\Seeder;

/**
 * Class FeaturePlansSeeder.
 */
class FeaturePlansSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate table
        // $this->truncateMultiple(['cp_plan_features']);

        // Disable foreign key checks!
        $this->disableForeignKeys();

        /**
         * Assign feature for EAP with Challenge plan
         */
        $eapWithChallenge = [
            3, 4, // Team Selection , Audit Survey
            6, 7, 8, // Wellbeing Score card, Group, Recommendations
            10, // My challenges
            12, 13, 14, // FAQs, Tech Support, Support,
            15, // Event
            16, // EAP
        ];

        /**
         * Assign feature for EAP plan
         */
        $eap = [
            4, // Audit Survey
            6, 8, // Wellbeing Score card, Recommendations
            12, 13, 14, // FAQs, Tech Support, Support,
            15, // Event
            16, // EAP
        ];

        /**
         * Assign feature for challenge plan
         */
        $challenge = [
            3, 4, // Team Selection , Audit Survey
            6, 7, 8, // Wellbeing Score card, Group, Recommendations
            10, // My challenges
            12, 13, 14, // FAQs, Tech Support, Support,
            15, // Event
        ];

        /**
         * Assign feature for standard plan
         */
        $standard = [
            4, // Audit Survey
            6, 8, // Wellbeing Score card, Group, Recommendations
            12, 13, 14, // FAQs, Tech Support, Support,
            15, // Event
        ];

        /**
         * Assign feature for portal standard plan
         */
        $portalStandard = [
            18, 19, // Audit Survey, Goals Selection
            21, // Wellbeing Scorecard
            23, 24, 25, 27, 28, 29, // Masterclass, Events, Explore, Goals, Supports, Contact
        ];

        /**
         * Assign feature for portal digital therapy plan
         */
        $portalDigitalTherapy = [
            26, 28, 29 // Digital Therapy, Supports, Contact
        ];

        /**
         * Assign feature for portal standard with digital therapy plan
         */
        $portalStandardWithDigitalTherapy = [
            18, 19, // Audit Survey, Goals Selection
            21, // Wellbeing Scorecard
            23, 24, 25, 26, 27, 28, 29 // Masterclass, Events, Explore, Goals, Digital Therapy, Supports, Contact
        ];

        CpPlan::where('slug', 'eap-with-challenge')->first()->planFeatures()->sync($eapWithChallenge);
        CpPlan::where('slug', 'eap')->first()->planFeatures()->sync($eap);
        CpPlan::where('slug', 'challenge')->first()->planFeatures()->sync($challenge);
        CpPlan::where('slug', 'standard')->first()->planFeatures()->sync($standard);

        //Assign reseller company plans
        CpPlan::where('slug', 'portal-standard')->first()->planFeatures()->sync($portalStandard);
        CpPlan::where('slug', 'portal-digital-therapy')->first()->planFeatures()->sync($portalDigitalTherapy);
        CpPlan::where('slug', 'portal-standard-with-digital-therapy')->first()->planFeatures()->sync($portalStandardWithDigitalTherapy);


        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
