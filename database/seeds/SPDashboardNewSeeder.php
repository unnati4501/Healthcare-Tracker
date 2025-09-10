<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SPDashboardNewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            //DB::beginTransaction();
            $unpreparedStmt = <<<'UNPREPAREDSTMT'
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_users`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_teams`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_challenges`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_steps_period`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_calories_period`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_popular_feeds`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_sync_details`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_active_individual`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_active_team`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_badges_earned`;

DROP PROCEDURE IF EXISTS `sp_dashboard_hs_category_wise`;
DROP PROCEDURE IF EXISTS `sp_dashboard_hs_sub_category_wise`;
DROP PROCEDURE IF EXISTS `sp_dashboard_hs_attempted_by`;

DROP PROCEDURE IF EXISTS `sp_dashboard_physical_steps_range`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_exercises_range`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_popular_exercises`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_popular_recipes`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_bmi`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_popular_exercises_tracker`;
DROP PROCEDURE IF EXISTS `sp_dashboard_physical_most_popular_exercises`;

DROP PROCEDURE IF EXISTS `sp_dashboard_psychological_meditation_hours`;
DROP PROCEDURE IF EXISTS `sp_dashboard_psychological_popular_meditations`;
DROP PROCEDURE IF EXISTS `sp_dashboard_psychological_top_meditations`;
DROP PROCEDURE IF EXISTS `sp_dashboard_psychological_moods_analysis`;

DROP PROCEDURE IF EXISTS `sp_dashboard_audit_company_score`;
DROP PROCEDURE IF EXISTS `sp_dashboard_audit_company_score_baseline`;
DROP PROCEDURE IF EXISTS `sp_dashboard_audit_company_score_line_graph`;
DROP PROCEDURE IF EXISTS `sp_dashboard_audit_category_wise_graph`;
DROP PROCEDURE IF EXISTS `sp_dashboard_audit_category_wise_tabs`;
DROP PROCEDURE IF EXISTS `sp_dashboard_audit_sub_category_wise_graph`;

DROP PROCEDURE IF EXISTS `sp_dashboard_booking_tab`;
DROP PROCEDURE IF EXISTS `sp_dashboard_booking_tab_events_revenue`;
DROP PROCEDURE IF EXISTS `sp_dashboard_booking_tab_today_event_calendar`;

DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_popular_webinar`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_popular_masterclass`;

DROP PROCEDURE IF EXISTS `sp_dashboard_usage_top_webinar`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_top_masterclass`;
DROP PROCEDURE IF EXISTS `sp_dashboard_app_usage_top_feeds`;

DROP PROCEDURE IF EXISTS `sp_dashboard_eap_activity_tab_session_count`;
DROP PROCEDURE IF EXISTS `sp_dashboard_eap_activity_tab_counsellors_count`;
DROP PROCEDURE IF EXISTS `sp_dashboard_eap_activity_tab_utilization`;
DROP PROCEDURE IF EXISTS `sp_dashboard_eap_activity_tab_skill_trend`;
DROP PROCEDURE IF EXISTS `sp_dashboard_eap_activity_tab_appointment`;

DROP PROCEDURE IF EXISTS `sp_dashboard_digital_therapy_tab_session_count`;
DROP PROCEDURE IF EXISTS `sp_dashboard_digital_therapy_tab_wellbeing_specialist_count`;
DROP PROCEDURE IF EXISTS `sp_dashboard_digital_therapy_tab_utilization`;
DROP PROCEDURE IF EXISTS `sp_dashboard_digital_therapy_tab_skill_trend`;
DROP PROCEDURE IF EXISTS `sp_dashboard_digital_therapy_tab_appointment`;


DROP PROCEDURE IF EXISTS `sp_calculate_user_step_avg`;

DROP PROCEDURE IF EXISTS `sp_dashboard_booking_tab_event_count`;
DROP PROCEDURE IF EXISTS `sp_dashboard_booking_tab_skill_trend`;

#Code for creating sp_dashboard_app_usage_users procedure
CREATE PROCEDURE `sp_dashboard_app_usage_users`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN fromLastSevenDaysDate VARCHAR(50),
    IN userId BIGINT(12),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND users.email != 'superadmin@grr.la'";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    IFNULL(SUM(DATE(CONVERT_TZ(users.last_activity_at, 'UTC', '", timezone, "')) >= '", fromDate ,"'), 0) as activeUsers,
    IFNULL(SUM(DATE(CONVERT_TZ(users.last_activity_at, 'UTC', '", timezone, "')) >= '", fromLastSevenDaysDate ,"'), 0) as activeUsersForLast7Days,
    COUNT(users.id) as totalUsers
    FROM users
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, " AND users.id != ", userId, ";");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for create sp_dashboard_app_usage_teams procedure
CREATE PROCEDURE `sp_dashboard_app_usage_teams`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN locationId VARCHAR(121),
    IN departmentId BIGINT(12)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "1 = 1";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND team_location.company_id IN (", companyId, ") ");
END IF; 
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND team_location.company_location_id = ", locationId);
END IF;     
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND team_location.department_id = ", departmentId);
END IF;


