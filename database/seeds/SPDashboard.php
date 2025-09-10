<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SPDashboard extends Seeder
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
            $unpreparedStmt = <<<'UNPREPAREDSTMT'
DROP PROCEDURE IF EXISTS `sp_dashboard_overview`;
DROP PROCEDURE IF EXISTS `sp_dashboard_overview_inspire`;
DROP PROCEDURE IF EXISTS `sp_dashboard_move_users_teams`;
DROP PROCEDURE IF EXISTS `sp_move_popularexercises`;
DROP PROCEDURE IF EXISTS `sp_get_users`;
DROP PROCEDURE IF EXISTS `sp_nourish_burned_calories`;
DROP PROCEDURE IF EXISTS `sp_nourish_users_bmi`;
DROP PROCEDURE IF EXISTS `sp_inspire_course_details`;
DROP PROCEDURE IF EXISTS `sp_inspire_meditation_hours`;
DROP PROCEDURE IF EXISTS `sp_inspire_course_completed`;
DROP PROCEDURE IF EXISTS `sp_challange_ongoing_challanges`;
DROP PROCEDURE IF EXISTS `sp_challenge_most_earned_badges`;
DROP PROCEDURE IF EXISTS `sp_challenge_most_active_individual`;
DROP PROCEDURE IF EXISTS `sp_nourish_team_activity`;
DROP PROCEDURE IF EXISTS `sp_move_team_activity`;
DROP PROCEDURE IF EXISTS `sp_get_active_users`;

