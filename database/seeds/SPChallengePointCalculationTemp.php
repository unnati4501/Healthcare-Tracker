<?php declare (strict_types = 1);
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SPChallengePointCalculationTemp extends Seeder
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
DROP PROCEDURE IF EXISTS `sp_inter_comp_challenge_pointcalculation0`;
DROP PROCEDURE IF EXISTS `sp_inter_comp_challenge_pointcalculation1`;
DROP PROCEDURE IF EXISTS `sp_inter_comp_challenge_pointcalculation2`;
DROP PROCEDURE IF EXISTS `sp_company_challenge_pointcalculation0`;
DROP PROCEDURE IF EXISTS `sp_company_challenge_pointcalculation1`;
DROP PROCEDURE IF EXISTS `sp_company_challenge_pointcalculation2`;
DROP PROCEDURE IF EXISTS `sp_team_challenge_pointcalculation0`;
DROP PROCEDURE IF EXISTS `sp_team_challenge_pointcalculation1`;
DROP PROCEDURE IF EXISTS `sp_team_challenge_pointcalculation2`;
DROP PROCEDURE IF EXISTS `sp_individual_challenge_pointcalculation0`;
DROP PROCEDURE IF EXISTS `sp_individual_challenge_pointcalculation1`;
DROP PROCEDURE IF EXISTS `sp_individual_challenge_pointcalculation2`;

CREATE PROCEDURE `sp_inter_comp_challenge_pointcalculation0`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

DECLARE challenge_id bigint(11);

DELETE FROM `challenge_history` WHERE `challenge_history`.`challenge_id` = challengeId;

DELETE FROM `intComChUserPointListTable` WHERE `intComChUserPointListTable`.`tchID` = challengeId;

DELETE FROM `freezed_challenge_participents` WHERE `freezed_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_team_challenge_participents` WHERE `freezed_team_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `challenge_wise_user_ponits` WHERE `challenge_wise_user_ponits`.`challenge_id` = challengeId;

DELETE FROM `challenge_wise_team_ponits` WHERE `challenge_wise_team_ponits`.`challenge_id` = challengeId;

DELETE FROM `challenge_wise_company_points` WHERE `challenge_wise_company_points`.`challenge_id` = challengeId;

END;

CREATE PROCEDURE `sp_inter_comp_challenge_pointcalculation1`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_company_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

DECLARE userList cursor for (select distinct users.id , users.timezone , challenge_participants.team_id, challenge_participants.company_id from challenge_participants left join user_team  on challenge_participants.team_id = user_team.team_id left join users on user_team.user_id = users.id where challenge_participants.challenge_id = challengeId);

DECLARE challengeRuleList cursor for (select challenge_targets.short_name, challenge_rules.model_name , challenge_rules.uom , challenge_rules.model_id from challenge_rules inner join challenge_targets on challenge_targets.id = challenge_rules.challenge_target_id where challenge_rules.challenge_id = challengeId);

SET report_run_date_time = convert_tz(now(),@@session.time_zone,'UTC');
SET SESSION group_concat_max_len=4294967295;

select group_concat(distinct users.id) into participatedUserId  from challenge_participants inner join user_team  on challenge_participants.team_id = user_team.team_id inner join users on users.id = user_team.user_id where challenge_participants.challenge_id = challengeId;

SELECT
    id,
    creator_id,
    challenge_category_id,
    timezone,
    title,
    description,
    start_date,
    end_date
INTO challenge_id , challenge_creator_id , challenge_challenge_category_id , challenge_timezone , challenge_title , challenge_description , challenge_start_date , challenge_end_date FROM
    challenges
WHERE
    id = challengeId;


OPEN userList;
BEGIN
DECLARE userListFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET userListFlag = TRUE;

userList_loop:
LOOP

FETCH userList INTO userList_id, userList_timezone, userList_team_id, userList_company_id;

IF userListFlag THEN
    LEAVE userList_loop;
END IF;
set userPoint = 0;

IF (userList_id IS NOT NULL) then

    SELECT
        CONVERT_TZ(challenge_start_date,
                appTimeZone,
                userList_timezone)
    INTO convert_start_date;

    SELECT
        CONVERT_TZ(challenge_end_date,
                appTimeZone,
                userList_timezone)
    INTO convert_end_date;


    set userPoint = individual_point_calculation(challengeId,userList_id,convert_start_date,convert_end_date,appTimeZone,userList_timezone,steps,distance,exercises_distance,exercises_duration,meditations);
-- select appTimeZone , userPoint;

END IF;

INSERT INTO intComChUserPointListTable VALUES (challengeId,userList_id,userList_team_id,userList_company_id,userPoint,0);

END LOOP;
END;
CLOSE userList;

OPEN challengeRuleList;
BEGIN
DECLARE insertedStepsData boolean default false;
DECLARE challengeRuleFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET challengeRuleFlag = TRUE;

challengeRuleList_loop:
LOOP
FETCH challengeRuleList INTO rule_short_name, rule_model_name, rule_uom, rule_model_id;

IF challengeRuleFlag THEN
    LEAVE challengeRuleList_loop;
END IF;

IF (rule_short_name = 'distance' OR rule_short_name = 'steps') and insertedStepsData = false  THEN
    set insertedStepsData = true;

    insert into tempUserStepsTable (tchID,tUserId,tracker,steps,distance,calories,log_date) select challengeId , `user_step`.`user_id` , `user_step`.`tracker`, `user_step`.`steps`, `user_step`.`distance`, `user_step`.`calories` , `user_step`.`log_date` from `user_step` inner join `users` on `user_step`.`user_id` = `users`.`id` where find_in_set(`user_step`.`user_id`,participatedUserId) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);

ELSEIF (rule_short_name = 'exercises' and rule_model_name = 'Exercise') THEN

    insert into tempUserExerciseTable (tchID,tUserId,exercise_id,tracker,duration,distance,calories,start_date,end_date) select challengeId , `user_exercise`.`user_id`, `user_exercise`.`exercise_id` , `user_exercise`.`tracker`, `user_exercise`.`duration`, `user_exercise`.`distance`, `user_exercise`.`calories` , `user_exercise`.`start_date`, `user_exercise`.`end_date` from `user_exercise` inner join `users` on `user_exercise`.`user_id` = `users`.`id` where find_in_set(`user_exercise`.`user_id`,participatedUserId) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`) and `user_exercise`.`deleted_at` is NULL and `user_exercise`.`exercise_id` = rule_model_id  ;

ELSEIF rule_short_name = 'meditations' THEN

    insert into tempUserInspireTable (tchID,tUserId,meditation_track_id,duration_listened,log_date) select challengeId , `user_listened_tracks`.`user_id` , `user_listened_tracks`.`meditation_track_id`, `user_listened_tracks`.`duration_listened`, `user_listened_tracks`.`created_at` from `user_listened_tracks` inner join `users` on `user_listened_tracks`.`user_id` = `users`.`id` where find_in_set(`user_listened_tracks`.`user_id`,participatedUserId) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);
end IF;

END LOOP;
END;

CLOSE challengeRuleList;


-- Delete table Data before insert