SET @q = CONCAT("SELECT
    COUNT(DISTINCT(team_location.team_id)) as newTeams,
    (
        SELECT COUNT(DISTINCT(teams.id)) FROM teams
        INNER JOIN team_location ON (team_location.team_id = teams.id)
        WHERE ", whereCond, "
    ) as totalTeams
    FROM teams
    INNER JOIN team_location ON (team_location.team_id = teams.id)
    WHERE CONVERT_TZ(teams.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "'
    AND ", whereCond, ";");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_challenges procedure
CREATE PROCEDURE `sp_dashboard_app_usage_challenges`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN roleGroup VARCHAR(50)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "challenges.cancelled = false AND challenges.challenge_type != 'inter_company'";
DECLARE whereCond2 TEXT DEFAULT "challenges.cancelled = false AND challenges.challenge_type = 'inter_company'";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND challenges.company_id IN (", companyId ,")");
    SET whereCond2 = CONCAT(whereCond2, " AND challenge_participants.company_id IN (", companyId ,")");
END IF;

SET @ongoingChallenges = 0;
SET @upcomingChallenges = 0;
SET @completedChallenges = 0;

SET @icOngoingChallenges = 0;
SET @icUpcomingChallenges = 0;
SET @icCompletedChallenges = 0;

SET @q1 = CONCAT("SELECT
    SUM(CONVERT_TZ(start_date, 'UTC', '" ,timezone, "')  <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(end_date, 'UTC', '" ,timezone, "') >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')) as ongoing,
    SUM(CONVERT_TZ(start_date, 'UTC', '" ,timezone, "')  >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(end_date, 'UTC', '" ,timezone, "') >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')) as upcoming,
    SUM(CONVERT_TZ(start_date, 'UTC', '" ,timezone, "')  <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(end_date, 'UTC', '" ,timezone, "') <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')) as completed
    INTO @ongoingChallenges, @upcomingChallenges, @completedChallenges
    FROM challenges
    WHERE ", whereCond, ";");

SET @q2 = CONCAT("SELECT
    IFNULL(SUM(CONVERT_TZ(icdata.start_date, 'UTC', '" ,timezone, "')  <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(icdata.end_date, 'UTC', '" ,timezone, "') >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')), 0) as ongoing,
    IFNULL(SUM(CONVERT_TZ(icdata.start_date, 'UTC', '" ,timezone, "')  >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(icdata.end_date, 'UTC', '" ,timezone, "') >= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')), 0) as upcoming,
    IFNULL(SUM(CONVERT_TZ(icdata.start_date, 'UTC', '" ,timezone, "')  <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "') AND CONVERT_TZ(icdata.end_date, 'UTC', '" ,timezone, "') <= CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', '" ,timezone, "')), 0) as completed
    INTO @icOngoingChallenges, @icUpcomingChallenges, @icCompletedChallenges
    FROM (
        SELECT challenges.start_date, challenges.end_date
        FROM challenges
            INNER JOIN challenge_participants ON (challenge_participants.challenge_id = challenges.id)
            WHERE ", whereCond2, "
            GROUP BY challenges.id
        ) AS icdata");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

#IF (roleGroup = 'zevo') THEN
#    SET @ongoingChallenges = 0;
#    SET @upcomingChallenges = 0;
#    SET @completedChallenges = 0;
#END IF;

SELECT
    ROUND(IFNULL(@ongoingChallenges,0) + IFNULL(@icOngoingChallenges, 0)) AS totalOngoingChallenges,
    ROUND(IFNULL(@upcomingChallenges,0) + IFNULL(@icUpcomingChallenges,0)) AS totalUpComingChallenges,
    ROUND(IFNULL(@completedChallenges,0) + IFNULL(@icCompletedChallenges,0)) AS totalCompletedChallenges;

SET @q1 = NULL;
SET @q2 = NULL;
SET @ongoingChallenges = NULL;
SET @upcomingChallenges = NULL;
SET @completedChallenges = NULL;
SET @icOngoingChallenges = NULL;
SET @icUpcomingChallenges = NULL;
SET @icCompletedChallenges = NULL;

END;


#Code for creating sp_dashboard_app_usage_steps_period procedure
CREATE PROCEDURE `sp_dashboard_app_usage_steps_period`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    us.steps as averageSteps,
    ROUND(
    (COUNT(us.user_id) * 100) /
    (
        SELECT COUNT(DISTINCT(user_step.user_id))
        FROM user_step
        INNER JOIN users ON (users.id = user_step.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(log_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    ), 1) AS userPercent
    FROM (
        SELECT
            DISTINCT(user_step.user_id),
            IF(ROUND((SUM(steps) / " , days , "), -3) >= 20000, 20000, ROUND((SUM(steps) / " , days , "), -3)) AS steps
        FROM user_step
        INNER JOIN users ON (users.id = user_step.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(log_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
        GROUP BY user_step.user_id
    ) AS us
    GROUP BY us.steps;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_calories_period procedure
CREATE PROCEDURE `sp_dashboard_app_usage_calories_period`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    us.calories as averageCalories,
    ROUND(
    (COUNT(us.user_id) * 100) /
    (
        SELECT COUNT(DISTINCT(user_step.user_id))
        FROM user_step
        INNER JOIN users ON (users.id = user_step.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(log_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    ), 1) AS userPercent
    FROM (
        SELECT
            DISTINCT(user_step.user_id),
            IF(ROUND((SUM(calories) / " , days , "), -3) >= 20000, 20000, ROUND((SUM(calories) / " , days , "), -3)) AS calories
        FROM user_step
        INNER JOIN users ON (users.id = user_step.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(log_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
        GROUP BY user_step.user_id
    ) AS us
    GROUP BY us.calories;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_popular_feeds procedure
CREATE PROCEDURE `sp_dashboard_app_usage_popular_feeds`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)");

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    sub_categories.name as feedCategory,
    SUM(feed_user.view_count) as totalViews
    FROM feeds
    INNER JOIN feed_user ON (feed_user.feed_id = feeds.id) AND CONVERT_TZ(feed_user.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "'
    INNER JOIN sub_categories ON (feeds.sub_category_id = sub_categories.id)
    INNER JOIN users ON (feed_user.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY sub_categories.name;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_sync_details procedure
CREATE PROCEDURE `sp_dashboard_app_usage_sync_details`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    IFNULL(ROUND((COUNT(DISTINCT(user_step.user_id)) * 100) /
    (
        SELECT COUNT(DISTINCT(users.id))
        FROM users
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        WHERE ", whereCond, "
    ), 1),0) as syncPercent
    FROM user_step
    INNER JOIN users ON (user_step.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_step.log_date, 'UTC', '" , timezone , "') BETWEEN TIMESTAMP(DATE_SUB(CURRENT_DATE(), INTERVAL 3 DAY)) AND TIMESTAMP(CURRENT_TIMESTAMP());");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_active_individual procedure
CREATE PROCEDURE `sp_dashboard_app_usage_active_individual`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    user_exercise.user_id,
    ROUND(SUM(duration/3600), 1) as totalHours
    FROM user_exercise
    INNER JOIN users ON (users.id = user_exercise.user_id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_exercise.start_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY user_exercise.user_id
    ORDER BY totalHours DESC
    LIMIT 5;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_active_team procedure
CREATE PROCEDURE `sp_dashboard_app_usage_active_team`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    user_team.team_id,
    ROUND((SUM(duration/3600) / (
        SELECT COUNT(DISTINCT(ut.user_id))
        FROM user_team ut
        INNER JOIN users ON (users.id = ut.user_id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        WHERE ut.team_id=user_team.team_id
        AND ", whereCond, "
    )), 1) as averageHours
    FROM user_exercise
    INNER JOIN users ON (users.id = user_exercise.user_id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_exercise.start_date, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY user_team.team_id
    ORDER BY averageHours DESC
    LIMIT 5;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_app_usage_badges_earned procedure
CREATE PROCEDURE `sp_dashboard_app_usage_badges_earned`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    badge_user.user_id,
    COUNT(badge_user.badge_id) as mostBadges
    FROM badge_user
    INNER JOIN users ON (users.id = badge_user.user_id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND badge_user.status='Active'
    AND CONVERT_TZ(badge_user.created_at, 'UTC', '" , timezone , "') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY badge_user.user_id
    ORDER BY mostBadges DESC
    LIMIT 5;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_hs_category_wise procedure
CREATE PROCEDURE `sp_dashboard_hs_category_wise`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN categoryId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

SET @totalSurveyUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT(hs_survey.user_id)) INTO @totalSurveyUsers
    FROM hs_survey_responses
    INNER JOIN hs_survey on (hs_survey_responses.survey_id = hs_survey.id)
    INNER JOIN users on (hs_survey.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND hs_survey_responses.category_id=", categoryId ,"
    AND CONVERT_TZ(hs_survey_responses.created_at, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"';");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    hs.physicalScore,
    IF(@totalSurveyUsers > 0, ROUND((COUNT(hs.physicalScore) * 100) / @totalSurveyUsers, 1), 0) as percent
    FROM (
    SELECT
        CASE
        WHEN ROUND((SUM(score) * 100) / SUM(max_score), 1) < 60  THEN 'Low'
        WHEN ROUND((SUM(score) * 100) / SUM(max_score), 1) BETWEEN 60 AND 80 THEN 'Moderate'
        ELSE 'High'
        END AS physicalScore
    FROM hs_survey_responses
    INNER JOIN hs_survey on (hs_survey_responses.survey_id = hs_survey.id)
    INNER JOIN hs_questions on (hs_survey_responses.question_id = hs_questions.id)
    INNER JOIN users on (hs_survey.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND hs_survey_responses.category_id=", categoryId ,"
    AND CONVERT_TZ(hs_survey_responses.created_at, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
    GROUP BY hs_survey.user_id) as hs
    GROUP BY physicalScore
    ORDER BY
        CASE WHEN physicalScore = 'Low' THEN '1'
            WHEN physicalScore = 'Moderate' THEN '2'
            ELSE physicalScore
            END ASC;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;


#Code for creating sp_dashboard_hs_sub_category_wise procedure
CREATE PROCEDURE `sp_dashboard_hs_sub_category_wise`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN categoryId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    hs_sub_categories.display_name as sub_category,
    ROUND((SUM(score) * 100) / SUM(max_score), 1) as percent
    FROM hs_survey_responses
    INNER JOIN hs_survey on (hs_survey_responses.survey_id = hs_survey.id)
    INNER JOIN hs_questions on (hs_survey_responses.question_id = hs_questions.id)
    INNER JOIN hs_sub_categories on (hs_survey_responses.sub_category_id = hs_sub_categories.id)
    INNER JOIN users on (hs_survey.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND hs_survey_responses.category_id=", categoryId ,"
    AND CONVERT_TZ(hs_survey_responses.created_at, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
    GROUP BY hs_survey_responses.sub_category_id");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_hs_attempted_by procedure
CREATE PROCEDURE `sp_dashboard_hs_attempted_by`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN categoryId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";
DECLARE andCond TEXT DEFAULT "";
DECLARE whereCondLocationId TEXT DEFAULT "";

SET @totalSurveyUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF;
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

IF (locationId IS NOT NULL) THEN
    SET whereCondLocationId = CONCAT(whereCond , " AND company_locations.id = ", locationId);
ELSE
    SET whereCondLocationId = whereCond;
END IF;

IF (categoryId = 1) THEN
    SET andCond = CONCAT(andCond , "AND hs_survey.physical_survey_started=1 AND hs_survey.physical_survey_complete_time IS NOT NULL AND CONVERT_TZ(hs_survey.physical_survey_complete_time, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'");
ELSEIF (categoryId = 2) THEN
    SET andCond = CONCAT(andCond , "AND hs_survey.physcological_survey_started=1 AND hs_survey.physcological_survey_complete_time IS NOT NULL AND CONVERT_TZ(hs_survey.physcological_survey_complete_time, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'");
END If;

SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT(hs_survey.user_id)) INTO @totalSurveyUsers
    FROM hs_survey
    INNER JOIN users on (hs_survey.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCondLocationId, "");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    IF(@totalSurveyUsers > 0, ROUND((COUNT(hs_survey.id) * 100) / @totalSurveyUsers, 1), 0) as attemptedPercent
    FROM hs_survey
    INNER JOIN users on (hs_survey.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    WHERE ", whereCond, "
    ", andCond ,"");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;


#Code for creating sp_dashboard_physical_steps_range procedure
CREATE PROCEDURE `sp_dashboard_physical_steps_range`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

SET @totalStepUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT(user_step.user_id)) INTO @totalStepUsers
    FROM user_step
    INNER JOIN users on (user_step.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_step.log_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    us.stepRange,
    IF(@totalStepUsers > 0, ROUND((COUNT(us.user_id) * 100) / @totalStepUsers, 1), 0) as percent
    FROM(
        SELECT
            user_step.user_id,
            CASE
                WHEN ROUND((SUM(steps)/'",days,"'),1) < 8000 THEN 'Low'
                WHEN ROUND((SUM(steps)/'",days,"'),1) BETWEEN 8000 AND 12000 THEN 'Moderate'
                WHEN ROUND((SUM(steps)/'",days,"'),1) > 12000 THEN 'High'
            END as stepRange
        FROM user_step
        INNER JOIN users on (user_step.user_id = users.id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(user_step.log_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
        GROUP BY user_step.user_id) as us
    GROUP BY us.stepRange
    ORDER BY
        CASE WHEN stepRange = 'Low' THEN '1'
            WHEN stepRange = 'Moderate' THEN '2'
            ELSE stepRange
            END ASC;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;


#Code for creating sp_dashboard_physical_exercises_range procedure
CREATE PROCEDURE `sp_dashboard_physical_exercises_range`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

SET @totalExerciseUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT(user_exercise.user_id)) INTO @totalExerciseUsers
    FROM user_exercise
    INNER JOIN users on (user_exercise.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_exercise.start_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    ue.exerciseRange,
    IF(@totalExerciseUsers > 0, ROUND((COUNT(ue.user_id) * 100) / @totalExerciseUsers, 1), 0) as percent
    FROM(
        SELECT
            user_exercise.user_id,
            CASE
                WHEN ROUND(((SUM(duration) / 3600)/'",days,"'), 1) < 1 THEN 'Low'
                WHEN ROUND(((SUM(duration) / 3600)/'",days,"'), 1) BETWEEN 1 AND 4 THEN 'Moderate'
                WHEN ROUND(((SUM(duration) / 3600)/'",days,"'), 1) BETWEEN 4 AND 10 THEN 'High'
                WHEN ROUND(((SUM(duration) / 3600)/'",days,"'), 1) > 10 THEN 'Very High'
            END as exerciseRange
        FROM user_exercise
        INNER JOIN users on (user_exercise.user_id = users.id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND CONVERT_TZ(user_exercise.start_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
        GROUP BY user_exercise.user_id) as ue
    GROUP BY ue.exerciseRange
    ORDER BY
        CASE WHEN exerciseRange = 'Low' THEN '1'
            WHEN exerciseRange = 'Moderate' THEN '2'
            WHEN exerciseRange = 'High' THEN '3'
            ELSE exerciseRange
            END ASC;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;


#Code for creating sp_dashboard_physical_popular_exercises procedure
CREATE PROCEDURE `sp_dashboard_physical_popular_exercises`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1) AND DATE(CONVERT_TZ(user_exercise.end_date, 'UTC', '", timezone ,"')) >= '", fromDate, "'");

SET @totalExerciseUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT user_exercise.user_id) INTO @totalExerciseUsers
    FROM user_exercise
    INNER JOIN user_team ON (user_team.user_id = user_exercise.user_id)
    INNER JOIN user_profile ON (user_profile.user_id = user_exercise.user_id)
    INNER JOIN users ON (users.id = user_exercise.user_id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, ";");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    exercises.title,
    IF(@totalExerciseUsers > 0, ROUND(((COUNT(DISTINCT user_exercise.user_id) * 100) / @totalExerciseUsers),1), 0) as percent
    FROM exercises
    INNER JOIN user_exercise ON (exercises.id = user_exercise.exercise_id)
    INNER JOIN user_team ON (user_team.user_id = user_exercise.user_id)
    INNER JOIN user_profile ON (user_profile.user_id = user_exercise.user_id)
    INNER JOIN users ON (users.id = user_exercise.user_id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY exercises.id
    HAVING percent > 0;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;


#Code for creating sp_dashboard_physical_popular_recipes procedure
CREATE PROCEDURE `sp_dashboard_physical_popular_recipes`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    recipe.id as recipe_id,
    SUM(recipe_user.view_count) as totalViews
    FROM recipe
    INNER JOIN recipe_user ON (recipe_user.recipe_id = recipe.id)
    INNER JOIN users ON (recipe_user.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(recipe_user.created_at, 'UTC', '", timezone ,"') BETWEEN TIMESTAMP(DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) AND TIMESTAMP(DATE(CURRENT_DATE()))
    GROUP BY recipe_id
    HAVING totalViews > 0
    ORDER BY totalViews DESC, recipe_id ASC
    LIMIT 5;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_physical_bmi procedure
CREATE PROCEDURE `sp_dashboard_physical_bmi`(
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN gender VARCHAR(50)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

IF (gender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.gender = '", gender ,"'");
END IF;

SET @q = CONCAT("SELECT
    userBmi.bmi as label,
    COUNT(userBmi.bmi) as count,
    SUM(userBmi.weight) as weight
    FROM(
        SELECT
            bmi_tbl.user_id,
            CASE
                WHEN ROUND(bmi, 1) < 18.5 THEN 'UnderWeight'
                WHEN ROUND(bmi, 1) >= 18.5 AND ROUND(bmi, 1) < 25 THEN 'Normal'
                WHEN ROUND(bmi, 1) >= 25 AND ROUND(bmi, 1) < 30 THEN 'OverWeight'
                WHEN ROUND(bmi, 1) >= 30 THEN 'Obese'
            END AS bmi,
            weight
        FROM user_bmi bmi_tbl
        INNER JOIN users on (bmi_tbl.user_id = users.id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE bmi_tbl.id = (SELECT MAX(id) FROM user_bmi WHERE bmi_tbl.user_id = user_id)
        AND ", whereCond, "
        GROUP BY bmi_tbl.user_id) AS userBmi
    GROUP BY userBmi.bmi;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_psychological_meditation_hours procedure
CREATE PROCEDURE `sp_dashboard_psychological_meditation_hours`(
    IN timezone VARCHAR(50),
    IN duration VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(12),
    IN departmentId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", timezone ,"')) >= '", fromDate, "'");
DECLARE groupByCol VARCHAR(50) DEFAULT "";
DECLARE orderByCol VARCHAR(50) DEFAULT "";
DECLARE cols TEXT DEFAULT "";

SET groupByCol = (CASE
    WHEN (duration = "day") THEN "log_date_only"
    WHEN (duration = "month") THEN "log_date_week"
    WHEN (duration = "year") THEN "log_month"
END);

SET orderByCol = (CASE
    WHEN (duration = "day") THEN "log_date_only ASC"
    WHEN (duration = "month") THEN "log_date_week ASC"
    WHEN (duration = "year") THEN "log_date_year ASC"
END);

SET cols = (CASE
    WHEN (duration = "day") THEN CONCAT("DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", timezone ,"')) AS log_date_only")
    WHEN (duration = "month") THEN CONCAT("WEEK(DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", timezone ,"')), 1) AS log_date_week")
    WHEN (duration = "year") THEN CONCAT("DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", timezone ,"')) AS log_date_year, MONTH(user_listened_tracks.created_at) AS log_month")
END);

IF (companyId IS NOT NULL AND departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", companyId ,") AND user_team.department_id = ", pDepartmentId);
ELSEIF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", companyId ,")");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

set @q = CONCAT("SELECT
    ROUND((SUM(user_listened_tracks.duration_listened) / 60), 1) AS listened_hours, ",
    cols, "
    FROM user_listened_tracks
    INNER JOIN user_team ON (user_team.user_id = user_listened_tracks.user_id)
    INNER JOIN user_profile ON (user_profile.user_id = user_listened_tracks.user_id)
    INNER JOIN users ON (users.id = user_listened_tracks.user_id)
    WHERE ", whereCond, "
    GROUP BY ", groupByCol, "
    HAVING listened_hours > 0
    ORDER BY ", orderByCol);

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_psychological_popular_meditations procedure
CREATE PROCEDURE `sp_dashboard_psychological_popular_meditations`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    sub_categories.name as meditationCategory,
    SUM(user_meditation_track_logs.view_count) as totalViews
    FROM meditation_tracks
    INNER JOIN user_meditation_track_logs ON (user_meditation_track_logs.meditation_track_id = meditation_tracks.id) AND CONVERT_TZ(user_meditation_track_logs.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "'
    INNER JOIN sub_categories ON (meditation_tracks.sub_category_id = sub_categories.id)
    INNER JOIN users ON (user_meditation_track_logs.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY sub_categories.name;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_psychological_top_meditations procedure
CREATE PROCEDURE `sp_dashboard_psychological_top_meditations`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    meditation_tracks.title,
    SUM(user_meditation_track_logs.view_count) AS totalViews
    FROM meditation_tracks
    INNER JOIN user_meditation_track_logs ON (user_meditation_track_logs.meditation_track_id = meditation_tracks.id)
    INNER JOIN users ON (user_meditation_track_logs.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_meditation_track_logs.created_at, 'UTC', '", timezone ,"') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY title
    HAVING totalViews > 0
    LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_psychological_moods_analysis procedure
CREATE PROCEDURE `sp_dashboard_psychological_moods_analysis`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1) AND DATE(CONVERT_TZ(mood_user.created_at, 'UTC', '", timezone ,"')) >= '", fromDate, "'");

SET @totalMoodUsers = 0;


IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(mood_user.user_id) INTO @totalMoodUsers
    FROM mood_user
    INNER JOIN users ON (users.id = mood_user.user_id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, ";");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    moods.title,
    IFNULL((
        SELECT
            IF(@totalMoodUsers > 0, ROUND(COUNT(mood_user.mood_id) * 100 / @totalMoodUsers,1), 0)
        FROM mood_user
        INNER JOIN users ON (users.id = mood_user.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND mood_user.mood_id = moods.id
        GROUP BY mood_user.mood_id
    ),0) as percent
    FROM moods;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1= NULL;
SET @q2 = NULL;

END;

#Code for creating sp_dashboard_audit_company_score procedure
CREATE PROCEDURE `sp_dashboard_audit_company_score`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN maxScore INT(11),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (fromDate IS NOT NULL AND toDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "' AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", timezone ,"') <= '", toDate, "'");
END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) AS percentage
FROM zc_survey_responses
INNER JOIN users ON (users.id = zc_survey_responses.user_id)
INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
WHERE ", whereCond);

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_audit_company_score_baseline procedure
CREATE PROCEDURE `sp_dashboard_audit_company_score_baseline`(
    IN pTimezone VARCHAR(50),
    IN pFromDate VARCHAR(50),
    IN pToDate VARCHAR(50),
    IN pMaxScore INT(11),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pCategoryId BIGINT(12),
    IN pSubCategoryId BIGINT(12),
    IN pFromAge INT(11),
    IN pToAge INT(11)
)
BEGIN
DECLARE whereCond TEXT DEFAULT " AND users.is_blocked = 0";
DECLARE innerWhereCond TEXT DEFAULT "1 = 1";

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", pCompanyId, ")");
    SET innerWhereCond = CONCAT(innerWhereCond , " AND zc_survey_log.company_id IN (", pCompanyId, ")");
END IF; 
IF (pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", pDepartmentId);
END IF;     
IF (pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", pLocationId);
END IF;

IF (pCategoryId IS NOT NULL AND pSubCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId, " AND zc_survey_responses.sub_category_id = ", pSubCategoryId);
ELSEIF (pCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId);
END IF;

IF (pFromAge IS NOT NULL AND pToAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.age BETWEEN ", pFromAge, " AND ", pToAge);
END IF;

SET @q = CONCAT("SELECT
    IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) AS baseLine
FROM zc_survey_responses
INNER JOIN users ON (users.id = zc_survey_responses.user_id)
INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
WHERE zc_survey_responses.survey_log_id = (SELECT id FROM zc_survey_log WHERE ", innerWhereCond, " ORDER BY id ASC LIMIT 1)", whereCond);

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_audit_company_score_line_graph procedure
CREATE PROCEDURE `sp_dashboard_audit_company_score_line_graph`(
    IN pTimezone VARCHAR(50),
    IN pFromDate VARCHAR(50),
    IN pToDate VARCHAR(50),
    IN pMaxScore INT(11),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pCategoryId BIGINT(12),
    IN pSubCategoryId BIGINT(12),
    IN pFromAge INT(11),
    IN pToAge INT(11)
)
BEGIN
DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (pFromDate IS NOT NULL AND pToDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') >= '", pFromDate, "' AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') <= '", pToDate, "'");
END IF;

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", pCompanyId, ")");
END IF; 
IF (pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", pDepartmentId);
END IF;     
IF (pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", pLocationId);
END IF;

IF (pCategoryId IS NOT NULL AND pSubCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId, " AND zc_survey_responses.sub_category_id = ", pSubCategoryId);
ELSEIF (pCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId);
END IF;

IF (pFromAge IS NOT NULL AND pToAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pFromAge, " AND ", pToAge);
END IF;

SET @q = CONCAT("SELECT
    zcs.log_month,
    @tGainScore := @tGainScore + zcs.gain_score AS t_gain_score,
    @tMaxScore := @tMaxScore + zcs.max_score AS t_max_score,
    IFNULL(FORMAT((@tGainScore / @tMaxScore), 2), 0) AS month_percentage
FROM (SELECT @tGainScore := 0, @tMaxScore := 0) AS dummy
CROSS JOIN (
    SELECT
        DATE_FORMAT(CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone, "'), '%Y_%m') AS log_month,
        (SUM(zc_survey_responses.score) * 100) AS gain_score,
        IFNULL(SUM(zc_survey_responses.max_score), 0) AS max_score
    FROM zc_survey_responses
    INNER JOIN users ON (users.id = zc_survey_responses.user_id)
    INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
    INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
    WHERE ", whereCond, "
    GROUP BY log_month
    ORDER BY log_month ASC
) AS zcs");

SET @tGainScore = 0;
SET @tMaxScore = 0;

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_audit_category_wise_tabs procedure
CREATE PROCEDURE `sp_dashboard_audit_category_wise_tabs`(
    IN pMaxScore INT(11),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pCategoryId BIGINT(12),
    IN pTimezone VARCHAR(50),
    IN pFromDate VARCHAR(50),
    IN pToDate VARCHAR(50),
    IN pFromAge INT(11),
    IN pToAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (pFromDate IS NOT NULL AND pToDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') >= '", pFromDate, "' AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') <= '", pToDate, "'");
END IF;

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", pCompanyId, ")");
END IF; 
IF (pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", pDepartmentId);
END IF;     
IF (pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", pLocationId);
END IF;

IF (pCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.category_id = ", pCategoryId);
END IF;

IF (pFromAge IS NOT NULL AND pToAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pFromAge, " AND ", pToAge);
END IF;

SET @q = CONCAT("SELECT
    res.category_id,
    zc_categories.display_name,
    IFNULL(FORMAT(((sum(res.score) * 100) / IFNULL(SUM(res.max_score), 0)), 2), 0) as category_percentage
FROM(
SELECT
    zc_survey_responses.id,
    zc_survey_responses.category_id,
    zc_survey_responses.score,
    zc_survey_responses.max_score
FROM zc_survey_responses
INNER JOIN users ON (users.id = zc_survey_responses.user_id)
INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
WHERE ", whereCond ,"
) AS res
INNER JOIN zc_categories ON (zc_categories.id = res.category_id)
GROUP BY res.category_id
ORDER BY res.category_id ASC");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_audit_category_wise_graph procedure
CREATE PROCEDURE `sp_dashboard_audit_category_wise_graph`(
    IN pMaxScore INT(11),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pCategoryId BIGINT(12),
    IN pTimezone VARCHAR(50),
    IN pFromDate VARCHAR(50),
    IN pToDate VARCHAR(50),
    IN pFromAge INT(11),
    IN pToAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND zc_survey_responses.max_score IS NOT NULL";

IF (pFromDate IS NOT NULL AND pToDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') >= '", pFromDate, "' AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') <= '", pToDate, "'");
END IF;

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", pCompanyId, ")");
END IF; 
IF (pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", pDepartmentId);
END IF;     
IF (pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", pLocationId);
END IF;

IF (pCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.category_id = ", pCategoryId);
END IF;

IF (pFromAge IS NOT NULL AND pToAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pFromAge, " AND ", pToAge);
END IF;

SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));

SET @q = CONCAT("SELECT
    res.category_id,
    zc_categories.display_name,
    IFNULL(FORMAT((sum(res.total_sum) / sum(res.total_qs)), 2), 0) as category_percentage,
    IFNULL(FORMAT(((SUM(res.status = 'low') * 100) / count(res.id)), 2), 0) AS low,
    IFNULL(FORMAT(((SUM(res.status = 'moderate') * 100) / count(res.id)), 2), 0) AS moderate,
    IFNULL(FORMAT(((SUM(res.status = 'high') * 100) / count(res.id)), 2), 0) AS high
FROM(
SELECT
    zc_survey_responses.id,
    zc_survey_responses.category_id,
    (SUM(zc_survey_responses.score) * 100) AS total_sum,
    SUM(zc_survey_responses.max_score) AS total_qs,
    IF((IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) BETWEEN 0 AND 60), 'low', IF((IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) BETWEEN 60 AND 80), 'moderate', IF((IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) BETWEEN 80 AND 100), 'high', 'low'))) AS status
FROM zc_survey_responses
INNER JOIN users ON (users.id = zc_survey_responses.user_id)
INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
WHERE ", whereCond ,"
GROUP BY zc_survey_responses.user_id
) AS res
INNER JOIN zc_categories ON (zc_categories.id = res.category_id)
GROUP BY res.category_id
ORDER BY res.category_id ASC");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_audit_sub_category_wise_graph procedure
CREATE PROCEDURE `sp_dashboard_audit_sub_category_wise_graph`(
    IN pMaxScore INT(11),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pCategoryId BIGINT(12),
    IN pSubCategoryId BIGINT(12),
    IN pTimezone VARCHAR(50),
    IN pFromDate VARCHAR(50),
    IN pToDate VARCHAR(50),
    IN pFromAge INT(11),
    IN pToAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (pFromDate IS NOT NULL AND pToDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') >= '", pFromDate, "' AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '", pTimezone ,"') <= '", pToDate, "'");
END IF;

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.company_id IN (", pCompanyId, ")");
END IF; 
IF (pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND zc_survey_responses.department_id = ", pDepartmentId);
END IF;     
IF (pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", pLocationId);
END IF;

IF (pCategoryId IS NOT NULL AND pSubCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId, " AND zc_survey_responses.sub_category_id = ", pSubCategoryId);
ELSEIF (pCategoryId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND zc_survey_responses.category_id = ", pCategoryId);
END IF;

IF (pFromAge IS NOT NULL AND pToAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pFromAge, " AND ", pToAge);
END IF;

SET @q = CONCAT("SELECT
    zc_survey_responses.sub_category_id,
    zc_sub_categories.display_name AS subcategory_name,
    IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 2), 0) AS percentage
FROM zc_survey_responses
INNER JOIN users ON (users.id = zc_survey_responses.user_id)
INNER JOIN user_profile ON (user_profile.user_id = zc_survey_responses.user_id)
INNER JOIN company_locations ON (company_locations.company_id = zc_survey_responses.company_id)
INNER JOIN zc_sub_categories on (zc_sub_categories.id = zc_survey_responses.sub_category_id)
WHERE ", whereCond ,"
GROUP BY zc_survey_responses.sub_category_id
ORDER BY zc_survey_responses.sub_category_id ASC");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_booking_tab procedure
CREATE PROCEDURE `sp_dashboard_booking_tab`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN type VARCHAR(60),
    IN roletype VARCHAR(121),
    IN lcompanyId BIGINT(12)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "event_booking_logs.status = '4'";
DECLARE whereJoinCond TEXT DEFAULT "";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND event_companies.company_id IN (", companyId ,")");
    SET whereJoinCond = CONCAT(" AND event_booking_logs.company_id IN (", companyId ,") ");
END IF;

IF (roletype = 'zevo') THEN
    SET whereCond = CONCAT(whereCond, " AND events.company_id IS NULL");
ELSEIF (roletype = 'reseller') THEN
    SET whereCond = CONCAT(whereCond, " AND (events.company_id IS NULL OR events.company_id = ", lcompanyId ,")");
END IF;

IF (fromDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND DATE(CONVERT_TZ(CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time), 'UTC', '", timezone ,"')) BETWEEN '",toDate,"' AND '", fromDate ,"'");
ELSE
   SET whereCond = CONCAT(whereCond , " AND DATE(CONVERT_TZ(CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time), 'UTC', '", timezone ,"')) >= '", toDate ,"'");
END IF;

SET @q = CONCAT("SELECT COUNT( DISTINCT event_booking_logs.id) AS upcomming_event
FROM event_booking_logs
INNER JOIN events ON events.id = event_booking_logs.event_id
INNER JOIN event_companies ON event_companies.event_id = events.id ",whereJoinCond,"
WHERE ", whereCond, ";");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_booking_tab_events_revenue procedure
CREATE PROCEDURE `sp_dashboard_booking_tab_events_revenue`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN roletype VARCHAR(121),
    IN lcompanyId BIGINT(12)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("( TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN '", fromDate ,"' AND '",toDate,"' OR TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN '", fromDate ,"' AND '",toDate,"' )");
DECLARE whereCondBooked TEXT DEFAULT CONCAT("TIMESTAMP(event_booking_logs.created_at) BETWEEN '", fromDate ,"' AND '",toDate,"'");
DECLARE whereCondCancelled TEXT DEFAULT CONCAT("TIMESTAMP(event_booking_logs.updated_at) BETWEEN '", fromDate ,"' AND '",toDate,"'");

DECLARE whereJoinCond TEXT DEFAULT "";

SET @countOfBooked = 0;
SET @countOfCompleted = 0;
SET @sumOfCompleted = 0;
SET @countOfCancelled = 0;
SET @sumOfBooked = 0;
SET @sumOfCancelled = 0;

IF (companyId IS NOT NULL) THEN
    SET whereJoinCond = CONCAT(" AND event_booking_logs.company_id IN (", companyId ,") ");
END IF;

IF (roletype = 'zevo') THEN
    SET whereCond = CONCAT(whereCond, " AND events.company_id IS NULL");
    SET whereCondBooked = CONCAT(whereCondBooked, " AND events.company_id IS NULL");
    SET whereCondCancelled = CONCAT(whereCondCancelled, " AND events.company_id IS NULL");
ELSEIF (roletype = 'reseller') THEN
    SET whereCond = CONCAT(whereCond, " AND (events.company_id IS NULL OR events.company_id = ", lcompanyId ,")");
    SET whereCondBooked = CONCAT(whereCondBooked, " AND (events.company_id IS NULL OR events.company_id = ", lcompanyId ,")");
    SET whereCondCancelled = CONCAT(whereCondCancelled, " AND (events.company_id IS NULL OR events.company_id = ", lcompanyId ,")");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id) INTO @countOfBooked FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCondBooked, " AND event_booking_logs.status = '4';");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id)  INTO @countOfCompleted FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCond, " AND event_booking_logs.status = '5';");

SET @q2 = CONCAT("SELECT SUM(temp.fees) INTO @sumOfCompleted FROM (SELECT IFNULL(SUM(DISTINCT events.fees),0) AS fees FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCond, " AND event_booking_logs.status = '5' GROUP BY event_booking_logs.id) AS temp;");

SET @q3 = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id)  INTO @countOfCancelled FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCondCancelled, " AND event_booking_logs.status = '3';");

SET @q4 = CONCAT("SELECT SUM(temp.fees) INTO @sumOfBooked FROM (SELECT IFNULL(SUM(DISTINCT events.fees),0) AS fees FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCondBooked, " AND event_booking_logs.status = '4' GROUP BY event_booking_logs.id) AS temp;");

SET @q5 = CONCAT("SELECT SUM(temp.fees) INTO @sumOfCancelled FROM (SELECT IFNULL(SUM(DISTINCT events.fees),0) AS fees FROM `event_booking_logs` INNER JOIN events ON events.id = event_booking_logs.event_id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id  WHERE ", whereCondCancelled, " AND event_booking_logs.status = '3' GROUP BY event_booking_logs.id) AS temp;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @q3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

PREPARE stmt4 FROM @q4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

PREPARE stmt5 FROM @q5;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

SELECT ROUND(IFNULL(@countOfBooked,0)) AS countOfBooked, ROUND(IFNULL(@countOfCompleted,0)) AS countOfCompleted, ROUND(IFNULL(@sumOfCompleted,0)) AS sumOfCompleted, ROUND(IFNULL(@countOfCancelled,0)) AS countOfCancelled, ROUND(IFNULL(@sumOfBooked,0)) AS sumOfBooked, ROUND(IFNULL(@sumOfCancelled,0)) AS sumOfCancelled;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @q3 = NULL;
SET @q4 = NULL;
SET @q5 = NULL;
SET @countOfBooked = NULL;
SET @countOfCompleted = NULL;
SET @sumOfCompleted = NULL;
SET @countOfCancelled = NULL;
SET @sumOfBooked = NULL;
SET @sumOfCancelled = NULL;

END;

#Code for creating sp_dashboard_booking_tab_today_event_calendar procedure
CREATE PROCEDURE `sp_dashboard_booking_tab_today_event_calendar`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN roletype VARCHAR(121),
    IN lcompanyId BIGINT(12),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("event_booking_logs.status = '4' AND TIMESTAMP(DATE(CONVERT_TZ(CONCAT(event_booking_logs.booking_date,' ',event_booking_logs.start_time), 'UTC', '", timezone, "'))) BETWEEN '", fromDate ,"' AND '",toDate,"'");
DECLARE whereJoinCond TEXT DEFAULT "";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND event_companies.company_id IN (", companyId ,")");
    SET whereJoinCond = CONCAT(" AND event_booking_logs.company_id IN (", companyId ,") ");
END IF;

IF (roletype = 'zevo') THEN
    SET whereCond = CONCAT(whereCond, " AND events.company_id IS NULL");
ELSEIF (roletype = 'reseller') THEN
    SET whereCond = CONCAT(whereCond, " AND (events.company_id IS NULL OR events.company_id = ", lcompanyId ,")");
END IF;

SET @q = CONCAT("SELECT events.id, events.name, events.fees, event_booking_logs.booking_date, event_booking_logs.start_time, (SELECT count(event_registered_users_logs.id) FROM event_registered_users_logs WHERE event_registered_users_logs.event_booking_log_id = event_booking_logs.id AND event_registered_users_logs.is_cancelled = 0) AS participants_users, ( SELECT name from companies WHERE id = event_booking_logs.company_id ) AS company_name FROM `events` INNER JOIN event_booking_logs ON event_booking_logs.event_id = events.id ",whereJoinCond," INNER JOIN event_companies ON event_companies.event_id = events.id WHERE ", whereCond, " GROUP BY event_booking_logs.id ORDER BY TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) ASC, events.created_at DESC LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_app_usage_popular_webinar procedure
CREATE PROCEDURE `sp_dashboard_app_usage_popular_webinar`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)");

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    sub_categories.name as webinarCategory,
    SUM(webinar_user.view_count) as totalViews
    FROM webinar
    INNER JOIN webinar_user ON (webinar_user.webinar_id = webinar.id) AND CONVERT_TZ(webinar_user.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "'
    INNER JOIN sub_categories ON (webinar.sub_category_id = sub_categories.id)
    INNER JOIN users ON (webinar_user.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY sub_categories.name;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_app_usage_popular_masterclass procedure
CREATE PROCEDURE `sp_dashboard_app_usage_popular_masterclass`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)");

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    sub_categories.name as masterclassCategory,
    SUM(user_course.joined) as totalEnrollment
    FROM courses
    INNER JOIN user_course ON (user_course.course_id = courses.id) AND CONVERT_TZ(user_course.created_at, 'UTC', '", timezone ,"') >= '", fromDate, "'
    INNER JOIN sub_categories ON (courses.sub_category_id = sub_categories.id)
    INNER JOIN users ON (user_course.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY sub_categories.name;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;
END;

#Code for creating sp_dashboard_usage_top_webinar procedure
CREATE PROCEDURE `sp_dashboard_usage_top_webinar`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    webinar.title,
    SUM(webinar_user.view_count) AS totalViews
    FROM webinar
    INNER JOIN webinar_user ON (webinar_user.webinar_id = webinar.id)
    INNER JOIN users ON (webinar_user.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(webinar_user.created_at, 'UTC', '", timezone ,"') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY webinar.title
    HAVING totalViews > 0
    LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_app_usage_top_masterclass procedure
CREATE PROCEDURE `sp_dashboard_app_usage_top_masterclass`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

IF (fromDate IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND CONVERT_TZ(user_course.created_at, 'UTC', '", timezone ,"') BETWEEN '", fromDate, "' AND '" , toDate, "'");
END IF;

SET @q = CONCAT("SELECT
    courses.title,
    SUM(user_course.joined) AS totalEnrollment
    FROM courses
    INNER JOIN user_course ON (user_course.course_id = courses.id)
    INNER JOIN users ON (user_course.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    GROUP BY courses.title
    HAVING totalEnrollment > 0
    LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_app_usage_top_feeds procedure
CREATE PROCEDURE `sp_dashboard_app_usage_top_feeds`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q = CONCAT("SELECT
    feeds.title,
    SUM(feed_user.view_count) AS totalViews
    FROM feeds
    INNER JOIN feed_user ON (feed_user.feed_id = feeds.id)
    INNER JOIN users ON (feed_user.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(feed_user.created_at, 'UTC', '", timezone ,"') BETWEEN '" , fromDate , "' AND '" , toDate , "'
    GROUP BY title
    HAVING totalViews > 0
    LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_eap_activity_tab_session_count procedure
CREATE PROCEDURE `sp_dashboard_eap_activity_tab_session_count`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN counsellorId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

SET @todaySession = 0;
SET @upcomingSession = 0;
SET @completedSession = 0;
SET @cancelledSession = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (counsellorId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " eap_calendly.therapist_id = ", counsellorId ," AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT eap_calendly.id) INTO @todaySession FROM `eap_calendly` INNER JOIN user_team ON user_team.user_id = eap_calendly.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " CONVERT_TZ(eap_calendly.start_time, 'UTC', '", timezone, "') >= '" , fromDate , "' AND CONVERT_TZ(eap_calendly.end_time, 'UTC', '", timezone, "') <= '" , toDate , "';");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT eap_calendly.id) INTO @upcomingSession FROM `eap_calendly` INNER JOIN user_team ON user_team.user_id = eap_calendly.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " eap_calendly.start_time >= DATE(NOW()) and eap_calendly.status != 'rescheduled' and eap_calendly.status != 'canceled';");

SET @q2 = CONCAT("SELECT COUNT(DISTINCT eap_calendly.id) INTO @completedSession FROM `eap_calendly` INNER JOIN user_team ON user_team.user_id = eap_calendly.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " eap_calendly.end_time <= DATE(NOW()) and eap_calendly.status != 'rescheduled' and eap_calendly.status != 'canceled';");

SET @q3 = CONCAT("SELECT COUNT(DISTINCT eap_calendly.id) INTO @cancelledSession FROM `eap_calendly` INNER JOIN user_team ON user_team.user_id = eap_calendly.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " (eap_calendly.status = 'canceled' OR eap_calendly.status = 'rescheduled') AND eap_calendly.cancelled_at IS NOT NULL;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @q3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT ROUND(IFNULL(@todaySession,0)) AS todaySession, ROUND(IFNULL(@upcomingSession,0)) AS upcomingSession, ROUND(IFNULL(@completedSession,0)) AS completedSession, ROUND(IFNULL(@cancelledSession,0)) AS cancelledSession;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @q3 = NULL;
SET @todaySession = NULL;
SET @upcomingSession = NULL;
SET @completedSession = NULL;
SET @cancelledSession = NULL;

END;

#Code for creating sp_dashboard_eap_activity_tab_counsellors_count procedure
CREATE PROCEDURE `sp_dashboard_eap_activity_tab_counsellors_count`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

SET @totalCounsellors = 0;
SET @activeCounsellors = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @totalCounsellors FROM users LEFT JOIN user_team ON user_team.user_id = users.id LEFT JOIN role_user on role_user.user_id = users.id LEFT JOIN roles on roles.id=role_user.role_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " users.id != 1 AND roles.slug = 'counsellor';");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @activeCounsellors FROM users LEFT JOIN user_team ON user_team.user_id = users.id LEFT JOIN role_user on role_user.user_id = users.id LEFT JOIN roles on roles.id=role_user.role_id LEFT JOIN eap_calendly ON eap_calendly.therapist_id = users.id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " users.id != 1 AND roles.slug = 'counsellor' AND CONVERT_TZ(eap_calendly.created_at, 'UTC', '", timezone, "') >= '" , toDate , "';");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SELECT ROUND(IFNULL(@totalCounsellors,0)) AS totalCounsellors, ROUND(IFNULL(@activeCounsellors,0)) AS activeCounsellors;

SET @q = NULL;
SET @q1 = NULL;
SET @totalCounsellors = NULL;
SET @activeCounsellors = NULL;

END;

#Code for creating sp_dashboard_eap_activity_tab_utilization procedure
CREATE PROCEDURE `sp_dashboard_eap_activity_tab_utilization`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

SET @userUseService = 0;
SET @numberOfUsers = 0;
SET @assignToCounsellors = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT eap_tickets.id) INTO @userUseService FROM eap_tickets LEFT JOIN user_team ON user_team.user_id = eap_tickets.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " eap_tickets.user_id IS NOT NULL;");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @numberOfUsers FROM users LEFT JOIN user_team ON user_team.user_id = users.id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " users.can_access_app = 1 AND users.is_blocked = 0;");

SET @q2 = CONCAT("SELECT COUNT(DISTINCT eap_tickets.id) INTO @assignToCounsellors FROM eap_tickets LEFT JOIN user_team ON user_team.user_id = eap_tickets.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " eap_tickets.therapist_id IS NOT NULL;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT ROUND(IFNULL(@userUseService,0)) AS userUseService, ROUND(IFNULL(@numberOfUsers,0)) AS numberOfUsers, ROUND(IFNULL(@assignToCounsellors,0)) AS assignToCounsellors;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @userUseService = NULL;
SET @numberOfUsers = NULL;
SET @assignToCounsellors = NULL;

END;

#Code for creating sp_dashboard_eap_activity_tab_skill_trend procedure
CREATE PROCEDURE `sp_dashboard_eap_activity_tab_skill_trend`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN counsellorId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (counsellorId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " counsellor_skills.user_id = ", counsellorId ," AND ");
END IF;

SET @q = CONCAT("SELECT sub_categories.name as categoriesSkill, count(counsellor_skills.id) AS totalAssignUser FROM sub_categories LEFT JOIN counsellor_skills ON counsellor_skills.skill_id = sub_categories.id LEFT JOIN user_team ON user_team.user_id = counsellor_skills.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " sub_categories.category_id = 8 GROUP BY sub_categories.id LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_eap_activity_tab_appointment procedure
CREATE PROCEDURE `sp_dashboard_eap_activity_tab_appointment`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN counsellorId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (counsellorId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " eap_calendly.therapist_id = ", counsellorId ," AND ");
END IF;

SET @q = CONCAT("SELECT cast(eap_calendly.start_time as Date) as daydate, COUNT(eap_calendly.id) as sessionCount FROM eap_calendly LEFT JOIN user_team ON user_team.user_id = eap_calendly.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " (CONVERT_TZ(eap_calendly.start_time, 'UTC', '", timezone, "') >= '" , fromDate , "' OR CONVERT_TZ(eap_calendly.start_time, 'UTC', '", timezone, "') <= '" , toDate , "' ) and (CONVERT_TZ(eap_calendly.end_time, 'UTC', '", timezone, "') >= '" , fromDate , "' OR CONVERT_TZ(eap_calendly.end_time, 'UTC', '", timezone, "') <= '" , toDate , "') GROUP BY cast(eap_calendly.start_time as Date);");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;


#Code for creating sp_dashboard_physical_popular_exercises_tracker procedure
CREATE PROCEDURE `sp_dashboard_physical_popular_exercises_tracker`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN isManual INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

SET @totalExerciseUsers = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;


SET @q1 = CONCAT("SELECT
    COUNT(DISTINCT(user_exercise.user_id)) INTO @totalExerciseUsers
    FROM user_exercise
    INNER JOIN users on (user_exercise.user_id = users.id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, "
    AND CONVERT_TZ(user_exercise.start_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    ue.exerciseRange,
    IF(@totalExerciseUsers > 0, ROUND((COUNT(ue.user_id) * 100) / @totalExerciseUsers, 1), 0) as percent
    FROM(
        SELECT
            user_exercise.user_id,
            CASE
                WHEN (COUNT(user_exercise.user_id)) < 10  THEN 'Least Popular'
                WHEN (COUNT(user_exercise.user_id)) BETWEEN 10 AND 30  THEN 'Moderate'
                WHEN (COUNT(user_exercise.user_id)) > 30 THEN 'Most Popular'
            END as exerciseRange
        FROM user_exercise
        INNER JOIN users on (user_exercise.user_id = users.id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND user_exercise.is_manual = ", isManual ,"
        AND CONVERT_TZ(user_exercise.start_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
        GROUP BY user_exercise.user_id) as ue
    GROUP BY ue.exerciseRange
    ORDER BY
        CASE WHEN exerciseRange = 'Least Popular' THEN '1'
            WHEN exerciseRange = 'Moderate' THEN '2'
            WHEN exerciseRange = 'Most Popular' THEN '3'
            ELSE exerciseRange
            END ASC;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1 = NULL;
SET @q2 = NULL;

END;

#Code for creating sp_dashboard_physical_most_popular_exercises procedure
CREATE PROCEDURE `sp_dashboard_physical_most_popular_exercises`(
    IN timezone VARCHAR(50),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN days INT(11),
    IN companyId BIGINT(12),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN isManual INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0 AND (users.can_access_app=1 OR users.can_access_portal=1)";

SET @totalExercises = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.company_id IN (", companyId, ")");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_team.department_id = ", departmentId);
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND company_locations.id = ", locationId);
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", fromAge, " AND ", toAge);
END IF;

SET @q1 = CONCAT("SELECT
    COUNT(user_exercise.exercise_id) INTO @totalExercises
    FROM user_exercise
    INNER JOIN users ON (users.id = user_exercise.user_id)
    INNER JOIN user_team ON (user_team.user_id = users.id)
    INNER JOIN user_profile ON (user_profile.user_id = users.id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
    WHERE ", whereCond, ";");

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q2 = CONCAT("SELECT
    exercises.title,
    IFNULL((
        SELECT
            IF(@totalExercises > 0, ROUND(COUNT(user_exercise.exercise_id) * 100 / @totalExercises,1), 0)
        FROM user_exercise
        INNER JOIN users ON (users.id = user_exercise.user_id)
        INNER JOIN user_team ON (user_team.user_id = users.id)
        INNER JOIN user_profile ON (user_profile.user_id = users.id)
        INNER JOIN departments ON (departments.id = user_team.department_id)
        INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
        INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
        WHERE ", whereCond, "
        AND user_exercise.is_manual = ", isManual ,"
        AND user_exercise.exercise_id = exercises.id
        AND CONVERT_TZ(user_exercise.start_date, 'UTC', '", timezone ,"') BETWEEN '", fromDate ,"' AND '", toDate ,"'
        GROUP BY user_exercise.exercise_id
    ),0) as percent
    FROM exercises
    ORDER BY percent DESC
    LIMIT 8;");

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @q1= NULL;
SET @q2 = NULL;

END;

CREATE PROCEDURE `sp_calculate_user_step_avg`(
    IN logDate VARCHAR(50)
)
BEGIN

DECLARE userList_Id bigint(11);
DECLARE todo_position INT DEFAULT 0;
DECLARE stepsCount bigint(11) DEFAULT 0;

DECLARE userList cursor for (SELECT user_id FROM user_step GROUP BY user_id);

SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));

SELECT count(temp.id) INTO stepsCount FROM (SELECT id FROM user_step WHERE log_date <= logDate GROUP BY user_id ORDER BY log_date DESC) as temp;

SET todo_position = 1;

OPEN userList;

userList_loop:
LOOP

FETCH userList INTO userList_Id;

insert into users_steps_authenticator_avg(user_id,steps_total,steps_avg,days,log_date) SELECT t.user_id, SUM(t.steps) AS total_steps, AVG(t.steps) AS avg_steps, COUNT(t.steps) AS total_count, logDate AS log_date FROM ( SELECT steps, user_id FROM user_step WHERE user_id = userList_Id AND log_date <= logDate ORDER BY `user_step`.`log_date` DESC LIMIT 15 ) AS t;

IF todo_position >= stepsCount THEN
    LEAVE userList_loop;
END IF;

SET todo_position = todo_position + 1;

END LOOP;

CLOSE userList;

END;

#Code for creating sp_dashboard_digital_therapy_tab_wellbeing_specialist_count procedure
CREATE PROCEDURE `sp_dashboard_digital_therapy_tab_wellbeing_specialist_count`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN wellbeingSpecialistId INT(11),
    IN serviceId  VARCHAR(121)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";
DECLARE whereCond2 TEXT DEFAULT "";

SET @totalWellbeingSpecialists = 0;
SET @activeWellbeingSpecialists = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("cronofy_schedule.company_id IN (", companyId ,") AND ");
END IF;

#IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
#    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
#END IF;

IF (serviceId IS NOT NULL) THEN
    SET whereCond2 = CONCAT(whereCond2 , " cronofy_schedule.service_id IN (", serviceId, ") AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @totalWellbeingSpecialists FROM users JOIN ws_user ON ws_user.user_id = users.id LEFT JOIN role_user on role_user.user_id = users.id LEFT JOIN roles on roles.id=role_user.role_id LEFT JOIN cronofy_schedule ON cronofy_schedule.ws_id = users.id WHERE ", whereCond, " ", whereCond2, " users.id != 1 AND roles.slug = 'wellbeing_specialist';");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @activeWellbeingSpecialists FROM users JOIN ws_user ON ws_user.user_id = users.id LEFT JOIN role_user on role_user.user_id = users.id LEFT JOIN roles on roles.id=role_user.role_id LEFT JOIN cronofy_schedule ON cronofy_schedule.ws_id = users.id WHERE ", whereCond, " ", whereCond2, " users.id != 1 AND roles.slug = 'wellbeing_specialist' AND CONVERT_TZ(cronofy_schedule.created_at, 'UTC', '", timezone, "') >= '" , toDate , "';");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SELECT ROUND(IFNULL(@totalWellbeingSpecialists,0)) AS totalWellbeingSpecialists, ROUND(IFNULL(@activeWellbeingSpecialists,0)) AS activeWellbeingSpecialists;

SET @q = NULL;
SET @q1 = NULL;
SET @totalWellbeingSpecialists = NULL;
SET @activeWellbeingSpecialists = NULL;

END;

#Code for creating sp_dashboard_digital_therapy_tab_session_count procedure
CREATE PROCEDURE `sp_dashboard_digital_therapy_tab_session_count`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN wellbeingSpecialistId INT(11),
    IN serviceId VARCHAR(121)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

SET @todaySession = 0;
SET @upcomingSession = 0;
SET @completedSession = 0;
SET @cancelledSession = 0;

#IF (companyId IS NOT NULL) THEN
#    SET whereCond = CONCAT(" cronofy_schedule.company_id IN (", companyId ,") AND ");
#END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.company_id IN (", companyId, ") AND ");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_team.department_id = ", departmentId, " AND ");
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " company_locations.id = ", locationId, " AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND");
END IF;

IF (wellbeingSpecialistId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.ws_id = ", wellbeingSpecialistId ," AND ");
END IF;

IF (serviceId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.service_id IN (", serviceId, ") AND ");
END IF;

#SET @q = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @todaySession FROM `cronofy_schedule` LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " CONVERT_TZ(cronofy_schedule.start_time, 'UTC', '", timezone, "') >= '" , fromDate , "' AND CONVERT_TZ(cronofy_schedule.end_time, 'UTC', '", timezone, "') <= '" , toDate , "';");

#SET @q1 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @upcomingSession FROM `cronofy_schedule` LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " cronofy_schedule.start_time >= NOW() and cronofy_schedule.status != 'rescheduled' and cronofy_schedule.status != 'canceled' and cronofy_schedule.status != 'open';");

#SET @q2 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @completedSession FROM `cronofy_schedule` LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " cronofy_schedule.end_time <= NOW() and cronofy_schedule.status != 'rescheduled' and cronofy_schedule.status != 'canceled' and cronofy_schedule.status != 'open' and cronofy_schedule.no_show = 'No';");

#SET @q3 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @cancelledSession FROM `cronofy_schedule` LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " (cronofy_schedule.status = 'canceled' OR cronofy_schedule.status = 'rescheduled') and cronofy_schedule.status != 'open';");

SET @q = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @todaySession FROM `cronofy_schedule` LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id JOIN users ON users.id = session_group_users.user_id JOIN users as ws ON ws.id = cronofy_schedule.ws_id JOIN user_team ON user_team.user_id = users.id JOIN user_profile ON user_profile.user_id = users.id LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " users.deleted_at IS NULL AND CONVERT_TZ(cronofy_schedule.start_time, 'UTC', '", timezone, "') >= '" , fromDate , "' AND CONVERT_TZ(cronofy_schedule.end_time, 'UTC', '", timezone, "') <= '" , toDate , "' AND cronofy_schedule.status = 'booked' AND cronofy_schedule.no_show = 'No' AND ws.deleted_at IS NULL;");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @upcomingSession FROM `cronofy_schedule` LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id JOIN users ON users.id = session_group_users.user_id JOIN users as ws ON ws.id = cronofy_schedule.ws_id JOIN user_team ON user_team.user_id = users.id JOIN user_profile ON user_profile.user_id = users.id LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " users.deleted_at IS NULL AND cronofy_schedule.start_time >= UTC_TIMESTAMP() and cronofy_schedule.status != 'rescheduled' and cronofy_schedule.status != 'canceled' and cronofy_schedule.status != 'short_canceled' and cronofy_schedule.start_time != '0000-00-00 00:00:00' and cronofy_schedule.status != 'open' AND ws.deleted_at IS NULL;");

SET @q2 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @completedSession FROM `cronofy_schedule` LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id JOIN users ON users.id = session_group_users.user_id JOIN users as ws ON ws.id = cronofy_schedule.ws_id JOIN user_team ON user_team.user_id = users.id JOIN user_profile ON user_profile.user_id = users.id LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " users.deleted_at IS NULL AND (cronofy_schedule.end_time <= UTC_TIMESTAMP() OR cronofy_schedule.status = 'completed') AND cronofy_schedule.status NOT IN ( 'canceled', 'rescheduled', 'open', 'short_canceled') and cronofy_schedule.start_time != '0000-00-00 00:00:00' and cronofy_schedule.no_show = 'No' AND ws.deleted_at IS NULL;");

SET @q3 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @cancelledSession FROM `cronofy_schedule` LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id JOIN users ON users.id = session_group_users.user_id JOIN users as ws ON ws.id = cronofy_schedule.ws_id JOIN user_team ON user_team.user_id = users.id JOIN user_profile ON user_profile.user_id = users.id LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " users.deleted_at IS NULL AND cronofy_schedule.cancelled_at IS NOT NULL AND (cronofy_schedule.status = 'canceled' OR cronofy_schedule.status = 'rescheduled' OR cronofy_schedule.status = 'short_canceled') and cronofy_schedule.start_time != '0000-00-00 00:00:00' and cronofy_schedule.status != 'open' AND ws.deleted_at IS NULL;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @q3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT ROUND(IFNULL(@todaySession,0)) AS todaySession, ROUND(IFNULL(@upcomingSession,0)) AS upcomingSession, ROUND(IFNULL(@completedSession,0)) AS completedSession, ROUND(IFNULL(@cancelledSession,0)) AS cancelledSession;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @q3 = NULL;
SET @todaySession = NULL;
SET @upcomingSession = NULL;
SET @completedSession = NULL;
SET @cancelledSession = NULL;

END;

#Code for creating sp_dashboard_digital_therapy_tab_appointment procedure
CREATE PROCEDURE `sp_dashboard_digital_therapy_tab_appointment`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN wellbeingSpecialistId INT(11),
    IN serviceId VARCHAR(121)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

#IF (companyId IS NOT NULL) THEN
#    SET whereCond = CONCAT(" cronofy_schedule.company_id IN (", companyId ,") AND ");
#END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.company_id IN (", companyId, ") AND ");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_team.department_id = ", departmentId, " AND ");
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " company_locations.id = ", locationId, " AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (wellbeingSpecialistId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.ws_id = ", wellbeingSpecialistId ," AND ");
END IF;

IF (serviceId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.service_id IN (", serviceId, ") AND ");
END IF;

SET @q = CONCAT("SELECT DATE_FORMAT(cronofy_schedule.start_time,'%a') as day, COUNT(DISTINCT cronofy_schedule.id) as sessionCount FROM cronofy_schedule LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id JOIN users ON session_group_users.user_id = users.id JOIN users as ws ON ws.id = cronofy_schedule.ws_id JOIN user_team ON user_team.user_id = users.id JOIN user_profile ON user_profile.user_id = users.id LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " users.deleted_at IS NULL AND ws.deleted_at IS NULL AND (cronofy_schedule.end_time <= UTC_TIMESTAMP() OR cronofy_schedule.status = 'completed') AND cronofy_schedule.status NOT IN ( 'canceled', 'rescheduled', 'open', 'short_canceled') and cronofy_schedule.start_time != '0000-00-00 00:00:00' and cronofy_schedule.no_show = 'No' GROUP BY DATE_FORMAT(cronofy_schedule.start_time,'%a');");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_digital_therapy_tab_skill_trend procedure
CREATE PROCEDURE `sp_dashboard_digital_therapy_tab_skill_trend`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN departmentId BIGINT(12),
    IN locationId BIGINT(12),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN wellbeingSpecialistId INT(11),
    IN serviceId  VARCHAR(121)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

#IF (companyId IS NOT NULL) THEN
#    SET whereCond = CONCAT("cronofy_schedule.company_id IN (", companyId ,") AND ");
#END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.company_id IN (", companyId, ") AND ");
END IF; 
IF (departmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_team.department_id = ", departmentId, " AND ");
END IF;     
IF (locationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " company_locations.id = ", locationId, " AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (wellbeingSpecialistId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.ws_id = ", wellbeingSpecialistId ," AND ");
END IF;

IF (serviceId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " cronofy_schedule.service_id IN (", serviceId, ") AND ");
END IF;

#SET @q = CONCAT("SELECT service_sub_categories.name as categoriesSkill, count(users_services.id) AS totalAssignUser FROM service_sub_categories LEFT JOIN users_services ON users_services.service_id = service_sub_categories.id LEFT JOIN user_team ON user_team.user_id = users_services.user_id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond, " service_sub_categories.id IS NOT NULL GROUP BY service_sub_categories.id LIMIT 10;");

SET @q = CONCAT("SELECT service_sub_categories.name as categoriesSkill, COUNT(DISTINCT cronofy_schedule.id) AS totalAssignUser FROM service_sub_categories LEFT JOIN cronofy_schedule ON cronofy_schedule.topic_id = service_sub_categories.id LEFT JOIN session_group_users ON session_group_users.session_id = cronofy_schedule.id 
JOIN users ON users.id = session_group_users.user_id 
JOIN users as ws ON ws.id = cronofy_schedule.ws_id 
JOIN user_team ON user_team.user_id = users.id 
JOIN user_profile ON user_profile.user_id = users.id
LEFT JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
LEFT JOIN company_locations ON (company_locations.id = team_location.company_location_id) WHERE ", whereCond, " cronofy_schedule.end_time <= UTC_TIMESTAMP() AND cronofy_schedule.status != 'rescheduled' AND cronofy_schedule.status != 'canceled' AND cronofy_schedule.status != 'open' AND cronofy_schedule.status != 'short_canceled' AND cronofy_schedule.no_show = 'No' AND users.deleted_at IS NULL AND ws.deleted_at IS NULL AND cronofy_schedule.start_time != '0000-00-00 00:00:00' GROUP BY cronofy_schedule.topic_id LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

#Code for creating sp_dashboard_digital_therapy_tab_utilization procedure
CREATE PROCEDURE `sp_dashboard_digital_therapy_tab_utilization`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN fromAge INT(11),
    IN toAge INT(11),
    IN wellbeingSpecialistId INT(11),
    IN serviceId VARCHAR(121)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";
DECLARE whereCond1 TEXT DEFAULT "";
DECLARE whereCond2 TEXT DEFAULT "";

SET @userUseService = 0;
SET @numberOfUsers = 0;
SET @assignToCounsellors = 0;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT("cronofy_schedule.company_id IN (", companyId ,") AND ");
    SET whereCond1 = CONCAT("user_team.company_id IN (", companyId ,") AND ");
END IF;

IF (fromAge IS NOT NULL AND toAge IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " user_profile.age BETWEEN ", fromAge, " AND ", toAge, " AND ");
END IF;

IF (serviceId IS NOT NULL) THEN
    SET whereCond2 = CONCAT(whereCond2 , " cronofy_schedule.service_id IN (", serviceId, ") AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @userUseService FROM cronofy_schedule LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " ", whereCond2, " cronofy_schedule.company_id IS NOT NULL AND cronofy_schedule.status != 'open' ;");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT users.id) INTO @numberOfUsers FROM users LEFT JOIN user_team ON user_team.user_id = users.id LEFT JOIN user_profile ON user_profile.user_id = user_team.user_id WHERE ", whereCond1, " users.can_access_app = 1 AND users.is_blocked = 0;");

SET @q2 = CONCAT("SELECT COUNT(DISTINCT cronofy_schedule.id) INTO @assignToCounsellors FROM cronofy_schedule LEFT JOIN user_team ON user_team.user_id = cronofy_schedule.user_id LEFT JOIN user_profile ON user_profile.user_id = cronofy_schedule.user_id WHERE ", whereCond, " ", whereCond2, " cronofy_schedule.ws_id IS NOT NULL AND cronofy_schedule.status != 'open';");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT ROUND(IFNULL(@userUseService,0)) AS userUseService, ROUND(IFNULL(@numberOfUsers,0)) AS numberOfUsers, ROUND(IFNULL(@assignToCounsellors,0)) AS assignToCounsellors;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @userUseService = NULL;
SET @numberOfUsers = NULL;
SET @assignToCounsellors = NULL;

END;

#Code for creating sp_dashboard_booking_tab_event_count procedure
CREATE PROCEDURE `sp_dashboard_booking_tab_event_count`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN roletype VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN wellbeingSpecialistId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

SET @todayEvent = 0;
SET @upcomingEvent = 0;
SET @completedEvent = 0;
SET @cancelledEvent = 0;

IF (roletype = 'zevo') THEN
    SET whereCond = CONCAT(whereCond , " events.company_id IS NULL AND ");
ELSEIF (roletype = 'reseller') THEN
    SET whereCond = CONCAT(whereCond, " (events.company_id IS NULL OR events.company_id IN (", companyId, ")) AND ");
END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " event_booking_logs.company_id IN (", companyId, ") AND ");
END IF;

IF (wellbeingSpecialistId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " event_booking_logs.presenter_user_id = ", wellbeingSpecialistId ," AND ");
END IF;

SET @q = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id) INTO @todayEvent FROM `event_booking_logs` JOIN events ON events.id = event_booking_logs.event_id  WHERE ", whereCond, " CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), 'UTC', '", timezone, "') >= '" , fromDate , "' AND events.status = '2' AND event_booking_logs.status = '4' AND CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time), 'UTC', '", timezone, "') <= '" , toDate , "';");

SET @q1 = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id) INTO @upcomingEvent FROM `event_booking_logs` JOIN events ON events.id = event_booking_logs.event_id WHERE ", whereCond, " CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time) >= UTC_TIMESTAMP() and (event_booking_logs.status = '4' OR event_booking_logs.status = '6' ) AND events.status = '2'");

SET @q2 = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id) INTO @completedEvent FROM `event_booking_logs` JOIN events ON events.id = event_booking_logs.event_id WHERE ", whereCond, " ((CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time) <= UTC_TIMESTAMP() OR event_booking_logs.status = '5') AND event_booking_logs.status != '3') AND events.status = '2' ");

SET @q3 = CONCAT("SELECT COUNT(DISTINCT event_booking_logs.id) INTO @cancelledEvent FROM `event_booking_logs` JOIN events ON events.id = event_booking_logs.event_id WHERE ", whereCond, " event_booking_logs.status = '3' AND events.status = '2';");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @q3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT ROUND(IFNULL(@todayEvent,0)) AS todayEvent, ROUND(IFNULL(@upcomingEvent,0)) AS upcomingEvent, ROUND(IFNULL(@completedEvent,0)) AS completedEvent, ROUND(IFNULL(@cancelledEvent,0)) AS cancelledEvent;

SET @q = NULL;
SET @q1 = NULL;
SET @q2 = NULL;
SET @q3 = NULL;
SET @todayEvent = NULL;
SET @upcomingEvent = NULL;
SET @completedEvent = NULL;
SET @cancelledEvent = NULL;

END;

#Code for creating sp_dashboard_booking_tab_skill_trend procedure
CREATE PROCEDURE `sp_dashboard_booking_tab_skill_trend`(
    IN timezone VARCHAR(50),
    IN companyId VARCHAR(121),
    IN roletype VARCHAR(121),
    IN fromDate VARCHAR(50),
    IN toDate VARCHAR(50),
    IN wellbeingSpecialistId INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

IF (roletype = 'zevo') THEN
    SET whereCond = CONCAT(whereCond , " events.company_id IS NULL AND ");
ELSEIF (roletype = 'reseller') THEN
    SET whereCond = CONCAT(whereCond, " (events.company_id IS NULL OR events.company_id IN (", companyId, ")) AND ");
END IF;

IF (companyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " event_booking_logs.company_id IN (", companyId, ") AND ");
END IF;

IF (wellbeingSpecialistId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " event_booking_logs.presenter_user_id = ", wellbeingSpecialistId ," AND ");
END IF;

SET @q = CONCAT("SELECT sub_categories.name as categoriesSkill, COUNT(DISTINCT event_booking_logs.id) AS totalAssignUser FROM sub_categories LEFT JOIN events ON events.subcategory_id = sub_categories.id LEFT JOIN event_booking_logs ON event_booking_logs.event_id = events.id 
WHERE ", whereCond, " ((CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time) <= UTC_TIMESTAMP() OR event_booking_logs.status = '5') AND event_booking_logs.status != '3') AND events.status = '2' GROUP BY events.subcategory_id LIMIT 10;");

PREPARE stmt FROM @q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @q = NULL;

END;

UNPREPAREDSTMT;
            DB::unprepared($unpreparedStmt);
            //DB::commit();
        } catch (Exception $exception) {
            //DB::rollBack();
            echo $exception->getMessage();
        }
    }
}