#Code for create sp_dashboard_overview procedure
CREATE PROCEDURE `sp_dashboard_overview`(
    IN pWeek1_1 varchar(50),
    IN pWeek1_2 varchar(50),
    IN pWeek2_1 varchar(50),
    IN pWeek2_2 varchar(50),
    IN pTZ varchar(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

set @s = CONCAT("SELECT
    DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) AS log_date_only,
    (CASE
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek1_1, "' AND '", pWeek1_2, "' THEN 1
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek2_1, "' AND '", pWeek2_2, "' THEN 2
        ELSE 0
    END) AS flag,
    SUM(CASE
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek1_1, "' AND '", pWeek1_2, "' THEN user_step.steps
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek2_1, "' AND '", pWeek2_2, "' THEN user_step.steps
        ELSE 0
    END) AS steps,
    SUM(CASE
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek1_1, "' AND '", pWeek1_2, "' THEN user_step.calories
        WHEN DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek2_1, "' AND '", pWeek2_2, "' THEN user_step.calories
        ELSE 0
    END) AS calories
FROM user_step
INNER JOIN user_team ON (user_team.user_id = user_step.user_id)
INNER JOIN user_profile ON(user_profile.user_id = user_step.user_id)
INNER JOIN users ON (users.id = user_step.user_id)
WHERE ", whereCond, "
GROUP BY flag
HAVING log_date_only BETWEEN '", pWeek1_1, "' AND '", pWeek2_2, "'
ORDER BY flag ASC;");

#SELECT @s;

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s = NULL;
END;

#Code for create sp_dashboard_overview_inspire procedure
CREATE PROCEDURE `sp_dashboard_overview_inspire`(
    IN pWeek1_1 varchar(50),
    IN pWeek1_2 varchar(50),
    IN pWeek2_1 varchar(50),
    IN pWeek2_2 varchar(50),
    IN pTZ varchar(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

set @s = CONCAT("SELECT
    DATE(CONVERT_TZ(user_listened_tracks.updated_at, 'UTC', '", pTZ ,"')) AS log_date_only,
    (CASE
        WHEN DATE(CONVERT_TZ(user_listened_tracks.updated_at, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek1_1, "' AND '", pWeek1_2, "' THEN 1
        WHEN DATE(CONVERT_TZ(user_listened_tracks.updated_at, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek2_1, "' AND '", pWeek2_2, "' THEN 2
        ELSE 0
    END) AS flag,
    COUNT(CASE
        WHEN DATE(CONVERT_TZ(user_listened_tracks.updated_at, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek1_1, "' AND '", pWeek1_2, "' THEN user_listened_tracks.id
        WHEN DATE(CONVERT_TZ(user_listened_tracks.updated_at, 'UTC', '", pTZ ,"')) BETWEEN '", pWeek2_1, "' AND '", pWeek2_2, "' THEN user_listened_tracks.id
        ELSE 0
    END) AS listened_tracks_count
FROM user_listened_tracks
INNER JOIN user_team ON (user_team.user_id = user_listened_tracks.user_id)
INNER JOIN user_profile ON(user_profile.user_id = user_listened_tracks.user_id)
INNER JOIN users ON (users.id = user_listened_tracks.user_id)
WHERE ", whereCond, "
GROUP BY flag
HAVING log_date_only BETWEEN '", pWeek1_1, "' AND '", pWeek2_2, "'
ORDER BY flag ASC;");

#SELECT @s;

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s = NULL;
END;

#Code for create sp_dashboard_move_users_teams procedure
CREATE PROCEDURE `sp_dashboard_move_users_teams`(
    IN pTZ varchar(50),
    IN pActiveUsersDate varchar(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.is_blocked = 0";
DECLARE whereCond2 TEXT DEFAULT "";
SET @activeUsers = 0;
SET @totalUsers = 0;
SET @activeTeams = 0;
SET @totalTeams = 0;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond2 = CONCAT("teams.company_id = ", pCompanyId, " AND teams.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond2 = CONCAT("teams.company_id = ", pCompanyId);
ELSE
    SET whereCond2 = "1 = 1";
END IF;

SET @q1 = CONCAT("SELECT
    SUM(DATE(CONVERT_TZ(users.last_activity_at, 'UTC', '", pTZ ,"')) >= '", pActiveUsersDate, "'),
    COUNT(users.id)
INTO @activeUsers, @totalUsers
FROM users
INNER JOIN user_team ON (user_team.user_id = users.id)
INNER JOIN user_profile ON (user_profile.user_id = users.id)
WHERE ", whereCond, ";");

SET @q2 = CONCAT("SELECT
    SUM(DATE(CONVERT_TZ(teams.created_at, 'UTC', '", pTZ ,"')) >= '", pActiveUsersDate, "'),
    COUNT(teams.id)
INTO @activeTeams, @totalTeams
FROM teams
WHERE ", whereCond2, ";");

#SELECT @q1,  @q2;

PREPARE stmt1 FROM @q1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @q2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT
    @activeUsers AS activeUsers,
    @totalUsers AS totalUsers,
    @activeTeams AS activeTeams,
    @totalTeams AS totalTeams;

SET @q1 = NULL;
SET @q2 = NULL;
SET @activeUsers = NULL;
SET @totalUsers = NULL;
SET @activeTeams = NULL;
SET @totalTeams = NULL;

END;

#Code for create sp_move_popularexercises procedure
CREATE PROCEDURE `sp_move_popularexercises`(
    IN pTZ VARCHAR(50),
    IN pEndDate VARCHAR(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 BIGINT(11),
    IN pAge2 BIGINT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND DATE(CONVERT_TZ(user_exercise.end_date, 'UTC', '", pTZ ,"')) >= '", pEndDate, "'");
SET @totalExercisesUser = 0;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET @totalParticiaptedQry = CONCAT("SELECT
    COUNT(DISTINCT user_exercise.user_id) INTO @totalExercisesUser
FROM user_exercise
INNER JOIN user_team ON (user_team.user_id = user_exercise.user_id)
INNER JOIN user_profile ON (user_profile.user_id = user_exercise.user_id)
INNER JOIN users ON (users.id = user_exercise.user_id)
WHERE ", whereCond, ";");

PREPARE totalParticiaptedQrystmt FROM @totalParticiaptedQry;
EXECUTE totalParticiaptedQrystmt;
DEALLOCATE PREPARE totalParticiaptedQrystmt;

SET @q = CONCAT("SELECT
    exercises.title,
    #COUNT(DISTINCT user_exercise.user_id) AS total_particiapted_users,
    IF((@totalExercisesUser > 0), ((COUNT(DISTINCT user_exercise.user_id) * 100) /", @totalExercisesUser ,"), 0) as participant_percent,
    (SUM(user_exercise.duration)/3600) AS duration_hours
FROM exercises
INNER JOIN user_exercise ON (exercises.id = user_exercise.exercise_id)
INNER JOIN user_team ON (user_team.user_id = user_exercise.user_id)
INNER JOIN user_profile ON (user_profile.user_id = user_exercise.user_id)
INNER JOIN users ON (users.id = user_exercise.user_id)
WHERE ", whereCond, "
GROUP BY exercises.id
HAVING participant_percent > 0;");

#SELECT @q, @totalParticiaptedQry, @totalExercisesUser;

PREPARE stmt1 FROM @q;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q = NULL;
SET @totalParticiaptedQry = NULL;

END;

#Code for create sp_get_users procedure
CREATE  PROCEDURE `sp_get_users`(
    IN pType VARCHAR(50),
    IN pActiveUserDate VARCHAR(50),
    IN pTZ VARCHAR(50),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11),
    IN pGender VARCHAR(10)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "";

IF (pType = "active") THEN
    SET whereCond = CONCAT("users.can_access_app = 1 AND DATE(CONVERT_TZ(users.last_activity_at, 'UTC', '", pTZ ,"')) >= '", pActiveUserDate ,"' AND users.is_blocked = 0");
ELSEIF (pType = "app") THEN
    SET whereCond = CONCAT("(users.can_access_app = 1 || users.can_access_portal = 1) AND users.is_blocked = 0");
ELSEIF (pType = "total") THEN
    SET whereCond = CONCAT("users.is_blocked = 0");
END IF;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", pCompanyId ,") AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", pCompanyId ,")");
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pGender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.gender = '", pGender ,"'");
END IF;

SET @q = CONCAT("SELECT COUNT(users.id) AS active_users
FROM users
INNER JOIN user_team ON (user_team.user_id = users.id)
INNER JOIN user_profile ON (user_profile.user_id = users.id)
WHERE ", whereCond, ";");

#SELECT @q;

PREPARE stmt1 FROM @q;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q = NULL;

END;

#Code for create sp_nourish_burned_calories procedure
CREATE PROCEDURE `sp_nourish_burned_calories`(
    IN pTZ VARCHAR(50),
    IN pType VARCHAR(50),
    IN pDate VARCHAR(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) >= '", pDate, "'");
DECLARE groupByCol VARCHAR(50) DEFAULT "";
DECLARE orderByCol VARCHAR(50) DEFAULT "";
DECLARE cols TEXT DEFAULT "";

SET groupByCol = (CASE
    WHEN (pType = "day") THEN "log_date_only"
    WHEN (pType = "month") THEN "log_date_week"
    WHEN (pType = "year") THEN "log_month"
END);

SET orderByCol = (CASE
    WHEN (pType = "day") THEN "log_date_only ASC"
    WHEN (pType = "month") THEN "log_date_week ASC"
    WHEN (pType = "year") THEN "log_date_year ASC"
END);

SET cols = (CASE
    WHEN (pType = "day") THEN CONCAT("DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) AS log_date_only")
    WHEN (pType = "month") THEN CONCAT("WEEK(DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')), 1) AS log_date_week")
    WHEN (pType = "year") THEN CONCAT("MONTH(log_date) AS log_month, DATE(CONVERT_TZ(user_step.log_date, 'UTC', '", pTZ ,"')) AS log_date_year")
END);

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

set @s = CONCAT("SELECT
    SUM(calories) AS burnt_calories, ",
    cols, "
FROM user_step
INNER JOIN user_team ON (user_team.user_id = user_step.user_id)
INNER JOIN user_profile ON (user_profile.user_id = user_team.user_id)
INNER JOIN users ON (users.id = user_team.user_id)
WHERE ", whereCond, "
GROUP BY ", groupByCol, "
HAVING burnt_calories > 0
ORDER BY ", orderByCol, ";");

#SELECT @s;

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s = NULL;

END;

#Code for create sp_nourish_users_bmi procedure
CREATE PROCEDURE `sp_nourish_users_bmi`(
    IN pTZ VARCHAR(50),
    IN pGender VARCHAR(10),
    IN ptotalUsers BIGINT(11),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pAge1 BIGINT(11),
    IN pAge2 BIGINT(11)
)
BEGIN

DECLARE whereCond text DEFAULT CONCAT("users.is_blocked = 0 AND t2.user_id IS NULL AND users.can_access_app = 1 AND users.is_blocked = 0");

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pGender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.gender = '", pGender ,"'");
END IF;

#bmi_flag
#1 - Underweight
#2 - Normal
#3 - Overweight
#4 - Obese

SET @s = CONCAT("SELECT
    COUNT(t1.user_id),
    (CASE
        WHEN (t1.bmi <= 18.5) THEN 1
        WHEN (t1.bmi >= 18.6 AND t1.bmi <= 24.9) THEN 2
        WHEN (t1.bmi >= 25 AND t1.bmi <= 29.9) THEN 3
        ELSE 4
    END) AS bmi_flag,
    SUM(t1.weight) AS weight,
    ((COUNT(t1.user_id) * 100) / ", ptotalUsers ,") AS percentages
FROM user_bmi t1
LEFT JOIN user_bmi t2 ON (t1.user_id = t2.user_id AND DATE(CONVERT_TZ(t1.log_date, 'UTC', '", pTZ ,"')) < DATE(CONVERT_TZ(t2.log_date, 'UTC', '", pTZ ,"')))
INNER JOIN user_profile ON (user_profile.user_id = t1.user_id)
INNER JOIN user_team ON (user_team.user_id = t1.user_id)
INNER JOIN users ON (users.id = t1.user_id)
WHERE ", whereCond, "
GROUP BY bmi_flag
ORDER BY bmi_flag ASC;");

#SELECT @s;

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

END;

#Code for create sp_inspire_course_details procedure
CREATE PROCEDURE `sp_inspire_course_details`(
IN pCompanyId BIGINT(12),
IN pDepartmentId BIGINT(12),
IN pAge1 INT(11),
IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.can_access_app = 1 AND users.is_blocked = 0";
DECLARE joinQuery TEXT DEFAULT "";

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET @qry1 = 0;
SET @qry2 = 0;
SET @qry3 = 0;
SET @joinedCourses = 0;
SET @startedCourses = 0;
SET @completedCourses = 0;

SET joinQuery = "INNER JOIN user_team ON (user_team.user_id = user_course.user_id)
INNER JOIN user_profile ON(user_profile.user_id = user_course.user_id)
INNER JOIN users ON(users.id = user_course.user_id)";

SET @qry1 = CONCAT("SELECT
COUNT(user_course.user_id)
FROM user_course
", joinQuery, "
WHERE ", whereCond ," AND joined=1 INTO @joinedCourses");

SET @qry2 = CONCAT("SELECT
COUNT(user_course.user_id)
FROM user_course
", joinQuery, "
WHERE ", whereCond ," AND started_course=1 INTO @startedCourses");

SET @qry3 = CONCAT("SELECT
COUNT(user_course.user_id)
FROM user_course
", joinQuery, "
WHERE ", whereCond ," AND completed=1 INTO @completedCourses");

PREPARE stmt1 FROM @qry1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @qry2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @qry3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT @joinedCourses as joined_courses, @startedCourses as started_courses, @completedCourses as completed_courses;

SET @qry1 = NULL;
SET @qry2 = NULL;
SET @qry3 = NULL;
SET @joinedCourses = NULL;
SET @startedCourses = NULL;
SET @completedCourses = NULL;

END;

#Code for create sp_inspire_meditation_hours procedure
CREATE PROCEDURE `sp_inspire_meditation_hours`(
    IN pTZ VARCHAR(50),
    IN pType VARCHAR(50),
    IN pDate VARCHAR(50),
    IN pCompanyId VARCHAR(121),
    IN pDepartmentId BIGINT(12),
    IN pLocationId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", pTZ ,"')) >= '", pDate, "'");
DECLARE groupByCol VARCHAR(50) DEFAULT "";
DECLARE orderByCol VARCHAR(50) DEFAULT "";
DECLARE cols TEXT DEFAULT "";

SET groupByCol = (CASE
    WHEN (pType = "day") THEN "log_date_only"
    WHEN (pType = "month") THEN "log_date_week"
    WHEN (pType = "year") THEN "log_month"
END);

SET orderByCol = (CASE
    WHEN (pType = "day") THEN "log_date_only ASC"
    WHEN (pType = "month") THEN "log_date_week ASC"
    WHEN (pType = "year") THEN "log_date_year ASC"
END);

SET cols = (CASE
    WHEN (pType = "day") THEN CONCAT("DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", pTZ ,"')) AS log_date_only")
    WHEN (pType = "month") THEN CONCAT("WEEK(DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", pTZ ,"')), 1) AS log_date_week")
    WHEN (pType = "year") THEN CONCAT("DATE(CONVERT_TZ(user_listened_tracks.created_at, 'UTC', '", pTZ ,"')) AS log_date_year, MONTH(user_listened_tracks.created_at) AS log_month")
END);

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL AND pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", pCompanyId, ") AND user_team.department_id = ", pDepartmentId," AND company_locations.id = ", pLocationId);
ELSEIF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL AND pLocationId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", pCompanyId, ") AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id IN (", pCompanyId ,")");
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

set @s = CONCAT("SELECT
    ROUND((SUM(user_listened_tracks.duration_listened) / 60), 1) AS listened_hours, ",
    cols, "
FROM user_listened_tracks
INNER JOIN user_team ON (user_team.user_id = user_listened_tracks.user_id)
INNER JOIN user_profile ON (user_profile.user_id = user_listened_tracks.user_id)
INNER JOIN users ON (users.id = user_listened_tracks.user_id)
INNER JOIN company_locations ON (company_locations.company_id = user_team.company_id)
WHERE ", whereCond, "
GROUP BY ", groupByCol, "
HAVING listened_hours > 0
ORDER BY ", orderByCol);

#SELECT @s;

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @s = NULL;

END;

#Code for create sp_inspire_course_completed procedure
CREATE PROCEDURE `sp_inspire_course_completed`(
IN pCompanyId BIGINT(12),
IN pDepartmentId BIGINT(12),
IN pAge1 INT(11),
IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.can_access_app = 1 AND users.is_blocked = 0";
DECLARE joinQuery TEXT DEFAULT "";

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET @qry = 0;

SET joinQuery = "INNER JOIN user_team ON (user_team.user_id = uc.user_id)
INNER JOIN user_profile ON(user_profile.user_id = uc.user_id)
INNER JOIN users ON(users.id = uc.user_id)";

SET @qry = CONCAT("SELECT
    record.cid , record.cname, (sum(record.subRecord)) as caverage
FROM (
    SELECT
        DISTINCT(cat.id) as cid, cat.name as cname, uc.user_id,
    (
        (
            (
                SELECT
                    COUNT(user_course.user_id)
                FROM user_course
                INNER JOIN courses ON courses.id = user_course.course_id
                INNER JOIN categories ON categories.id = courses.category_id
                WHERE categories.id = cat.id AND user_course.user_id = uc.user_id
                AND user_course.completed = 1
                GROUP BY cat.id , user_course.user_id
            ) * 100
        ) /
        (
            (
                SELECT
                    COUNT(courses.id)
                FROM courses
                INNER JOIN categories ON categories.id = courses.category_id
                WHERE categories.id = cat.id
                GROUP BY cat.id
            ) *
            (
                SELECT
                    COUNT(users.id)
                FROM users
                INNER JOIN user_team ON user_team.user_id = users.id
                INNER JOIN user_profile ON user_profile.user_id = users.id
                WHERE ", whereCond ,"
            )
        )
    ) as subRecord
    FROM user_course as uc
    INNER JOIN courses ON courses.id = uc.course_id
    INNER JOIN categories as cat ON cat.id = courses.category_id
    ", joinQuery, "
    WHERE ", whereCond ,"
    AND cat.is_excluded = 0
    GROUP BY cat.id , uc.user_id
) as record
GROUP BY record.cid");

PREPARE stmt FROM @qry;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @qry = NULL;

END;

#Code for create sp_challange_ongoing_challanges procedure
CREATE PROCEDURE `sp_challange_ongoing_challanges`(
    IN pTZ VARCHAR(50),
    IN pDate VARCHAR(50),
    IN pCompanyId BIGINT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("challenges.cancelled = FALSE");
DECLARE whereCond2 TEXT DEFAULT CONCAT("challenges.cancelled = FALSE");

IF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND challenges.company_id = ", pCompanyId);
    SET whereCond2 = CONCAT(whereCond2, " AND challenge_participants.company_id = ", pCompanyId);
END IF;

SET @totalongoingchallenges = 0;
SET @ictotalongoingchallenges = 0;
SET @totalchallenges = 0;
SET @ictotalchallenges = 0;
SET @totalcompletedchallenges = 0;
SET @ictotalcompletedchallenges = 0;

SET @s = CONCAT("SELECT
    SUM(CONVERT_TZ(challenges.start_date, 'UTC', '", pTZ ,"') <= '", pDate ,"' AND CONVERT_TZ(challenges.end_date, 'UTC', '", pTZ ,"') >= '", pDate ,"'),
    (SUM(CONVERT_TZ(challenges.start_date, 'UTC', '", pTZ ,"') <= '", pDate ,"' AND CONVERT_TZ(challenges.end_date, 'UTC', '", pTZ ,"') >= '", pDate ,"') + SUM(CONVERT_TZ(challenges.start_date, 'UTC', '", pTZ ,"') > '", pDate ,"')),
    SUM(CONVERT_TZ(challenges.end_date, 'UTC', '", pTZ ,"') < '", pDate ,"')
INTO @totalongoingchallenges, @totalchallenges, @totalcompletedchallenges
FROM challenges
WHERE ", whereCond, ";");

SET @s2 = CONCAT("SELECT
    SUM(CONVERT_TZ(icdata.start_date, 'UTC', '", pTZ ,"') <= '", pDate ,"' AND CONVERT_TZ(icdata.end_date, 'UTC', '", pTZ ,"') >= '", pDate ,"'),
    (SUM(CONVERT_TZ(icdata.start_date, 'UTC', '", pTZ ,"') <= '", pDate ,"' AND CONVERT_TZ(icdata.end_date, 'UTC', '", pTZ ,"') >= '", pDate ,"') + SUM(CONVERT_TZ(icdata.start_date, 'UTC', '", pTZ ,"') > '", pDate ,"')),
    SUM(CONVERT_TZ(icdata.end_date, 'UTC', '", pTZ ,"') < '", pDate ,"')
INTO @ictotalongoingchallenges, @ictotalchallenges, @ictotalcompletedchallenges
FROM (SELECT challenges.start_date, challenges.end_date
FROM challenges
    INNER JOIN challenge_participants ON (challenge_participants.challenge_id = challenges.id)
    WHERE ", whereCond2, " AND challenges.challenge_type = 'inter_company'
    GROUP BY challenges.id
) AS icdata");

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt1 FROM @s2;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;


SELECT
    ROUND(@totalongoingchallenges + @ictotalongoingchallenges) AS totalongoingchallenges,
    ROUND(@totalchallenges + @ictotalchallenges) AS totalchallenges,
    ROUND(@totalcompletedchallenges + @ictotalcompletedchallenges) AS totalcompletedchallenges;

SET @s = NULL;
SET @s2 = NULL;
SET @totalongoingchallenges = NULL;
SET @ictotalongoingchallenges = NULL;
SET @totalchallenges = NULL;
SET @ictotalchallenges = NULL;
SET @totalcompletedchallenges = NULL;
SET @ictotalcompletedchallenges = NULL;

END;

#Code for create sp_challenge_most_earned_badges procedure
CREATE PROCEDURE `sp_challenge_most_earned_badges`(
    IN pTZ VARCHAR(50),
    IN pDATE VARCHAR(50),
    IN pCompanyId BIGINT(10),
    IN pDepartmentId BIGINT(10),
    IN pAge1 INT(10),
    IN pAge2 INT(10)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND
    users.can_access_app = 1 AND
    b.status = 'Active' AND
    badges.type = 'challenge' AND
    CONVERT_TZ(b.created_at, 'UTC', '", pTZ ,"') >= '", pDATE ,"' AND
    b.expired_at IS NULL");

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));

SET @q = CONCAT("SELECT
    b.user_id,
    CONCAT(users.first_name, ' ', users.last_name) AS user_name,
    COUNT(b.id) AS earned_badges,
    departments.name AS departments_name,
    company_locations.name AS location_name,
    (SELECT created_at FROM badge_user WHERE user_id = b.user_id ORDER BY created_at DESC LIMIT 1) as max_date
FROM badge_user AS b
    INNER JOIN badges ON (badges.id = b.badge_id)
    INNER JOIN users ON (users.id = b.user_id)
    INNER JOIN user_team ON (user_team.user_id = b.user_id)
    INNER JOIN user_profile ON (user_profile.user_id = b.user_id)
    INNER JOIN departments ON (departments.id = user_team.department_id)
    INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
    INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
WHERE ", whereCond ,"
GROUP BY b.user_id
HAVING earned_badges > 0
ORDER BY earned_badges DESC, user_name ASC
LIMIT 5;");

#SELECT @q;

PREPARE stmt1 FROM @q;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q = NULL;

END;

#Code for create sp_challenge_most_active_individual procedure
CREATE PROCEDURE `sp_challenge_most_active_individual`(
    IN pTZ VARCHAR(50),
    IN pDATE VARCHAR(50),
    IN pCompanyId BIGINT(10),
    IN pDepartmentId BIGINT(10),
    IN pAge1 INT(10),
    IN pAge2 INT(10)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.is_blocked = 0 AND
    users.can_access_app = 1 AND
    challenges.cancelled = FALSE AND
    CONVERT_TZ(challenges.start_date, 'UTC', '", pTZ ,"') <= '", pDATE ,"' AND
    CONVERT_TZ(challenges.end_date, 'UTC', '", pTZ ,"') >= '", pDATE ,"'");

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET @q = CONCAT("SELECT
    users.id,
    CONCAT(users.first_name, ' ', users.last_name) AS user_name,
    departments.name AS departments_name,
    company_locations.name AS location_name,
    SUM(challenge_wise_user_ponits.points) AS points,
    COUNT(DISTINCT challenge_wise_user_ponits.challenge_id) AS all_participated_challenges
FROM challenge_wise_user_ponits
INNER JOIN users ON (users.id = challenge_wise_user_ponits.user_id)
INNER JOIN challenges ON (challenges.id = challenge_wise_user_ponits.challenge_id)
INNER JOIN user_team ON (user_team.user_id = users.id)
INNER JOIN user_profile ON (user_profile.user_id = users.id)
INNER JOIN departments ON (departments.id = user_team.department_id)
INNER JOIN team_location ON (team_location.company_id = user_team.company_id AND team_location.department_id = user_team.department_id AND team_location.team_id = user_team.team_id)
INNER JOIN company_locations ON (company_locations.id = team_location.company_location_id)
WHERE ", whereCond ,"
GROUP BY challenge_wise_user_ponits.user_id
HAVING points > 0
ORDER BY points DESC, user_name ASC
LIMIT 5;");

#SELECT @q;

PREPARE stmt1 FROM @q;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @q = NULL;

END;
UNPREPAREDSTMT;
            DB::unprepared($unpreparedStmt);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }
}