insert into `challenge_history` (challenge_id,creator_id,challenge_category_id,challenge_type,timezone,title,description,start_date,end_date) select `challenges`.`id` , `challenges`.`creator_id`,`challenges`.`challenge_category_id`,`challenges`.`challenge_type`,`challenges`.`timezone`,`challenges`.`title`,`challenges`.`description`,`challenges`.`start_date`,`challenges`.`end_date` from `challenges`  where `challenges`.`id` = challengeId;

insert into `freezed_challenge_participents` (challenge_id,company_id,team_id,participant_name) select `challenge_participants`.`challenge_id`, `challenge_participants`.`company_id`, `challenge_participants`.`team_id` , `teams`.`name` from `challenge_participants` inner join `teams` on `challenge_participants`.`team_id` = `teams`.`id` where `challenge_participants`.`challenge_id` = challengeId;

insert into `freezed_team_challenge_participents` (challenge_id,user_id,team_id,company_id,participant_name,timezone,challenge_type) select `challenge_participants`.`challenge_id`,`user_team`.`user_id`, `challenge_participants`.`team_id` , `user_team`.`company_id` , concat(`users`.`first_name`," ",`users`.`last_name`) , `users`.`timezone` , 'team'  from `challenge_participants` inner join `user_team` on `challenge_participants`.`team_id` = `user_team`.`team_id` inner join  `users` on `user_team`.`user_id` = `users`.`id` where `challenge_participants`.`challenge_id` = challengeId;

IF(EXISTS(SELECT * FROM tempUserStepsTable)) then
    insert into `challenge_user_steps_history` (challenge_id,user_id,tracker,steps,distance,calories,points,log_date) select `tempUserStepsTable`.tchID,`tempUserStepsTable`.tUserId,`tempUserStepsTable`.tracker,`tempUserStepsTable`.steps,`tempUserStepsTable`.distance,`tempUserStepsTable`.calories,(select `intComChUserPointListTable`.tpoint from intComChUserPointListTable where `intComChUserPointListTable`.tUserId = `tempUserStepsTable`.tUserId limit 1),`tempUserStepsTable`.log_date from tempUserStepsTable where NOT EXISTS ( select * from `freezed_challenge_steps` where `freezed_challenge_steps`.`challenge_id` = challengeId  and `tempUserStepsTable`.tUserId = `freezed_challenge_steps`.user_id and `tempUserStepsTable`.tracker = `freezed_challenge_steps`.tracker and `tempUserStepsTable`.steps = `freezed_challenge_steps`.steps and `tempUserStepsTable`.distance = `freezed_challenge_steps`.distance and `tempUserStepsTable`.calories = `freezed_challenge_steps`.calories and `tempUserStepsTable`.log_date = `freezed_challenge_steps`.log_date );
END IF;

DELETE FROM `freezed_challenge_steps` WHERE `freezed_challenge_steps`.`challenge_id` = challengeId;

IF(EXISTS(SELECT * FROM tempUserExerciseTable)) then
    insert into `challenge_user_exercise_history` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,points,start_date,end_date) select `tempUserExerciseTable`.tchID,`tempUserExerciseTable`.tUserId,`tempUserExerciseTable`.exercise_id,`tempUserExerciseTable`.tracker,`tempUserExerciseTable`.duration,`tempUserExerciseTable`.distance,`tempUserExerciseTable`.calories,(select `intComChUserPointListTable`.tpoint from intComChUserPointListTable where `intComChUserPointListTable`.tUserId = `tempUserExerciseTable`.tUserId limit 1),`tempUserExerciseTable`.start_date,`tempUserExerciseTable`.end_date from tempUserExerciseTable where NOT EXISTS ( select * from `freezed_challenge_exercise` where `freezed_challenge_exercise`.`challenge_id` = challengeId  and `tempUserExerciseTable`.tUserId = `freezed_challenge_exercise`.user_id and `tempUserExerciseTable`.tracker = `freezed_challenge_exercise`.tracker and `tempUserExerciseTable`.exercise_id = `freezed_challenge_exercise`.exercise_id and `tempUserExerciseTable`.duration = `freezed_challenge_exercise`.duration and `tempUserExerciseTable`.distance = `freezed_challenge_exercise`.distance and `tempUserExerciseTable`.calories = `freezed_challenge_exercise`.calories and `tempUserExerciseTable`.start_date = `freezed_challenge_exercise`.start_date and `tempUserExerciseTable`.end_date = `freezed_challenge_exercise`.end_date );
END IF;

DELETE FROM `freezed_challenge_exercise` WHERE `freezed_challenge_exercise`.`challenge_id` = challengeId;

IF(EXISTS(SELECT * FROM tempUserInspireTable)) then
    insert into `challenge_user_inspire_history` (challenge_id,user_id,meditation_track_id,duration_listened,points,log_date) select `tempUserInspireTable`.tchID, `tempUserInspireTable`.tUserId, `tempUserInspireTable`.meditation_track_id, `tempUserInspireTable`.duration_listened, (select `intComChUserPointListTable`.tpoint from intComChUserPointListTable where `intComChUserPointListTable`.tUserId = `tempUserInspireTable`.tUserId limit 1), `tempUserInspireTable`.log_date from tempUserInspireTable where NOT EXISTS ( select * from `freezed_challenge_inspire` where `freezed_challenge_inspire`.`challenge_id` = challengeId  and `tempUserInspireTable`.tUserId = `freezed_challenge_inspire`.user_id and `tempUserInspireTable`.meditation_track_id = `freezed_challenge_inspire`.meditation_track_id and `tempUserInspireTable`.duration_listened = `freezed_challenge_inspire`.duration_listened and `tempUserInspireTable`.log_date = `freezed_challenge_inspire`.log_date );
END IF;

DELETE FROM `freezed_challenge_inspire` WHERE `freezed_challenge_inspire`.`challenge_id` = challengeId;

END;
CREATE PROCEDURE `sp_inter_comp_challenge_pointcalculation2`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_company_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

