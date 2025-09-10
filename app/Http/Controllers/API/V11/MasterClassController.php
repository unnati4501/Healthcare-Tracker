<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Collections\V11\CategoryWiseMasterClassCollection;
use App\Http\Collections\V11\MasterClassSurveyCollection;
use App\Http\Controllers\API\V10\MasterClassController as v10MasterClassController;
use App\Http\Resources\V11\MasterClassDetailsResource;
use App\Models\Course;
use App\Models\CourseSurveyQuestions;
use App\Models\MasterClassComapany;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v10MasterClassController
{
    /**
     * API to fetch masterclasses by category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryMasterClass(Request $request, SubCategory $subcategory)
    {
        try {
            $user      = $this->user();
            $company   = $user->company()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $categoryWiseCourseData = Course::where("sub_category_id", $subcategory->id)
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id", DB::raw("(SELECT id FROM user_course WHERE course_id = courses.id and user_id = " . $user->id . " and joined = 1 limit 1) as isEnrolled"))
                    ->where("courses.status", true)
                    ->orderBy('isEnrolled', 'DESC')
                    ->orderBy('courses.created_at', 'DESC');
            } else {
                $categoryWiseCourseData = Course::where("sub_category_id", $subcategory->id)
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id")
                    ->where("courses.status", true)
                    ->orderBy('courses.created_at', 'DESC');
            }

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
    /**
     * API to save get details of masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function masterClassDetail(Request $request, Course $course)
    {
        try {
            if (!$course->status) {
                return $this->notFoundResponse('Masterclass is not published yet.');
            }

            $role      = getUserRole();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $company   = $this->user()->company()->first();

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $checkRecords = $course->masterclassCompany()->where('company_id', $company->id)->count();

                if ($checkRecords <= 0) {
                    return $this->notFoundResponse('Masterclass not found');
                }
            }

            $isComapanyExist = MasterClassComapany::where(['masterclass_id' => $course->id, 'company_id' => $company->id])->count();
            if (empty($isComapanyExist)) {
                return $this->notFoundResponse("Masterclass isn't published for your company or has been removed.");
            }

            $data = array("data" => new MasterClassDetailsResource($course));
            return $this->successResponse($data, 'Course Info retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function surveyMasterClass(Request $request, $type, Course $course)
    {
        try {
            // logged-in user
            $user = $this->user();

            $surveyQuestion = CourseSurveyQuestions::join("course_survey", "course_survey.id", "=", "course_survey_questions.survey_id")
                ->where("course_survey_questions.status", true)
                ->where("course_survey.status", true)
                ->where("course_survey.course_id", $course->id)
                ->where("course_survey.type", $type)
                ->select("course_survey_questions.*", "course_survey.id as surveyId", "course_survey.title as surveyTitle")
                ->get();

            if ($surveyQuestion->count() > 0) {
                $data              = [];
                $data['surveyId']  = $surveyQuestion[0]->surveyId;
                $data['title']     = $surveyQuestion[0]->surveyTitle;
                $data['questions'] = new MasterClassSurveyCollection($surveyQuestion);

                return $this->successResponse(['data' => $data], 'Survey listed successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
