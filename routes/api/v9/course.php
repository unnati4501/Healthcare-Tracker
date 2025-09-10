<?php
declare (strict_types = 1);

Route::get('home-course-panel', [
    'as'   => '.home-course-panel',
    'uses' => 'CourseController@homeCoursePanel',
]);

Route::get('running-content/{type}', [
    'as'   => '.running-content',
    'uses' => 'CourseController@runningContent',
]);

Route::get('ongoing', [
    'as'   => '.ongoing',
    'uses' => 'CourseController@ongoingCourses',
]);

Route::get('not-started', [
    'as'   => '.not-started',
    'uses' => 'CourseController@notStarted',
]);

Route::get('completed', [
    'as'   => '.completed',
    'uses' => 'CourseController@completed',
]);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'CourseController@saved',
]);
////
Route::get('category-courses/{subcategory}/{type?}', [
    'as'   => '.category-courses',
    'uses' => 'CourseController@categoryCourses',
]);

Route::get('course-coach-list/{subcategory}', [
    'as'   => '.course-coach-list',
    'uses' => 'CourseController@courseCoachList',
]);

Route::put('save-unsave-course/{course}', [
    'as'   => '.save-unsave-course',
    'uses' => 'CourseController@saveUnsaveCourse',
]);

Route::put('like-unlike-course/{course}', [
    'as'   => '.like-unlike-course',
    'uses' => 'CourseController@likeUnlikeCourse',
]);

Route::post('join-course/{course}', [
    'as'   => '.join-course',
    'uses' => 'CourseController@join',
]);

Route::post('reviews', [
    'as'   => '.reviews',
    'uses' => 'CourseController@reviews',
]);

Route::get('ratings/{course}', [
    'as'   => '.ratings',
    'uses' => 'CourseController@ratings',
]);

Route::get('review-list/{id}/{type}', [
    'as'   => '.review-list',
    'uses' => 'CourseController@reviewsList',
]);

Route::get('details/{course}', [
    'as'   => '.details',
    'uses' => 'CourseController@details',
]);

Route::get('benefit-instruction/{course}', [
    'as'   => '.benefit-instruction',
    'uses' => 'CourseController@benefitInstruction',
]);

Route::get('coach-detail-by-course/{course}', [
    'as'   => '.coach-detail-by-course',
    'uses' => 'CourseController@coachDetailsByCourse',
]);

Route::post('unlock-lesson/{courseLession}', [
    'as'   => '.unblock-lesson',
    'uses' => 'CourseController@unblockLesson',
]);

Route::get('lesson-details/{lesson}', [
    'as'   => '.lesson-details',
    'uses' => 'CourseController@lessonDetails',
]);

Route::get('coach-detail/{user}', [
    'as'   => '.coach-detail',
    'uses' => 'CourseController@coachDetail',
]);

Route::put('follow-unfollow-coach/{user}', [
    'as'   => '.follow-unfollow-coach',
    'uses' => 'CourseController@followUnfollowCoach',
]);

Route::get('coach-courses/{user}', [
    'as'   => '.coach-courses',
    'uses' => 'CourseController@coachCourses',
]);

Route::post('lesson-status-change/{courseLession}/{status}', [
    'as'   => '.lesson-status-change',
    'uses' => 'CourseController@lessonStatusChange',
]);

Route::delete('/remove-course-coach-reviews/{id}/{type}', [
    'as'   => '.remove-course-coach-reviews',
    'uses' => 'CourseController@removeReviews',
]);