IF(EXISTS(SELECT * FROM tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_steps` (challenge_id,user_id,tracker,steps,distance,calories,log_date) select * from tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_exercise` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,start_date,end_date) select * from tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_inspire` (challenge_id,user_id,meditation_track_id,duration_listened,log_date) select * from tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId;
END IF;

SET @rank1 = 0, @prev_val = NULL;

IF(EXISTS(SELECT * FROM intComChUserPointListTable)) then

    insert into `challenge_wise_user_ponits` (`challenge_id`,`user_id`,`team_id`,`company_id`,`rank`,`points`)  SELECT `intComChUserPointListTable`.tchID , `intComChUserPointListTable`.tUserId , `intComChUserPointListTable`.tUserTeamId, `intComChUserPointListTable`.tUserComId , @rank1 := IF(@prev_val=`intComChUserPointListTable`.tpoint,@rank1,@rank1+1) AS `rank`, @prev_val := `intComChUserPointListTable`.tpoint AS tpoint FROM `intComChUserPointListTable` where `intComChUserPointListTable`.tchID = challengeId and `intComChUserPointListTable`.tUserId IS NOT NULL ORDER BY `intComChUserPointListTable`.tpoint DESC;


SET @rank1 = 0, @prev_val = NULL;

    insert into `challenge_wise_team_ponits` (`challenge_id`,`company_id`,`team_id`,`rank`,`points`) SELECT `teamAvg`.tchID , `teamAvg`.tUserComId , `teamAvg`.tUserTeamId , @rank1 := IF(@prev_val=`teamAvg`.teamAvgPoint,@rank1,@rank1+1) AS `rank`, @prev_val := `teamAvg`.teamAvgPoint AS teamAvgPoint FROM (select `intComChUserPointListTable`.tchID, `intComChUserPointListTable`.tUserComId , `intComChUserPointListTable`.tUserTeamId , sum(`intComChUserPointListTable`.tpoint) / count(`intComChUserPointListTable`.tUserTeamId) as teamAvgPoint from `intComChUserPointListTable` where `intComChUserPointListTable`.tchID = challengeId and `intComChUserPointListTable`.tUserTeamId IS NOT NULL group by `intComChUserPointListTable`.tUserTeamId ,`intComChUserPointListTable`.tUserComId , `intComChUserPointListTable`.tchID ) as teamAvg where `teamAvg`.tchID = challengeId ORDER BY `teamAvg`.teamAvgPoint DESC;

SET @rank1 = 0, @prev_val = NULL;

    insert into `challenge_wise_company_points` (`challenge_id`,`company_id`,`rank`,`points`) SELECT `cmpAvg`.tchID , `cmpAvg`.tUserComId , @rank1 := IF(@prev_val=`cmpAvg`.compAvg,@rank1,@rank1+1) AS `rank`, @prev_val := `cmpAvg`.compAvg AS compAvg FROM (select `cmpData`.tchID , `cmpData`.tUserComId , sum(`cmpData`.teamAvgPoint) / count(`cmpData`.tUserTeamId) as compAvg  from (select `intComChUserPointListTable`.tchID, `intComChUserPointListTable`.tUserComId , `intComChUserPointListTable`.tUserTeamId , sum(`intComChUserPointListTable`.tpoint) / count(`intComChUserPointListTable`.tUserTeamId) as teamAvgPoint from `intComChUserPointListTable` where `intComChUserPointListTable`.tchID = challengeId and `intComChUserPointListTable`.tUserTeamId IS NOT NULL group by `intComChUserPointListTable`.tUserTeamId ,`intComChUserPointListTable`.tUserComId , `intComChUserPointListTable`.tchID) as cmpData group by `cmpData`.tUserComId , `cmpData`.tchID ) as cmpAvg where `cmpAvg`.tchID = challengeId ORDER BY `cmpAvg`.compAvg DESC;

END IF;

DELETE FROM `tempUserStepsTable` WHERE `tempUserStepsTable`.`tchID` = challengeId;

DELETE FROM `tempUserExerciseTable` WHERE `tempUserExerciseTable`.`tchID` = challengeId;

DELETE FROM `tempUserInspireTable` WHERE `tempUserInspireTable`.`tchID` = challengeId;

UPDATE challenges SET job_finished = 0 WHERE id = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

update `challenges` set `challenges`.`freezed_data_at` = report_run_date_time where `challenges`.`id` = challengeId;

END;

CREATE PROCEDURE `sp_company_challenge_pointcalculation0`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

-- Delete table Data before insert
DELETE FROM `challenge_history` WHERE `challenge_history`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_participents` WHERE `freezed_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_team_challenge_participents` WHERE `freezed_team_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_steps` WHERE `freezed_challenge_steps`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_exercise` WHERE `freezed_challenge_exercise`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_inspire` WHERE `freezed_challenge_inspire`.`challenge_id` = challengeId;

DELETE FROM `companyChUserPointListTable` WHERE `companyChUserPointListTable`.`tchID` = challengeId;

DELETE FROM `challenge_wise_user_ponits` WHERE `challenge_wise_user_ponits`.`challenge_id` = challengeId;

DELETE FROM `challenge_wise_team_ponits` WHERE `challenge_wise_team_ponits`.`challenge_id` = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

END;

CREATE PROCEDURE `sp_company_challenge_pointcalculation1`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

DECLARE userList cursor for (select distinct users.id , users.timezone , challenge_participants.team_id from challenge_participants left join user_team  on challenge_participants.team_id = user_team.team_id left join users on user_team.user_id = users.id where challenge_participants.challenge_id = challengeId);

DECLARE challengeRuleList cursor for (select challenge_targets.short_name, challenge_rules.model_name , challenge_rules.uom , challenge_rules.model_id from challenge_rules inner join challenge_targets on challenge_targets.id = challenge_rules.challenge_target_id where challenge_rules.challenge_id = challengeId);



select group_concat(distinct users.id) into participatedUserId  from challenge_participants inner join user_team  on challenge_participants.team_id = user_team.team_id inner join users on users.id = user_team.user_id where challenge_participants.challenge_id = challengeId;

SELECT
    id,
    creator_id,
    challenge_category_id,
    timezone,
    title,
    description,
    start_date,
    end_date
INTO challenge_id , challenge_creator_id , challenge_challenge_category_id , challenge_timezone , challenge_title , challenge_description , challenge_start_date , challenge_end_date FROM
    challenges
WHERE
    id = challengeId;


OPEN userList;
BEGIN
DECLARE userListFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET userListFlag = TRUE;

userList_loop:
LOOP

FETCH userList INTO userList_id, userList_timezone, userList_team_id;

IF userListFlag THEN
    LEAVE userList_loop;
END IF;
set userPoint = 0;

IF (userList_id IS NOT NULL) then

    SELECT
        CONVERT_TZ(challenge_start_date,
                appTimeZone,
                userList_timezone)
    INTO convert_start_date;

    SELECT
        CONVERT_TZ(challenge_end_date,
                appTimeZone,
                userList_timezone)
    INTO convert_end_date;


    set userPoint = individual_point_calculation(challengeId,userList_id,convert_start_date,convert_end_date,appTimeZone,userList_timezone,steps,distance,exercises_distance,exercises_duration,meditations);
-- select appTimeZone , userPoint;

END IF;

INSERT INTO companyChUserPointListTable VALUES (challengeId,userList_id,userList_team_id,userPoint,0);

END LOOP;
END;
CLOSE userList;

OPEN challengeRuleList;
BEGIN
DECLARE insertedStepsData boolean default false;
DECLARE challengeRuleFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET challengeRuleFlag = TRUE;

challengeRuleList_loop:
LOOP
FETCH challengeRuleList INTO rule_short_name, rule_model_name, rule_uom, rule_model_id;

IF challengeRuleFlag THEN
    LEAVE challengeRuleList_loop;
END IF;

IF (rule_short_name = 'distance' OR rule_short_name = 'steps') and insertedStepsData = false  THEN
    set insertedStepsData = true;

    insert into tempUserStepsTable (tchID,tUserId,tracker,steps,distance,calories,log_date) select challengeId , `user_step`.`user_id` , `user_step`.`tracker`, `user_step`.`steps`, `user_step`.`distance`, `user_step`.`calories` , `user_step`.`log_date` from `user_step` inner join `users` on `user_step`.`user_id` = `users`.`id` where find_in_set(`user_step`.`user_id`,participatedUserId) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);

