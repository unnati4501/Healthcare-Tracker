<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(TimezonesTableSeeder::class);
        $this->call(IndustriesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(PermissionRoleSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(SubCategoriesTableSeeder::class);
        $this->call(TrackerExercisesTableSeeder::class);
        $this->call(ChallengeCategoryTableSeeder::class);
        $this->call(ChallengeTargetTableSeeder::class);
        // $this->call(RecipeCategoryTableSeeder::class);
        $this->call(HsCategoryTableSeeder::class);
        $this->call(HsSubCategoryTableSeeder::class);
        $this->call(HsQuestionTypeTableSeeder::class);
        $this->call(HsQuestionsSeeder::class);
        // $this->call(MoodsTableSeeder::class);
        $this->call(ChallengeBadgeSeeder::class);
        $this->call(PersonalChallengeBadgeSeeder::class);
        $this->call(SPDashboard::class);
        $this->call(SPHealthScoreSeeder::class);
        $this->call(AppVersionSeeder::class);
        $this->call(SPChallengePointCalculation::class);
        $this->call(ZcQuestionTypesSeeder::class);
        $this->call(SPDashboardNewSeeder::class);

        /*
         * Seeders for SPRINT 57
         */
        $this->call(CompanyPlanTableSeeder::class); // Seed for when we added new company modules and features for plan
        /*
         * Seeders for SPRINT 88
         */
        $this->call(ContentChallengeTableSeeder::class); // Seed for when we add content challenge

        /*
         * Seeders for SPRINT 87
         */
        //$this->call(UpdatePlanForResellerCompanies::class); // Seed for when we added new company modules and features for plan

        /*Data transfer or one time seeders*/

        // $this->call(InspireMigrationSeeder::class);
        // $this->call(ZevoHealthTableSeeder::class);
        // $this->call(OldBadgeDeleteSeeder::class);
        // $this->call(UserNotificationNewSeed::class);
        // $this->call(CourseDataTransferSeeder::class);
        // $this->call(FeedDataTransferSeeder::class);
        // $this->call(GroupDataTransferSeeder::class);
        // $this->call(MeditationDataTransferSeeder::class);
        // $this->call(RecipeDataTransferSeeder::class);

        /*
         * One time seeders created in sprint 25
         */
        // $this->call(CourseDataRemoveSeeder::class);
        // $this->call(ResetAllUsersNotificationSettingToDefaultSeed::class); // This will update each users to notification settings preference to default as per zevolifesettings.notificationModules as well as add if any user's module entries are missing

        /*
         * One time seeders created in sprint 26
         */
        // $this->call(OldFeedDataRemoveSeeder::class); // This will remove old feeds data and this will run single time only

        /*
         * One time seeders created in sprint 29
         */
        // $this->call(OldNpsSurveyDataRemoveSeeder::class); // This will remove old Nps Survey data and this will run single time only
        // $this->call(SetOnboardingScreenPrioritySeeder::class); // This will set default priority to data on onboarding screen

        /*
         * One time seeders created in sprint 30
         */
        // $this->call(UpdateExistingUsersStartDateSeeder::class); // This will insert start date for existing users

        /*
         * One time seeders created in sprint 33
         */
        // $this->call(ChallengeImageLibraryTargetTypes::class); // This will insert default image library categories
        // $this->call(MeditationDataUpdateSeeder::class); // This will update default Audio Type of all meditations
        // $this->call(UpdateOldZCSurveyQuestionsOrderPriority::class); // This will update order priority of old ZcSurveyQuestions

        /*
         * One time seeders created in sprint 35
         */
        // $this->call(OldFeedDataRemoveSeeder::class); // This will sync existing masterclasses to all companies
        // $this->call(MigrateUsersToWaterWipesEmail::class); // Seed for migrate irishbreeze.com emails to waterwipes.com on production.

        /*
         * One time seeders created in sprint 36
         */
        // $this->call(AttachCompanyGroupRolesToAllCompanies::class); // seed for attach company group roles to all comapnies.

        /*
         * One time seeders created in sprint 37
         */
        // $this->call(ConvertExistingHCUsersToHCRole::class); // seed for convert existing HC user to normal user

        /*
         * One time seeders created in sprint 38
         */
        // $this->call(AddMoodTypeNotificationForExistingUsers::class); // seed for add mood type notificaion for existing users

        /*
         * One time seeders created in sprint 39
         */
        // $this->call(UpdateCompanyvisibilityEapRecords::class); // seed for update all company visibility records for eap

        /*
         * One time seeders created in sprint 43
         */
        // $this->call(AddWebinarTypeNotificationForExistingUsers::class); // seed for add webinars type notificaion for existing users
        // $this->call(ConvertExistingHCSlotsToTheirTimestamp::class); // seed for convert all existing slots timezone to hc's timezone from UTC
        // $this->call(UpdateCompanyVisibilityMeditationTracksSeeder::class); // seed for add company visibility for existing meditation records
        // $this->call(UpdateCompanyVisibilityWebinarRecords::class); // seed for add company visibility for existing webinar records
        // $this->call(EventDeepLinkUriUpdate::class); // seed for update deep link url for all events

        /*
         * One time seeders created in sprint 44
         */
        // $this->call(EnableZendeskFalgForExistingCompanies::class); // seed for enable 'is_intercom'(field is being used for 'zendesk' as of now) field to true for all the companies as requested by client ticket #ZL-3198
        // $this->call(UpdateCompanySizeDropdownSeeder::class); // seed for update company size dropdown when use 500+ value in company size dropdown
        // $this->call(UpdateCompanyVisibilityRecipeRecords::class); // seed for add company visibility for existing recipe records

        /*
         * One time seeders created in sprint 47
         */
        // $this->call(AddDefaultAppThemes::class); // seed for add default app themes
        // $this->call(SetMCSurveyOptionsScore::class); // seed for set score of the survey questions
        // $this->call(UpdateCompanyIdColumnCourseSurveyQuestionAnswers::class); // seed for set company id of course survey question answers
        // $this->call(UpdateMaxScoreToZcSurveyResponses::class); // seeds for add Max score of the question's option for each answers.
        // $this->call(RemoveAllAuditSurveyData::class); // seed to remove all the audit related survey details from the database.

        //$this->call(AddTagsForAllContents::class); // seed for add default app themes

        /*
         * One time seeders created in sprint 49
         */
        // $this->call(DisableSurveyForNonBindedCompany::class); // seed to disable survey for those company which survey is enabled but not binded/assigned any survey

        /*
         * One time seeders created in sprint 53
         */
        // $this->call(ChallengeImageLibraryTargetTypes::class); // seed to add one more categories in challenge image library

        /*
         * One time seeders created in sprint 56
         */
        // $this->call(AddDefaultCategoryForTags::class); // seed to enable has_tags for pre defined categories
        // $this->call(AddPredefineBadgesTableSeeder::class); // seed to default badge for daily and masterclass

        /*
         * One time seeders created in sprint 57
         */
        // $this->call(PlanTableSeeder::class); // Added default plan seeder
        // $this->call(DisableRecipesNotificaionToggleForAllUsers::class); // seed to disable recipes notificaion toggle for all users
        // $this->call(FeaturePlansSeeder::class); // Feature attech to default four plan
        // $this->call(AttechPlanToCompany::class); // Attech Company to company default plan

        /**
         * One Time Seeders updated in SPRINT 59
         */
        // $this->call(CompanyPlanTableSeeder::class); // Plan update as per confuence update
        // $this->call(FeaturePlansSeeder::class); // Plan assign update as per confuence update
        // $this->call(SPDashboardNewSeeder::class); // New Added EAP Avtivity Tab SP
        //$this->call(UpdatePortalSubDescriptionSeeder::class); // Update portal subdescription
        $this->call(UpdateContactusForPortalBranding::class); // Update portal subdescription
        /**
         * One Time Seeder update in SPRINT 60
         * /
        // $this->call(AttechPlanToCompany::class); // Update Default plan 'eap with challenge' to 'challenge'

        /**
         * One time seed update in SPRINT 61
         * /
        // $this->call(FeaturePlansSeeder::class); // Company plan Permission change
         */

        /**
         * One time seed update in SPRINT 72
         * /
        // $this->call(FindUserStepsAuthenticatorAvg::class); // Find user steps authencator avg till 01/March/2022
         */

        /**
         * One time seed update in SPRINT 75
         * /
        // $this->call(UpdateExistingWebinarteam::class); // Existing Webinar company attech with his team attech
        // $this->call(UpdateExistingRecipeTeam::class); // Existing Recipe company attech with his team attech
        // $this->call(UpdateExistingFeedTeam::class); // Existing Feed company attech with his team attech
        */

        /**
         * One time seed update in SPRINT 76
         * /
        // $this->call(UpdateExistingMasterclassTeam::class); // Existing Masterclass company attech with his team attech
        // $this->call(UpdateExistingMeditationTeam::class); // Existing Meditation company attech with his team attech
        */

        /**
         * One time seed update in SPRINT 77
         */
        // $this->call(UpdateExistingMeditationSubcategoriesIcons::class); // Existing meditation sub categories icon updated
        // $this->call(PermissionsTableSeeder::class); // Permission for well-being specialist
        // $this->call(PermissionRoleSeeder::class); // Permission for well-being specialist
        // $this->call(TimezonesTableSeeder::class); // User timezone updated.

        /**
         * One time seed update in SPRINT 78
         */
        // $this->call(PlanTableSeeder::class); // Plan table seeder
        // $this->call(PermissionRoleSeeder::class); // Permission role seeder
        // $this->call(UpdateExistingWSUserDetails::class); // Update existing ws user details

        /**
         * One time seed update in SPRINT 79
         */
        // $this->call(PermissionRoleSeeder::class); // Permission role seeder
        // $this->call(PermissionsTableSeeder::class); // Permission table seeder

        /**
         * One time seed update in SPRINT 81
         */
        // $this->call(AddNotesToSessionUserNotes::class); // Copy user notes calendly to session user notes

        /**
         * One time seed update in SPRINT 87
         */
        // $this->call(InviteExistingWellbeingConsultant::class); // Invite email to existing Wellbeing consultant

        /**
         * One time seed update in SPRINT 103
         */
        // $this->call(UpdateContactusForPortalBranding::class); // Set contact us details for portal branding
    }
}
