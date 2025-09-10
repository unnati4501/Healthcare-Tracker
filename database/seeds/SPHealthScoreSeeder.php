<?php declare (strict_types = 1);
namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class SPHealthScoreSeeder
 */
class SPHealthScoreSeeder extends Seeder
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
DROP PROCEDURE IF EXISTS `sp_health_score`;
DROP PROCEDURE IF EXISTS `sp_healthscore_survey`;
DROP PROCEDURE IF EXISTS `sp_healthscore_survey_backend`;
DROP PROCEDURE IF EXISTS `sp_healthscore_baseline`;

#Code for create sp_health_score procedure
CREATE PROCEDURE `sp_health_score`(
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pSurveyId BIGINT(12),
    IN pGender VARCHAR(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.can_access_app = 1 AND users.is_blocked = 0";

IF (pSurveyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND hs_survey.id = ", pSurveyId);
END IF;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pGender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.gender = '", pGender, "'");
END IF;

SET @qry = null;

SET @qry = CONCAT("SELECT record.category_id, record.sub_category_id, record.display_name, SUM(record.totalScore) as totalScore, SUM(record.totalMaxScore) as totalMaxScore
FROM
(
    SELECT
        hs_survey_responses.category_id,
        hs_survey_responses.sub_category_id,
        hs_sub_categories.display_name,
        SUM(hs_survey_responses.score) as totalScore,
        SUM(hs_questions.max_score) as totalMaxScore
    FROM hs_survey_responses
    INNER JOIN hs_questions ON hs_questions.id = hs_survey_responses.question_id
    INNER JOIN hs_survey ON hs_survey.id = hs_survey_responses.survey_id
    INNER JOIN hs_sub_categories ON hs_sub_categories.id = hs_survey_responses.sub_category_id
    INNER JOIN users ON users.id = hs_survey.user_id
    INNER JOIN user_profile ON user_profile.user_id = users.id
    INNER JOIN user_team ON user_team.user_id = users.id
    WHERE ", whereCond ,"
    GROUP BY hs_survey_responses.sub_category_id, hs_survey_responses.category_id
) as record
GROUP BY record.sub_category_id, record.category_id
");

PREPARE stmt FROM @qry;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @qry = NULL;

END;

#Code for create sp_healthscore_survey procedure
CREATE PROCEDURE `sp_healthscore_survey`(
    IN pCompanyId BIGINT(11),
    IN pDepartmentId BIGINT(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT "users.can_access_app = 1 and users.is_blocked = 0";

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

SET @counts = 0;
SET @allcounts = 0;
SET @maxcount = 0;
SET @appuser = 0;
SET @allsurvey = 0;
SET @physicalsurvey = 0;
SET @physcologicalsurvey = 0;

SET @appuserqry = CONCAT("SELECT COUNT(users.id) INTO @appuser
FROM users
INNER JOIN user_profile ON (user_profile.user_id = users.id)
INNER JOIN user_team ON (user_team.user_id = users.id)
WHERE ", whereCond, ";");

SET @alluserqry = CONCAT("SELECT
    COUNT(DISTINCT hs_survey.user_id) INTO @allsurvey
from hs_survey
INNER JOIN users ON (users.id = hs_survey.user_id)
INNER JOIN user_profile ON (user_profile.user_id = hs_survey.user_id)
INNER JOIN user_team ON (user_team.user_id = hs_survey.user_id)
WHERE ", whereCond, " AND hs_survey.physical_survey_complete_time IS NOT NULL AND hs_survey.physcological_survey_complete_time IS NOT NULL;");

SET @counts = CONCAT("SELECT
    COUNT(active_surveys.id),
    SUM(physical_survey_complete_time IS NOT NULL),
    SUM(physcological_survey_complete_time IS NOT NULL)
INTO @allcounts, @physicalsurvey, @physcologicalsurvey
FROM(
    SELECT base.*
    FROM hs_survey AS base
    WHERE base.id = (SELECT MAX(id) WHERE user_id = base.user_id)
    GROUP BY base.user_id
) AS active_surveys
INNER JOIN users ON (users.id = active_surveys.user_id)
INNER JOIN user_profile ON (user_profile.user_id = active_surveys.user_id)
INNER JOIN user_team ON (user_team.user_id = active_surveys.user_id)
WHERE ", whereCond);

PREPARE stmt1 FROM @appuserqry;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

PREPARE stmt2 FROM @alluserqry;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

PREPARE stmt3 FROM @counts;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SELECT GREATEST(@physicalsurvey, @physcologicalsurvey) INTO @maxcount;
IF(@maxcount IS NULL) THEN SET @maxcount = 0; END IF;

SELECT
    @appuser AS appuser,
    FLOOR(@appuser - @maxcount) AS notattempt,
    @allsurvey AS allsurvey,
    @allcounts AS allcounts,
    @physicalsurvey AS physicalsurvey,
    @physcologicalsurvey AS physcologicalsurvey;

SET @counts = null;
SET @allcounts = null;
SET @appuser = null;
SET @allsurvey = null;
SET @physicalsurvey = null;
SET @physcologicalsurvey = null;
SET @appuserqry = null;
SET @alluserqry = null;
SET @physicalsurveyqry = null;
SET @physcologicalsurveyqry = null;
SET @maxcount = NULL;
END;

#Code for create `sp_healthscore_survey_backend` procedure
CREATE PROCEDURE `sp_healthscore_survey_backend`(
    IN pType VARCHAR(50),
    IN pTZ VARCHAR(50),
    IN pDATE VARCHAR(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pSurveyId BIGINT(12),
    IN pId BIGINT(12),
    IN pGender VARCHAR(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE groupByCol VARCHAR(50) DEFAULT "category";
DECLARE whereCond TEXT DEFAULT CONCAT("DATE(CONVERT_TZ(hs_survey_responses.updated_at, 'UTC', '", pTZ ,"')) >= '", pDATE ,"' AND users.can_access_app = 1 AND users.is_blocked = 0");

IF (pId IS NOT NULL) THEN
    IF (pType = 'subcategory') THEN
        SET whereCond = CONCAT(whereCond, " AND hs_survey_responses.sub_category_id = ", pId);
    ELSE
        SET whereCond = CONCAT(whereCond, " AND hs_survey_responses.category_id = ", pId);
    END IF;
END IF;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_team.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pGender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.gender = '", pGender, "'");
END IF;

SET @qry = CONCAT("SELECT
    FORMAT(((record.totalScore * 100) / record.totalMaxScore), 1) AS scorePercentage,
    record.log_month_year
FROM(SELECT
        COUNT(hs_survey_responses.id) AS total_response_count,
        SUM(hs_survey_responses.score) as totalScore,
        SUM(hs_questions.max_score) as totalMaxScore,
        CONCAT(YEAR(DATE(CONVERT_TZ(hs_survey_responses.updated_at, 'UTC', '", pTZ ,"'))), '_', MONTH(DATE(CONVERT_TZ(hs_survey_responses.updated_at, 'UTC', '", pTZ ,"')))) AS log_month_year
    FROM hs_survey_responses
    INNER JOIN hs_questions ON hs_questions.id = hs_survey_responses.question_id
    INNER JOIN hs_survey ON hs_survey.id = hs_survey_responses.survey_id
    INNER JOIN users ON users.id = hs_survey.user_id
    INNER JOIN user_profile ON user_profile.user_id = users.id
    INNER JOIN user_team ON user_team.user_id = users.id
    WHERE ", whereCond ,"
    GROUP BY log_month_year
    HAVING total_response_count > 0) AS record");

PREPARE stmt FROM @qry;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @qry = NULL;
END;

#Code for create `sp_healthscore_baseline` procedure
CREATE PROCEDURE `sp_healthscore_baseline`(
    IN pType VARCHAR(50),
    IN pTZ VARCHAR(50),
    IN pCompanyId BIGINT(12),
    IN pDepartmentId BIGINT(12),
    IN pId BIGINT(12),
    IN pGender VARCHAR(12),
    IN pAge1 INT(11),
    IN pAge2 INT(11)
)
BEGIN

DECLARE whereCond TEXT DEFAULT CONCAT("users.can_access_app = 1 AND users.is_blocked = 0 AND CONVERT_TZ(hsr.created_at, 'UTC', '", pTZ, "') BETWEEN ihsr.initial_date AND ADDDATE(ihsr.initial_date, INTERVAL 29 DAY)");
DECLARE innerWhereCond TEXT DEFAULT CONCAT("users.can_access_app = 1 AND users.is_blocked = 0");

IF (pId IS NOT NULL) THEN
    IF (pType = 'subcategory') THEN
        SET whereCond = CONCAT(whereCond, " AND hsr.sub_category_id = ", pId);
        SET innerWhereCond = CONCAT(innerWhereCond, " AND hs_survey_responses.sub_category_id = ", pId);
    ELSE
        SET whereCond = CONCAT(whereCond, " AND hsr.category_id = ", pId);
        SET innerWhereCond = CONCAT(innerWhereCond, " AND hs_survey_responses.category_id = ", pId);
    END IF;
END IF;

IF (pCompanyId IS NOT NULL AND pDepartmentId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND hs_survey.company_id = ", pCompanyId, " AND user_team.company_id = ", pCompanyId, " AND user_team.department_id = ", pDepartmentId);
    SET innerWhereCond = CONCAT(innerWhereCond, " AND hs_survey.company_id = ", pCompanyId);
ELSEIF (pCompanyId IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND hs_survey.company_id = ", pCompanyId, " AND user_team.company_id = ", pCompanyId);
    SET innerWhereCond = CONCAT(innerWhereCond, " AND hs_survey.company_id = ", pCompanyId);
END IF;

IF (pAge1 IS NOT NULL AND pAge2 IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond , " AND user_profile.age BETWEEN ", pAge1, " AND ", pAge2);
END IF;

IF (pGender IS NOT NULL) THEN
    SET whereCond = CONCAT(whereCond, " AND user_profile.gender = '", pGender, "'");
END IF;

SET @qry = CONCAT("SELECT
    IFNULL(FORMAT(((SUM(hsr.score) * 100) / SUM(hs_questions.max_score)), 1), 0) AS baselinePercentage
FROM hs_survey_responses AS hsr
INNER JOIN hs_questions ON (hs_questions.id = hsr.question_id)
INNER JOIN hs_survey ON (hs_survey.id = hsr.survey_id)
INNER JOIN users ON users.id = hs_survey.user_id
INNER JOIN user_profile ON user_profile.user_id = users.id
INNER JOIN user_team ON user_team.user_id = users.id
CROSS JOIN(SELECT
    CONVERT_TZ(hs_survey_responses.created_at, 'UTC', '", pTZ, "') AS initial_date
FROM hs_survey_responses
INNER JOIN hs_survey ON (hs_survey.id = hs_survey_responses.survey_id)
INNER JOIN users ON users.id = hs_survey.user_id
WHERE ", innerWhereCond, "
ORDER BY hs_survey_responses.created_at ASC
LIMIT 1) AS ihsr
WHERE ", whereCond);

PREPARE stmt FROM @qry;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @qry = NULL;
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