ELSEIF (rule_short_name = 'exercises' and rule_model_name = 'Exercise') THEN

    insert into tempUserExerciseTable (tchID,tUserId,exercise_id,tracker,duration,distance,calories,start_date,end_date) select challengeId , `user_exercise`.`user_id`, `user_exercise`.`exercise_id` , `user_exercise`.`tracker`, `user_exercise`.`duration`, `user_exercise`.`distance`, `user_exercise`.`calories` , `user_exercise`.`start_date`, `user_exercise`.`end_date` from `user_exercise` inner join `users` on `user_exercise`.`user_id` = `users`.`id` where find_in_set(`user_exercise`.`user_id`,participatedUserId) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`) and `user_exercise`.`deleted_at` is NULL and `user_exercise`.`exercise_id` = rule_model_id  ;

ELSEIF rule_short_name = 'meditations' THEN

    insert into tempUserInspireTable (tchID,tUserId,meditation_track_id,duration_listened,log_date) select challengeId , `user_listened_tracks`.`user_id` , `user_listened_tracks`.`meditation_track_id`, `user_listened_tracks`.`duration_listened`, `user_listened_tracks`.`created_at` from `user_listened_tracks` inner join `users` on `user_listened_tracks`.`user_id` = `users`.`id` where find_in_set(`user_listened_tracks`.`user_id`,participatedUserId) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);
end IF;

END LOOP;
END;

CLOSE challengeRuleList;
END;

CREATE PROCEDURE `sp_company_challenge_pointcalculation2`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

SET report_run_date_time = convert_tz(now(),@@session.time_zone,'UTC');
SET SESSION group_concat_max_len=4294967295;

insert into `challenge_history` (challenge_id,creator_id,challenge_category_id,challenge_type,timezone,title,description,start_date,end_date) select `challenges`.`id` , `challenges`.`creator_id`,`challenges`.`challenge_category_id`,`challenges`.`challenge_type`,`challenges`.`timezone`,`challenges`.`title`,`challenges`.`description`,`challenges`.`start_date`,`challenges`.`end_date` from `challenges`  where `challenges`.`id` = challengeId;

insert into `freezed_challenge_participents` (challenge_id,team_id,participant_name) select `challenge_participants`.`challenge_id`, `challenge_participants`.`team_id` , `teams`.`name` from `challenge_participants` inner join `teams` on `challenge_participants`.`team_id` = `teams`.`id` where `challenge_participants`.`challenge_id` = challengeId;

insert into `freezed_team_challenge_participents` (challenge_id,user_id,team_id,participant_name,timezone,challenge_type) select `challenge_participants`.`challenge_id`,`user_team`.`user_id`, `challenge_participants`.`team_id` , concat(`users`.`first_name`," ",`users`.`last_name`) , `users`.`timezone` , 'team'  from `challenge_participants` inner join `user_team` on `challenge_participants`.`team_id` = `user_team`.`team_id` inner join  `users` on `user_team`.`user_id` = `users`.`id` where `challenge_participants`.`challenge_id` = challengeId;

IF(EXISTS(SELECT * FROM tempUserStepsTable)) then
    insert into `challenge_user_steps_history` (challenge_id,user_id,tracker,steps,distance,calories,points,log_date) select `tempUserStepsTable`.tchID,`tempUserStepsTable`.tUserId,`tempUserStepsTable`.tracker,`tempUserStepsTable`.steps,`tempUserStepsTable`.distance,`tempUserStepsTable`.calories,(select `companyChUserPointListTable`.tpoint from companyChUserPointListTable where `companyChUserPointListTable`.tUserId = `tempUserStepsTable`.tUserId limit 1),`tempUserStepsTable`.log_date from tempUserStepsTable where NOT EXISTS ( select * from `freezed_challenge_steps` where `freezed_challenge_steps`.`challenge_id` = challengeId  and `tempUserStepsTable`.tUserId = `freezed_challenge_steps`.user_id and `tempUserStepsTable`.tracker = `freezed_challenge_steps`.tracker and `tempUserStepsTable`.steps = `freezed_challenge_steps`.steps and `tempUserStepsTable`.distance = `freezed_challenge_steps`.distance and `tempUserStepsTable`.calories = `freezed_challenge_steps`.calories and `tempUserStepsTable`.log_date = `freezed_challenge_steps`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_steps` (challenge_id,user_id,tracker,steps,distance,calories,log_date) select * from tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable)) then
    insert into `challenge_user_exercise_history` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,points,start_date,end_date) select `tempUserExerciseTable`.tchID,`tempUserExerciseTable`.tUserId,`tempUserExerciseTable`.exercise_id,`tempUserExerciseTable`.tracker,`tempUserExerciseTable`.duration,`tempUserExerciseTable`.distance,`tempUserExerciseTable`.calories,(select `companyChUserPointListTable`.tpoint from companyChUserPointListTable where `companyChUserPointListTable`.tUserId = `tempUserExerciseTable`.tUserId limit 1),`tempUserExerciseTable`.start_date,`tempUserExerciseTable`.end_date from tempUserExerciseTable where NOT EXISTS ( select * from `freezed_challenge_exercise` where `freezed_challenge_exercise`.`challenge_id` = challengeId  and `tempUserExerciseTable`.tUserId = `freezed_challenge_exercise`.user_id and `tempUserExerciseTable`.tracker = `freezed_challenge_exercise`.tracker and `tempUserExerciseTable`.exercise_id = `freezed_challenge_exercise`.exercise_id and `tempUserExerciseTable`.duration = `freezed_challenge_exercise`.duration and `tempUserExerciseTable`.distance = `freezed_challenge_exercise`.distance and `tempUserExerciseTable`.calories = `freezed_challenge_exercise`.calories and `tempUserExerciseTable`.start_date = `freezed_challenge_exercise`.start_date and `tempUserExerciseTable`.end_date = `freezed_challenge_exercise`.end_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_exercise` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,start_date,end_date) select * from tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable)) then
    insert into `challenge_user_inspire_history` (challenge_id,user_id,meditation_track_id,duration_listened,points,log_date) select `tempUserInspireTable`.tchID, `tempUserInspireTable`.tUserId, `tempUserInspireTable`.meditation_track_id, `tempUserInspireTable`.duration_listened, (select `companyChUserPointListTable`.tpoint from companyChUserPointListTable where `companyChUserPointListTable`.tUserId = `tempUserInspireTable`.tUserId limit 1), `tempUserInspireTable`.log_date from tempUserInspireTable where NOT EXISTS ( select * from `freezed_challenge_inspire` where `freezed_challenge_inspire`.`challenge_id` = challengeId  and `tempUserInspireTable`.tUserId = `freezed_challenge_inspire`.user_id and `tempUserInspireTable`.meditation_track_id = `freezed_challenge_inspire`.meditation_track_id and `tempUserInspireTable`.duration_listened = `freezed_challenge_inspire`.duration_listened and `tempUserInspireTable`.log_date = `freezed_challenge_inspire`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_inspire` (challenge_id,user_id,meditation_track_id,duration_listened,log_date) select * from tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId;
END IF;

SET @rank1 = 0, @prev_val = NULL;

IF(EXISTS(SELECT * FROM companyChUserPointListTable where `companyChUserPointListTable`.tchID = challengeId)) then

    insert into `challenge_wise_user_ponits` (`challenge_id`,`user_id`,`team_id`,`rank`,`points`)  SELECT `companyChUserPointListTable`.tchID , `companyChUserPointListTable`.tUserId , `companyChUserPointListTable`.tUserTeamId , @rank1 := IF(@prev_val=`companyChUserPointListTable`.tpoint,@rank1,@rank1+1) AS `rank`, @prev_val := `companyChUserPointListTable`.tpoint AS tpoint FROM `companyChUserPointListTable` where `companyChUserPointListTable`.tchID = challengeId and `companyChUserPointListTable`.tUserId IS NOT NULL ORDER BY `companyChUserPointListTable`.tpoint DESC;


SET @rank1 = 0, @prev_val = NULL;

    insert into `challenge_wise_team_ponits` (`challenge_id`,`team_id`,`rank`,`points`) SELECT `teamAvg`.tchID , `teamAvg`.tUserTeamId , @rank1 := IF(@prev_val=`teamAvg`.teamAvgPoint,@rank1,@rank1+1) AS `rank`, @prev_val := `teamAvg`.teamAvgPoint AS teamAvgPoint FROM (select `companyChUserPointListTable`.tchID , `companyChUserPointListTable`.tUserTeamId , sum(`companyChUserPointListTable`.tpoint) as teamAvgPoint from `companyChUserPointListTable` where `companyChUserPointListTable`.tchID = challengeId  group by `companyChUserPointListTable`.tUserTeamId , `companyChUserPointListTable`.tchID ) as teamAvg where `teamAvg`.tchID = challengeId ORDER BY `teamAvg`.teamAvgPoint DESC;

END IF;

DELETE FROM `tempUserStepsTable` WHERE `tempUserStepsTable`.`tchID` = challengeId;

DELETE FROM `tempUserExerciseTable` WHERE `tempUserExerciseTable`.`tchID` = challengeId;

DELETE FROM `tempUserInspireTable` WHERE `tempUserInspireTable`.`tchID` = challengeId;

UPDATE challenges SET job_finished = 0 WHERE id = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

update `challenges` set `challenges`.`freezed_data_at` = report_run_date_time where `challenges`.`id` = challengeId;

END;

CREATE PROCEDURE `sp_team_challenge_pointcalculation0`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

DECLARE challenge_id bigint(11);

-- Delete table Data before insert
DELETE FROM `challenge_history` WHERE `challenge_history`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_participents` WHERE `freezed_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_team_challenge_participents` WHERE `freezed_team_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_steps` WHERE `freezed_challenge_steps`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_exercise` WHERE `freezed_challenge_exercise`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_inspire` WHERE `freezed_challenge_inspire`.`challenge_id` = challengeId;

DELETE FROM `teamChUserPointListTable` WHERE `teamChUserPointListTable`.`tchID` = challengeId;

DELETE FROM `challenge_wise_user_ponits` WHERE `challenge_wise_user_ponits`.`challenge_id` = challengeId;

DELETE FROM `challenge_wise_team_ponits` WHERE `challenge_wise_team_ponits`.`challenge_id` = challengeId;

END;

CREATE PROCEDURE `sp_team_challenge_pointcalculation1`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

DECLARE userList cursor for (select distinct users.id , users.timezone , challenge_participants.team_id from challenge_participants left join user_team  on challenge_participants.team_id = user_team.team_id left join users on user_team.user_id = users.id where challenge_participants.challenge_id = challengeId);

DECLARE challengeRuleList cursor for (select challenge_targets.short_name, challenge_rules.model_name , challenge_rules.uom , challenge_rules.model_id from challenge_rules inner join challenge_targets on challenge_targets.id = challenge_rules.challenge_target_id where challenge_rules.challenge_id = challengeId);

SET report_run_date_time = convert_tz(now(),@@session.time_zone,'UTC');
SET SESSION group_concat_max_len=4294967295;

select group_concat(distinct users.id) into participatedUserId  from challenge_participants inner join user_team  on challenge_participants.team_id = user_team.team_id inner join users on users.id = user_team.user_id where challenge_participants.challenge_id = challengeId;

SELECT
    id,
    creator_id,
    challenge_category_id,
    timezone,
    title,
    description,
    start_date,
    end_date
INTO challenge_id , challenge_creator_id , challenge_challenge_category_id , challenge_timezone , challenge_title , challenge_description , challenge_start_date , challenge_end_date FROM
    challenges
WHERE
    id = challengeId;


OPEN userList;
BEGIN
DECLARE userListFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET userListFlag = TRUE;

userList_loop:
LOOP

FETCH userList INTO userList_id, userList_timezone, userList_team_id;

IF userListFlag THEN
    LEAVE userList_loop;
END IF;
set userPoint = 0;

IF (userList_id IS NOT NULL) then

    SELECT
        CONVERT_TZ(challenge_start_date,
                appTimeZone,
                userList_timezone)
    INTO convert_start_date;

    SELECT
        CONVERT_TZ(challenge_end_date,
                appTimeZone,
                userList_timezone)
    INTO convert_end_date;


    set userPoint = individual_point_calculation(challengeId,userList_id,convert_start_date,convert_end_date,appTimeZone,userList_timezone,steps,distance,exercises_distance,exercises_duration,meditations);
-- select appTimeZone , userPoint;

END IF;

INSERT INTO teamChUserPointListTable VALUES (challengeId,userList_id,userList_team_id,userPoint,0);

END LOOP;
END;
CLOSE userList;

OPEN challengeRuleList;
BEGIN
DECLARE insertedStepsData boolean default false;
DECLARE challengeRuleFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET challengeRuleFlag = TRUE;

challengeRuleList_loop:
LOOP
FETCH challengeRuleList INTO rule_short_name, rule_model_name, rule_uom, rule_model_id;

IF challengeRuleFlag THEN
    LEAVE challengeRuleList_loop;
END IF;

IF (rule_short_name = 'distance' OR rule_short_name = 'steps') and insertedStepsData = false  THEN
    set insertedStepsData = true;

    insert into tempUserStepsTable (tchID,tUserId,tracker,steps,distance,calories,log_date) select challengeId , `user_step`.`user_id` , `user_step`.`tracker`, `user_step`.`steps`, `user_step`.`distance`, `user_step`.`calories` , `user_step`.`log_date` from `user_step` inner join `users` on `user_step`.`user_id` = `users`.`id` where find_in_set(`user_step`.`user_id`,participatedUserId) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);

ELSEIF (rule_short_name = 'exercises' and rule_model_name = 'Exercise') THEN

    insert into tempUserExerciseTable (tchID,tUserId,exercise_id,tracker,duration,distance,calories,start_date,end_date) select challengeId , `user_exercise`.`user_id`, `user_exercise`.`exercise_id` , `user_exercise`.`tracker`, `user_exercise`.`duration`, `user_exercise`.`distance`, `user_exercise`.`calories` , `user_exercise`.`start_date`, `user_exercise`.`end_date` from `user_exercise` inner join `users` on `user_exercise`.`user_id` = `users`.`id` where find_in_set(`user_exercise`.`user_id`,participatedUserId) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`) and `user_exercise`.`deleted_at` is NULL and `user_exercise`.`exercise_id` = rule_model_id  ;

