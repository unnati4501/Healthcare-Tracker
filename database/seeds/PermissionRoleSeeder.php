<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Class PermissionRoleSeeder.
 */
class PermissionRoleSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        /*
         * Assign permission to super admin role
         */
        $superAdminPermission = [
            2, 132, 133, 134, 135, 136, // Manage category
            7, 8, 9, 10, // Manage role
            12, 13, 14, 15, 16, 101, 207, 242, // Manage user
            19, 20, 21, 22, 23, 24, 25, 26, 118, 119, 233, 240, 249, 271, 302, // Manage company, Masterclass Survey Report, Survey Configuration
            28, 29, 30, 31, 32, // Manage department
            34, 35, 36, 37, // Manage team
            39, 40, 41, 42, // Manage location
            44, 45, 46, 47, // Manage meditation category
            49, 50, 51, 52, // Manage meditation library
            54, 55, 56, 57, // Manage exercise
            59, 60, 61, 62, 63, // Manage badge
            65, 66, 67, 68, 69, 155, 197, // Manage course
            71, 72, 73, 74, 75, 156, // Manage feed
            90, 91, // Manage app settings
            93, 94, 95, 96, // Manage onboarding
            98, 99, 100, // Manage notification
            103, 104, 137, 157, 250, // Manage reports
            106, 107, // Welllbeing squestioners
            109, 110, 154, // Dashboard/ Welllbeing dashboard / Moods analysis
            113, 114, 115, 116, 117, // manage-recipe/ create-recipe/ update-recipe/ delete-recipe/ view-recipe
            121, 122, 123, 124, 125, 126, 230, // Manage inter company challenge
            128, 129, 130, 131, // Manage personal challenge
            138, // EAP Introduction
            139, 140, 141, 142, 143, // Manage/Create/Edit/Delete EAP
            145, 146, 147, 148, // Manage moods
            150, 151, 152, 153, // Manage mood tags
            159, 160, 161, 162, // Manage Survey Category
            163, 164, 165, 166, 167, // Manage Survey Sub Category
            169, 170, 171, 172, 173, // Manage Survey Question Bank
            175, 176, 177, 178, 179, 189, 196, 199, 198, 248, 268, 289, 296, 310, // Manage Survey Question Bank, Review/Suggestions, Survey Insights, HR Report and View HR Report, Masterclass feedback report, digital therapy report, occupational health report, Usage Report,
            181, 182, // Manage Imports (Users, Questions)
            184, 188, 232, // Manage Project Survey, Manage Portal Survey
            192, 193, 194, 195, 205, 206, // Manage Goal Tags
            201, 202, 203, 204, // Challenge image library
            213, 214, 215, 216, // Booking Report
            227, 209, 210, 211, 212, // Webinar Management
            218, 219, 220, 221, 222, 229, 231, // Event Management
            224, 225, 226, // Marketplace, Market Place List, Book Event, Cancel Event
            235, 236, 237, 238, 239, // App Skin Management, Create, Update, Delete
            244, 245, 246, 247, // Manage Broadcast Message
            252, // Manage Clients
            255, 256, 291, // Manage Sessions,View Sessions, Manage Consent Form,
            258, 259, 260, 261, 262, // Manage/Add/View/Edit/Delete Category Tags
            263, 264, 265, 266, 267, // Manage/Add/View/Edit/Delete Company Plan,
            269, // User Registration Report
            316, // Realtime Welbeing Availability Report
            270, // Bookings
            273, 274, 275, 276, // Challenge Map Library,
            282, 283, 284, 285, 286, // Manage/Add/Edit/Delete Services
            292, 293, 294, // Manage Content challenge
            298, 299, 300, 301, // Manage/Add/Edit/Delete Podcast
            303, 304, 305, 306, // Manage/Add/Edit/Delete DT Banners
            308, 309, // Manage/Edit Admin Alert,
            312, 313, 314, 315 // Manage/Add/Edit/Delete Shorts
        ];

        /*
         * Assign permission to company admin role
         */
        $companyAdminPermission = [
            12, 13, 14, 15, 16, 101, // Manage user
            28, 29, 30, 31, 32, // Manage department
            34, 35, 36, 37, 111, 234, // Manage team/ Team Assignment/ Set Team Limit
            39, 40, 41, 42, // Manage location
            59, 63, // Manage badge
            71, 72, 73, 74, 75, 156, 290, // Manage feed
            77, 78, 79, 80, 81, // Manage group
            83, 84, 85, 86, 87, 88, // Manage challenge
            98, 99, 100, 228, // Manage notification
            109, 110, 154, // Dashboard/ Welllbeing dashboard / Moods analysis
            113, 114, 115, 116, 117, // manage-recipe/ create-recipe/ update-recipe/ delete-recipe/ view-recipe
            121, 125, // Manage inter company challenge
            128, 129, 130, 131, // Manage personal challenge
            138, // EAP Introduction
            139, 140, 141, 142, 143, // Manage/Create/Edit/Delete EAP
            189, // Manage Survey Question Bank, Review/Suggestions
            190, 196, 199, 198, 173, // Survey Insights, HR Report and View HR Report
            184, 185, 186, 187, 188, // Manage Project Survey
            244, 245, 246, 247, // Manage Broadcast Message
            218, 219, 220, 221, 222, 229, 231, // Event Management
            224, 225, 226, 241, // Marketplace, Market Place List, Book Event, Cancel Event, Event registered users
            213, 214, 215, 216, // Booking Report,
            269, //User Registration Report,
            270, //Bookings
            // 273, // Challenge Map Library
            255, 256, 287, 288, // Manage Sessions
        ];

        /*
         * Assign permission to reseller super admin role
         */
        $resellerSuperAdminPermission = [
            12, 13, 14, 15, 16, // Manage user
            19, 20, 21, 22, 23, 24, 25, 26, 240, // Manage company, Masterclass Survey Report
            28, 29, 30, 31, 32, // Manage department
            34, 35, 36, 37, // Manage team
            39, 40, 41, 42, // Manage location
            71, 72, 73, 74, 75, 156, // Manage feed
            113, 114, 115, 116, 117, // manage-recipe/ create-recipe/ update-recipe/ delete-recipe/ view-recipe
            138, // EAP Introduction
            139, 140, 141, 142, 143, // Manage/Create/Edit/Delete EAP
            98, 99, 100, // Manage notification
            109, // Dashboard
            189, 196, 199, 198, 173, // Manage Survey Question Bank, Review/Suggestions, Survey Insights, HR Report and View HR Report
            213, 214, 215, 216, // Booking Report
            189, 190, // Review/Suggestions
            218, 219, 220, 221, 222, 229, 231, // Event Management
            224, 225, 226, 241, // Marketplace, Market Place List, Book Event, Cancel Event, Event registered users
            232, // Manage Portal Survey
            250, // Manage Report - Content Report,
            269, //User Registration Report
            270, //Bookings,
            255, 256, // Manage Sessions,View Sessions
        ];

        /*
         * Assign permission to reseller company admin role
         */
        $resellerCompanyAdminPermission = [
            12, 13, 14, 15, 16, 101, // Manage user
            28, 29, 30, 31, 32, // Manage department
            34, 35, 36, 37, 111, // Manage team/ Team Assignment
            39, 40, 41, 42, // Manage location
            71, 72, 73, 74, 75, 156, // Manage feed
            77, 78, 79, 80, 81, // Manage group
            113, 114, 115, 116, 117, // manage-recipe/ create-recipe/ update-recipe/ delete-recipe/ view-recipe
            138, // EAP Introduction
            139, 140, 141, 142, 143, // Manage/Create/Edit/Delete EAP
            98, 99, 100, // Manage notification
            109, // Dashboard
            213, 214, 215, 216, // Booking Report
            110, 154, // Welllbeing dashboard / Moods analysis
            189, 190, 196, 198, 199, 173, // Manage Survey Question Bank, Review/Suggestions, Survey Insights, HR Report and View HR Report
            218, 219, 220, 221, 222, 229, 231, // Event Management
            224, 225, 226, 241, // Marketplace, Market Place List, Book Event, Cancel Event, Event registered users
            121, // Manage inter-company challenge
            83, 84, 85, 86, 87, 88, // Manage challenge
            128, 129, 130, 131, // Manage personal challenge
            59, 63, // Manage badge
            244, 245, 246, 247, // Manage Broadcast Message,
            269, 250, //User Registration Report,
            270, //Bookings
            255, 256, // Manage Sessions, View Sessions
        ];

        /*
         * Assign permission to Wellbeing consultant
         */
        $healthCocahRolePermission = [
            // 109, // Dashboard
            278, 279, 280, 270,// My profile, Authenticate, Availability, Bookings,
        ];

        /*
         * Assign permission to counsellor role
         */
        $counsellorRolePermission = [
            109, // Dashboard
            252, 253, // Manage Clients
            255, 256, // Manage Sessions
        ];

        /**
         * Assign permission to Wellbeing Specialist role
         */
        $wellbeingSpecialistRolePermission = [
            278, 279, 280, // My profile, Authenticate, Availability
            252, 253, // Manage Clients
            255, 256, 287, 288, // Manage Sessions,
            270, //Bookings
        ];

        /**
         * Assign permission to Clinical Lead
         */
        $wellbeingTeamLeadRolePermission = [
            109, // Dashboard
            252, 253, 295,// Manage Clients, Add Occupational Health Referral
            255, 256, // Manage Sessions,
            289, 296 // Digital Therapy Report, Occupational Health Report
        ];

        Role::find(1)->permissions()->sync($superAdminPermission);
        Role::find(2)->permissions()->sync($companyAdminPermission);

        Role::where('slug', 'reseller_super_admin')->first()->permissions()->sync($resellerSuperAdminPermission);
        Role::where('slug', 'reseller_company_admin')->first()->permissions()->sync($resellerCompanyAdminPermission);
        Role::where('slug', 'health_coach')->first()->permissions()->sync($healthCocahRolePermission);
        Role::where('slug', 'counsellor')->first()->permissions()->sync($counsellorRolePermission);
        Role::where('slug', 'wellbeing_specialist')->first()->permissions()->sync($wellbeingSpecialistRolePermission);
        Role::where('slug', 'wellbeing_team_lead')->first()->permissions()->sync($wellbeingTeamLeadRolePermission);


        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