ELSEIF rule_short_name = 'meditations' THEN

    insert into tempUserInspireTable (tchID,tUserId,meditation_track_id,duration_listened,log_date) select challengeId , `user_listened_tracks`.`user_id` , `user_listened_tracks`.`meditation_track_id`, `user_listened_tracks`.`duration_listened`, `user_listened_tracks`.`created_at` from `user_listened_tracks` inner join `users` on `user_listened_tracks`.`user_id` = `users`.`id` where find_in_set(`user_listened_tracks`.`user_id`,participatedUserId) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);
end IF;

END LOOP;
END;

CLOSE challengeRuleList;

END;

CREATE PROCEDURE `sp_team_challenge_pointcalculation2`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

insert into `challenge_history` (challenge_id,creator_id,challenge_category_id,challenge_type,timezone,title,description,start_date,end_date) select `challenges`.`id` , `challenges`.`creator_id`,`challenges`.`challenge_category_id`,`challenges`.`challenge_type`,`challenges`.`timezone`,`challenges`.`title`,`challenges`.`description`,`challenges`.`start_date`,`challenges`.`end_date` from `challenges`  where `challenges`.`id` = challengeId;

insert into `freezed_challenge_participents` (challenge_id,team_id,participant_name) select `challenge_participants`.`challenge_id`, `challenge_participants`.`team_id` , `teams`.`name` from `challenge_participants` inner join `teams` on `challenge_participants`.`team_id` = `teams`.`id` where `challenge_participants`.`challenge_id` = challengeId;

insert into `freezed_team_challenge_participents` (challenge_id,user_id,team_id,participant_name,timezone,challenge_type) select `challenge_participants`.`challenge_id`,`user_team`.`user_id`, `challenge_participants`.`team_id` , concat(`users`.`first_name`," ",`users`.`last_name`) , `users`.`timezone` , 'team'  from `challenge_participants` inner join `user_team` on `challenge_participants`.`team_id` = `user_team`.`team_id` inner join  `users` on `user_team`.`user_id` = `users`.`id` where `challenge_participants`.`challenge_id` = challengeId;

IF(EXISTS(SELECT * FROM tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId)) then
    insert into `challenge_user_steps_history` (challenge_id,user_id,tracker,steps,distance,calories,points,log_date) select `tempUserStepsTable`.tchID,`tempUserStepsTable`.tUserId,`tempUserStepsTable`.tracker,`tempUserStepsTable`.steps,`tempUserStepsTable`.distance,`tempUserStepsTable`.calories,(select `teamChUserPointListTable`.tpoint from teamChUserPointListTable where `teamChUserPointListTable`.tUserId = `tempUserStepsTable`.tUserId limit 1),`tempUserStepsTable`.log_date from tempUserStepsTable where NOT EXISTS ( select * from `freezed_challenge_steps` where `freezed_challenge_steps`.`challenge_id` = challengeId  and `tempUserStepsTable`.tUserId = `freezed_challenge_steps`.user_id and `tempUserStepsTable`.tracker = `freezed_challenge_steps`.tracker and `tempUserStepsTable`.steps = `freezed_challenge_steps`.steps and `tempUserStepsTable`.distance = `freezed_challenge_steps`.distance and `tempUserStepsTable`.calories = `freezed_challenge_steps`.calories and `tempUserStepsTable`.log_date = `freezed_challenge_steps`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_steps` (challenge_id,user_id,tracker,steps,distance,calories,log_date) select * from tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `challenge_user_exercise_history` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,points,start_date,end_date) select `tempUserExerciseTable`.tchID,`tempUserExerciseTable`.tUserId,`tempUserExerciseTable`.exercise_id,`tempUserExerciseTable`.tracker,`tempUserExerciseTable`.duration,`tempUserExerciseTable`.distance,`tempUserExerciseTable`.calories,(select `teamChUserPointListTable`.tpoint from teamChUserPointListTable where `teamChUserPointListTable`.tUserId = `tempUserExerciseTable`.tUserId limit 1),`tempUserExerciseTable`.start_date,`tempUserExerciseTable`.end_date from tempUserExerciseTable where NOT EXISTS ( select * from `freezed_challenge_exercise` where `freezed_challenge_exercise`.`challenge_id` = challengeId  and `tempUserExerciseTable`.tUserId = `freezed_challenge_exercise`.user_id and `tempUserExerciseTable`.tracker = `freezed_challenge_exercise`.tracker and `tempUserExerciseTable`.exercise_id = `freezed_challenge_exercise`.exercise_id and `tempUserExerciseTable`.duration = `freezed_challenge_exercise`.duration and `tempUserExerciseTable`.distance = `freezed_challenge_exercise`.distance and `tempUserExerciseTable`.calories = `freezed_challenge_exercise`.calories and `tempUserExerciseTable`.start_date = `freezed_challenge_exercise`.start_date and `tempUserExerciseTable`.end_date = `freezed_challenge_exercise`.end_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_exercise` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,start_date,end_date) select * from tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `challenge_user_inspire_history` (challenge_id,user_id,meditation_track_id,duration_listened,points,log_date) select `tempUserInspireTable`.tchID, `tempUserInspireTable`.tUserId, `tempUserInspireTable`.meditation_track_id, `tempUserInspireTable`.duration_listened, (select `teamChUserPointListTable`.tpoint from teamChUserPointListTable where `teamChUserPointListTable`.tUserId = `tempUserInspireTable`.tUserId limit 1), `tempUserInspireTable`.log_date from tempUserInspireTable where NOT EXISTS ( select * from `freezed_challenge_inspire` where `freezed_challenge_inspire`.`challenge_id` = challengeId  and `tempUserInspireTable`.tUserId = `freezed_challenge_inspire`.user_id and `tempUserInspireTable`.meditation_track_id = `freezed_challenge_inspire`.meditation_track_id and `tempUserInspireTable`.duration_listened = `freezed_challenge_inspire`.duration_listened and `tempUserInspireTable`.log_date = `freezed_challenge_inspire`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_inspire` (challenge_id,user_id,meditation_track_id,duration_listened,log_date) select * from tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId;
END IF;

SET @rank1 = 0, @prev_val = NULL;

IF(EXISTS(SELECT * FROM teamChUserPointListTable WHERE `teamChUserPointListTable`.`tchID` = challengeId)) then

    insert into `challenge_wise_user_ponits` (`challenge_id`,`user_id`,`team_id`,`rank`,`points`)  SELECT `teamChUserPointListTable`.tchID , `teamChUserPointListTable`.tUserId , `teamChUserPointListTable`.tUserTeamId , @rank1 := IF(@prev_val=`teamChUserPointListTable`.tpoint,@rank1,@rank1+1) AS `rank`, @prev_val := `teamChUserPointListTable`.tpoint AS tpoint FROM `teamChUserPointListTable` where `teamChUserPointListTable`.tchID = challengeId and `teamChUserPointListTable`.tUserId IS NOT NULL ORDER BY `teamChUserPointListTable`.tpoint DESC;


SET @rank1 = 0, @prev_val = NULL;

    insert into `challenge_wise_team_ponits` (`challenge_id`,`team_id`,`rank`,`points`) SELECT `teamAvg`.tchID , `teamAvg`.tUserTeamId , @rank1 := IF(@prev_val=`teamAvg`.teamAvgPoint,@rank1,@rank1+1) AS `rank`, @prev_val := `teamAvg`.teamAvgPoint AS teamAvgPoint FROM (select `teamChUserPointListTable`.tchID , `teamChUserPointListTable`.tUserTeamId , sum(`teamChUserPointListTable`.tpoint) / count(`teamChUserPointListTable`.tUserTeamId) as teamAvgPoint from `teamChUserPointListTable` where `teamChUserPointListTable`.tchID = challengeId  group by `teamChUserPointListTable`.tUserTeamId , `teamChUserPointListTable`.tchID ) as teamAvg where `teamAvg`.tchID = challengeId ORDER BY `teamAvg`.teamAvgPoint DESC;

END IF;

DELETE FROM `tempUserStepsTable` WHERE `tempUserStepsTable`.`tchID` = challengeId;

DELETE FROM `tempUserExerciseTable` WHERE `tempUserExerciseTable`.`tchID` = challengeId;

DELETE FROM `tempUserInspireTable` WHERE `tempUserInspireTable`.`tchID` = challengeId;

UPDATE challenges SET job_finished = 0 WHERE id = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

update `challenges` set `challenges`.`freezed_data_at` = report_run_date_time where `challenges`.`id` = challengeId;

END;

CREATE PROCEDURE `sp_individual_challenge_pointcalculation0`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

DECLARE challenge_id bigint(11);

-- Delete table Data before insert
DELETE FROM `challenge_history` WHERE `challenge_history`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_participents` WHERE `freezed_challenge_participents`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_steps` WHERE `freezed_challenge_steps`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_exercise` WHERE `freezed_challenge_exercise`.`challenge_id` = challengeId;

DELETE FROM `freezed_challenge_inspire` WHERE `freezed_challenge_inspire`.`challenge_id` = challengeId;

DELETE FROM `indUserPointListTable` WHERE `indUserPointListTable`.`tchID` = challengeId;

DELETE FROM `challenge_wise_user_ponits` WHERE `challenge_wise_user_ponits`.`challenge_id` = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

END;

CREATE PROCEDURE `sp_individual_challenge_pointcalculation1`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_id bigint(11);
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_name VARCHAR(50);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

DECLARE userList cursor for (select users.id , concat(users.first_name," ",users.last_name) as userName , users.timezone , user_team.team_id from challenge_participants inner join user_team  on challenge_participants.user_id = user_team.user_id inner join users on users.id = user_team.user_id where challenge_participants.challenge_id = challengeId and challenge_participants.status = 'Accepted');
DECLARE challengeRuleList cursor for (select challenge_targets.short_name, challenge_rules.model_name , challenge_rules.uom , challenge_rules.model_id from challenge_rules inner join challenge_targets on challenge_targets.id = challenge_rules.challenge_target_id where challenge_rules.challenge_id = challengeId);

SET report_run_date_time = convert_tz(now(),@@session.time_zone,'UTC');
SET SESSION group_concat_max_len=4294967295;

select group_concat(users.id) into participatedUserId  from users inner join challenge_participants on challenge_participants.user_id = users.id where challenge_participants.challenge_id = challengeId and challenge_participants.status = 'Accepted';

SELECT
    id,
    creator_id,
    challenge_category_id,
    timezone,
    title,
    description,
    start_date,
    end_date
INTO challenge_id , challenge_creator_id , challenge_challenge_category_id , challenge_timezone , challenge_title , challenge_description , challenge_start_date , challenge_end_date FROM
    challenges
WHERE
    id = challengeId;


OPEN userList;
BEGIN
DECLARE userListFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET userListFlag = TRUE;

userList_loop:
LOOP

FETCH userList INTO userList_id, userList_name, userList_timezone, userList_team_id;

IF userListFlag THEN
    LEAVE userList_loop;
END IF;

SELECT
    CONVERT_TZ(challenge_start_date,
            appTimeZone,
            userList_timezone)
INTO convert_start_date;

SELECT
    CONVERT_TZ(challenge_end_date,
            appTimeZone,
            userList_timezone)
INTO convert_end_date;

set userPoint = 0;
set userPoint = individual_point_calculation(challengeId,userList_id,convert_start_date,convert_end_date,appTimeZone,userList_timezone,steps,distance,exercises_distance,exercises_duration,meditations);
-- select appTimeZone , userPoint;

INSERT INTO indUserPointListTable VALUES (challengeId,userList_id,userList_team_id,userPoint,0);

END LOOP;
END;
CLOSE userList;

OPEN challengeRuleList;
BEGIN
DECLARE insertedStepsData boolean default false;
DECLARE challengeRuleFlag TINYINT DEFAULT FALSE;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET challengeRuleFlag = TRUE;

challengeRuleList_loop:
LOOP
FETCH challengeRuleList INTO rule_short_name, rule_model_name, rule_uom, rule_model_id;

IF challengeRuleFlag THEN
    LEAVE challengeRuleList_loop;
END IF;

IF (rule_short_name = 'distance' OR rule_short_name = 'steps') and insertedStepsData = false  THEN
    set insertedStepsData = true;

    insert into tempUserStepsTable (tchID,tUserId,tracker,steps,distance,calories,log_date) select challengeId , `user_step`.`user_id` , `user_step`.`tracker`, `user_step`.`steps`, `user_step`.`distance`, `user_step`.`calories` , `user_step`.`log_date` from `user_step` inner join `users` on `user_step`.`user_id` = `users`.`id` where find_in_set(`user_step`.`user_id`,participatedUserId) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`log_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);

ELSEIF (rule_short_name = 'exercises' and rule_model_name = 'Exercise') THEN

    insert into tempUserExerciseTable (tchID,tUserId,exercise_id,tracker,duration,distance,calories,start_date,end_date) select challengeId , `user_exercise`.`user_id`, `user_exercise`.`exercise_id` , `user_exercise`.`tracker`, `user_exercise`.`duration`, `user_exercise`.`distance`, `user_exercise`.`calories` , `user_exercise`.`start_date`, `user_exercise`.`end_date` from `user_exercise` inner join `users` on `user_exercise`.`user_id` = `users`.`id` where find_in_set(`user_exercise`.`user_id`,participatedUserId) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_exercise`.`start_date`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`) and `user_exercise`.`deleted_at` is NULL and `user_exercise`.`exercise_id` = rule_model_id  ;

ELSEIF rule_short_name = 'meditations' THEN

    insert into tempUserInspireTable (tchID,tUserId,meditation_track_id,duration_listened,log_date) select challengeId , `user_listened_tracks`.`user_id` , `user_listened_tracks`.`meditation_track_id`, `user_listened_tracks`.`duration_listened`, `user_listened_tracks`.`created_at` from `user_listened_tracks` inner join `users` on `user_listened_tracks`.`user_id` = `users`.`id` where find_in_set(`user_listened_tracks`.`user_id`,participatedUserId) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) >= CONVERT_TZ(challenge_start_date,appTimeZone,`users`.`timezone`) and CONVERT_TZ(`user_listened_tracks`.`created_at`,appTimeZone,`users`.`timezone`) <= CONVERT_TZ(challenge_end_date,appTimeZone,`users`.`timezone`);
end IF;

END LOOP;
END;

CLOSE challengeRuleList;
END;

CREATE PROCEDURE `sp_individual_challenge_pointcalculation2`(
    IN appTimeZone VARCHAR(50),
    IN challengeId bigint(11),
    IN steps bigint(11),
    IN distance bigint(11),
    IN exercises_distance bigint(11),
    IN exercises_duration bigint(11),
    IN meditations bigint(11)
)
BEGIN

declare participatedUserId text default "";
DECLARE challenge_creator_id bigint(11);
declare challenge_challenge_category_id bigint(11);
declare challenge_timezone varchar(50);
declare challenge_title varchar(200);
declare challenge_description text;
declare challenge_start_date datetime;
declare challenge_end_date datetime;
declare report_run_date_time datetime;

declare convert_start_date datetime;
declare convert_end_date datetime;
declare userPoint double;

declare userList_id bigint(11);
declare userList_team_id bigint(11);
declare userList_name VARCHAR(50);
declare userList_timezone VARCHAR(50);

declare rule_short_name varchar(50);
declare rule_model_name varchar(50);
declare rule_uom varchar(50);
declare rule_model_id bigint(11);

insert into `challenge_history` (challenge_id,creator_id,challenge_category_id,challenge_type,timezone,title,description,start_date,end_date) select `challenges`.`id` , `challenges`.`creator_id`,`challenges`.`challenge_category_id`,`challenges`.`challenge_type`,`challenges`.`timezone`,`challenges`.`title`,`challenges`.`description`,`challenges`.`start_date`,`challenges`.`end_date` from `challenges`  where `challenges`.`id` = challengeId;

insert into `freezed_challenge_participents` (challenge_id,user_id,participant_name) select `challenge_participants`.`challenge_id`, `challenge_participants`.`user_id` , concat(`users`.`first_name`," ",`users`.`last_name`) from `challenge_participants` inner join `users` on `challenge_participants`.`user_id` = `users`.`id` where `challenge_participants`.`challenge_id` = challengeId and `challenge_participants`.`status` = 'Accepted';

IF(EXISTS(SELECT * FROM tempUserStepsTable)) then
    insert into `challenge_user_steps_history` (challenge_id,user_id,tracker,steps,distance,calories,points,log_date) select `tempUserStepsTable`.tchID,`tempUserStepsTable`.tUserId,`tempUserStepsTable`.tracker,`tempUserStepsTable`.steps,`tempUserStepsTable`.distance,`tempUserStepsTable`.calories,(select `indUserPointListTable`.tpoint from indUserPointListTable where `indUserPointListTable`.tUserId = `tempUserStepsTable`.tUserId limit 1),`tempUserStepsTable`.log_date from tempUserStepsTable where NOT EXISTS ( select * from `freezed_challenge_steps` where `freezed_challenge_steps`.`challenge_id` = challengeId  and `tempUserStepsTable`.tUserId = `freezed_challenge_steps`.user_id and `tempUserStepsTable`.tracker = `freezed_challenge_steps`.tracker and `tempUserStepsTable`.steps = `freezed_challenge_steps`.steps and `tempUserStepsTable`.distance = `freezed_challenge_steps`.distance and `tempUserStepsTable`.calories = `freezed_challenge_steps`.calories and `tempUserStepsTable`.log_date = `freezed_challenge_steps`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_steps` (challenge_id,user_id,tracker,steps,distance,calories,log_date) select * from tempUserStepsTable WHERE `tempUserStepsTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `challenge_user_exercise_history` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,points,start_date,end_date) select `tempUserExerciseTable`.tchID,`tempUserExerciseTable`.tUserId,`tempUserExerciseTable`.exercise_id,`tempUserExerciseTable`.tracker,`tempUserExerciseTable`.duration,`tempUserExerciseTable`.distance,`tempUserExerciseTable`.calories,(select `indUserPointListTable`.tpoint from indUserPointListTable where `indUserPointListTable`.tUserId = `tempUserExerciseTable`.tUserId limit 1),`tempUserExerciseTable`.start_date,`tempUserExerciseTable`.end_date from tempUserExerciseTable where NOT EXISTS ( select * from `freezed_challenge_exercise` where `freezed_challenge_exercise`.`challenge_id` = challengeId  and `tempUserExerciseTable`.tUserId = `freezed_challenge_exercise`.user_id and `tempUserExerciseTable`.tracker = `freezed_challenge_exercise`.tracker and `tempUserExerciseTable`.exercise_id = `freezed_challenge_exercise`.exercise_id and `tempUserExerciseTable`.duration = `freezed_challenge_exercise`.duration and `tempUserExerciseTable`.distance = `freezed_challenge_exercise`.distance and `tempUserExerciseTable`.calories = `freezed_challenge_exercise`.calories and `tempUserExerciseTable`.start_date = `freezed_challenge_exercise`.start_date and `tempUserExerciseTable`.end_date = `freezed_challenge_exercise`.end_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_exercise` (challenge_id,user_id,exercise_id,tracker,duration,distance,calories,start_date,end_date) select * from tempUserExerciseTable WHERE `tempUserExerciseTable`.`tchID` = challengeId;
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `challenge_user_inspire_history` (challenge_id,user_id,meditation_track_id,duration_listened,points,log_date) select `tempUserInspireTable`.tchID, `tempUserInspireTable`.tUserId, `tempUserInspireTable`.meditation_track_id, `tempUserInspireTable`.duration_listened, (select `indUserPointListTable`.tpoint from indUserPointListTable where `indUserPointListTable`.tUserId = `tempUserInspireTable`.tUserId limit 1), `tempUserInspireTable`.log_date from tempUserInspireTable where NOT EXISTS ( select * from `freezed_challenge_inspire` where `freezed_challenge_inspire`.`challenge_id` = challengeId  and `tempUserInspireTable`.tUserId = `freezed_challenge_inspire`.user_id and `tempUserInspireTable`.meditation_track_id = `freezed_challenge_inspire`.meditation_track_id and `tempUserInspireTable`.duration_listened = `freezed_challenge_inspire`.duration_listened and `tempUserInspireTable`.log_date = `freezed_challenge_inspire`.log_date );
END IF;

IF(EXISTS(SELECT * FROM tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId)) then
    insert into `freezed_challenge_inspire` (challenge_id,user_id,meditation_track_id,duration_listened,log_date) select * from tempUserInspireTable WHERE `tempUserInspireTable`.`tchID` = challengeId;
END IF;

SET @rank1 = 0, @prev_val = NULL;

IF(EXISTS(SELECT * FROM indUserPointListTable WHERE `indUserPointListTable`.`tchID` = challengeId)) then

    insert into `challenge_wise_user_ponits` (`challenge_id`,`user_id`,`team_id`,`rank`,`points`)  SELECT `indUserPointListTable`.tchID , `indUserPointListTable`.tUserId , `indUserPointListTable`.tUserTeamId , @rank1 := IF(@prev_val=`indUserPointListTable`.tpoint,@rank1,@rank1+1) AS `rank`, @prev_val := `indUserPointListTable`.tpoint AS tpoint FROM `indUserPointListTable` where `indUserPointListTable`.tchID = challengeId ORDER BY `indUserPointListTable`.tpoint DESC;

END IF;

DELETE FROM `tempUserStepsTable` WHERE `tempUserStepsTable`.`tchID` = challengeId;

DELETE FROM `tempUserExerciseTable` WHERE `tempUserExerciseTable`.`tchID` = challengeId;

DELETE FROM `tempUserInspireTable` WHERE `tempUserInspireTable`.`tchID` = challengeId;

UPDATE challenges SET job_finished = 0 WHERE id = challengeId;

SET @rank1 = NULL;
SET @prev_val = NULL;

update `challenges` set `challenges`.`freezed_data_at` = report_run_date_time where `challenges`.`id` = challengeId;

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
