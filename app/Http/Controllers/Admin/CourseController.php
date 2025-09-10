<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseWeekRequest;
use App\Http\Requests\Admin\CreateCourseLessionRequest;
use App\Http\Requests\Admin\CreateCourseRequest;
use App\Http\Requests\Admin\EditCourseLessionRequest;
use App\Http\Requests\Admin\EditCourseRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\CourseSurvey;
use App\Models\CourseWeek;
use App\Models\DepartmentLocation;
use App\Models\Goal;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;

/**
 * Class CourseController
 *
 * @package App\Http\Controllers\Admin
 */
class CourseController extends Controller
{
    /**
     * variable to store the model object
     * @var Course
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Course $model ;
     */
    public function __construct(Course $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('course.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses');
        });
        Breadcrumbs::for('course.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses', route('admin.masterclass.index'));
            $trail->push('Add Masterclasses');
        });
        Breadcrumbs::for('course.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses', route('admin.masterclass.index'));
            $trail->push('Edit Masterclasses');
        });

        // lessons
        Breadcrumbs::for('course.lesson.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('classes', route('admin.masterclass.index'));
            $trail->push('Lessons');
        });
        Breadcrumbs::for('course.lesson.create', function ($trail, $courseId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses', route('admin.masterclass.index'));
            $trail->push('Lessons', route('admin.masterclass.manageLessions', $courseId));
            $trail->push('Add Lesson');
        });
        Breadcrumbs::for('course.lesson.edit', function ($trail, $courseId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses', route('admin.masterclass.index'));
            $trail->push('Lessons', route('admin.masterclass.manageLessions', $courseId));
            $trail->push('Edit Lesson');
        });

        // survey
        Breadcrumbs::for('course.survey.edit', function ($trail, $courseId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Masterclasses', route('admin.masterclass.index'));
            $trail->push('Lessons', route('admin.masterclass.manageLessions', $courseId));
            $trail->push('Edit Survey');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                  = array();
            $data['subcategories'] = SubCategory::where('status', 1)->where("category_id", 1)->pluck('name', 'id')->toArray();
            $data['pagination']    = config('zevolifesettings.datatable.pagination.long');
            $data['roleGroup']     = $role->group;
            $tags                  = CategoryTags::where("category_id", 1)->pluck('name', 'id')->toArray();
            $data['tags']          = array_replace(['NA' => 'NA'], $tags);
            $data['ga_title']      = trans('page_title.masterclass.masterclass_list');
            return \view('admin.course.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-course') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                  = array();
            $data['ga_title']      = trans('page_title.masterclass.create');
            $data['companies']     = $this->getAllCompaniesGroupType();
            $data['subcategories'] = SubCategory::where('status', 1)->where("category_id", 1)->pluck('name', 'id')->toArray();
            $data['goalTags']      = Goal::pluck('title', 'id')->toArray();
            $healthcoach           = User::select(\DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')
                ->where(["is_coach" => 1, 'is_blocked' => 0])
                ->pluck('name', 'id')
                ->toArray();
            $data['healthcoach'] = array_replace([1 => 'Zevo Admin'], $healthcoach);
            $data['tags']        = CategoryTags::where("category_id", 1)->pluck('name', 'id')->toArray();
            return \view('admin.course.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateCourseRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateCourseRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-course') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Course added successfully", $logData);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('labels.course.data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param Request $request ,Course $course
     * @return View
     */
    public function edit(Request $request, Course $course)
    {
        $role = getUserRole();
        if (!access()->allow('update-course') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data              = array();
            $data              = $course->courseEditData();
            $data['companies'] = $this->getAllCompaniesGroupType();
            $data['ga_title']  = trans('page_title.masterclass.edit');
            return \view('admin.course.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditCourseRequest $request ,Course $course
     *
     * @return RedirectResponse
     */
    public function update(EditCourseRequest $request, Course $course)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-course') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $course->toArray());
            $data = $course->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Course updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('labels.course.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getCourses(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    public function view(Course $course)
    {
        $role = getUserRole();
        if (!access()->allow('view-course') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $lessons = $course->courselessions()->orderBy('order_priority')->get();
            $data    = [
                'course'        => $course,
                'creator_data'  => $course->getCreatorData(),
                'pre_survey'    => $course->courseSurvey()->where('type', 'pre')->first(),
                'lessons'       => $lessons,
                'total_lessons' => $lessons->count(),
                'post_survey'   => $course->courseSurvey()->where('type', 'post')->first(),
                'ga_title'      => trans('page_title.masterclass.view', ["masterclass_name" => $course->title]),
            ];

            return \view('admin.course.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Course $course
     *
     * @return RedirectResponse
     */

    public function delete(Course $course)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-course') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_course_id' => $course->id,'deleted_course_name' => $course->title]);
            $this->auditLogRepository->created("Course deleted successfully", $logs);

            return $course->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Course $course
     *
     * @return View
     */

    public function getDetails(Course $course)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            $data               = array();
            $data['courseData'] = $course;
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');

            return \view('admin.course.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function deleteCourseLessionMedia(CourseLession $courseLession, $type = '')
    {
        try {
            return $courseLession->deleteMediaRecord($type);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function manageModules(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-modules') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['course']     = $course;
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');

            return \view('admin.course.week.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getCourseModules(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-modules') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $course->getModuleTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function createModule(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-modules') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data           = array();
            $data['course'] = $course;

            return \view('admin.course.week.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $course)->with('message', $messageData);
        }
    }

    /**
     * @param CourseWeekRequest $request
     *
     * @return RedirectResponse
     */
    public function storeModule(Course $course, CourseWeekRequest $request)
    {
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data    = $course->storeModuleEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.course.module_data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.courses.manageModules', $course)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.courses.manageModules', $course)->with('message', $messageData);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->withInput()->withErrors("Invalid youtube link");
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $course)->with('message', $messageData);
        }
    }

    /**
     * @param CourseWeek $courseWeek
     *
     * @return RedirectResponse
     */
    public function editModule(CourseWeek $courseWeek)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-modules') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data           = array();
            $data['record'] = $courseWeek;

            return \view('admin.course.week.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
        }
    }

    /**
     * @param CourseWeekRequest $request
     *
     * @return RedirectResponse
     */
    public function updateModule(CourseWeek $courseWeek, CourseWeekRequest $request)
    {
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data    = $courseWeek->updateEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.course.module_data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->withInput()->withErrors("Invalid youtube link");
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
        }
    }

    /**
     * @param  Course $course
     *
     * @return RedirectResponse
     */

    public function deleteModule(CourseWeek $courseWeek)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-modules') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $courseWeek->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function manageLessions(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            $data                               = array();
            $survey_count                       = $course->courseSurvey()->count();
            $data['course']                     = $course;
            $data['allow_add_survey_button']    = ($survey_count == 0);
            $data['allow_remove_survey_button'] = ($course->status == 0 && $survey_count > 0);
            $data['pagination']                 = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']                   = trans('page_title.masterclass.lessions.lessions_list') . $course->title;
            return view('admin.course.lession.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getLessions(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            return $course->getLessionTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function createLession(Course $course, CourseWeek $courseWeek, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data               = array();
            $data['course']     = $course;
            $data['courseWeek'] = $courseWeek;
            $data['ga_title']   = trans('page_title.masterclass.lessions.create');
            $lessionType        = config('zevolifesettings.masterclass_lesson_type');
            $nowInUTC           = now(config('app.timezone'))->toDateTimeString();
            $companies          = Company::select('id')->where('subscription_start_date', '<=', $nowInUTC)
                ->where('subscription_end_date', '>=', $nowInUTC)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNull('parent_id')
                            ->where('is_reseller', true);
                    })->orWhere(function ($query) {
                        $query->whereNotNull('parent_id')
                            ->where('is_reseller', false);
                    });
                })
                ->get()
                ->pluck('id')
                ->toArray();
            $checkResellerCompany = DB::table('masterclass_company')
                ->where('masterclass_id', $course->id)
                ->whereIn('company_id', $companies)->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            if (!empty($checkResellerCompany)) {
                unset($lessionType[3]);
            }
            $data['lessionType'] = $lessionType;
            return \view('admin.course.lession.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', [$course, $courseWeek])->with('message', $messageData);
        }
    }

    /**
     * @param CreateCourseLessionRequest $request
     *
     * @return RedirectResponse
     */
    public function storeLession(Course $course, CreateCourseLessionRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $course->storeLessionEntity($request->all());
            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('labels.course.lession_data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => 'Invalid youtube link',
            ], 422);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param CourseLession $courseLession
     *
     * @return RedirectResponse
     */
    public function editLession(CourseLession $courseLession, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                = array();
            $data['record']      = $courseLession;
            $data['request']     = $request->all();
            $data['ga_title']    = trans('page_title.masterclass.lessions.edit');
            $data['lessionType'] = config('zevolifesettings.masterclass_lesson_type');
            return \view('admin.course.lession.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', [$courseLession->course_id, $courseLession->course_week_id])->with('message', $messageData);
        }
    }

    /**
     * @param CourseWeekRequest $request
     *
     * @return RedirectResponse
     */
    public function updateLession(CourseLession $courseLession, EditCourseLessionRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $courseLession->updateLessionEntity($request->all());
            if ($data) {
                \DB::commit();
                $returnUrl = route('admin.masterclass.manageLessions', [$courseLession->course_id]);
                if (!$request->has('referrer')) {
                    \Session::put('message', [
                        'data'   => trans('labels.course.lession_data_update_success'),
                        'status' => 1,
                    ]);
                } else {
                    $returnUrl = route('admin.masterclass.view', [$courseLession->course_id, '#' . $courseLession->getKey()]);
                }
                return response()->json([
                    'status' => 1,
                    'url'    => $returnUrl,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 422);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => 'Invalid youtube link',
            ], 422);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }

    /**
     * @param  Course $course
     *
     * @return RedirectResponse
     */

    public function deleteLession(CourseLession $courseLession)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $courseLession->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', [$courseLession->course_id, $courseLession->course_week_id])->with('message', $messageData);
        }
    }

    public function publishCourse(Course $course, Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $course->publishCourse($request->all());
            \DB::commit();

            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.index')->with('message', $messageData);
        }
    }

    public function publishCourseModule(CourseWeek $courseWeek)
    {
        try {
            \DB::beginTransaction();
            $data = array();
            if ($courseWeek->status) {
                $data['published'] = false;
                $data['message']   = "Course module already published.";
            } else {
                $data = $courseWeek->publishCourseModule();
            }
            \DB::commit();

            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.courses.manageModules', $courseWeek->course_id)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getServeys(Course $course, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            return $course->getSurveyTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Course $course
     *
     * @return RedirectResponse
     */
    public function addSurveys(Course $course)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        if ($course->status) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data          = [];
            $isSurveyAdded = $course->courseSurvey()->count();
            if ($isSurveyAdded == 0) {
                $data = $course->addDefaultSurveys();
                if ($data) {
                    \DB::commit();
                    $data = [
                        'data'   => 'Surveys has been added successfully!',
                        'status' => 1,
                    ];
                } else {
                    \DB::rollback();
                    $data = [
                        'data'   => 'Failed to add surveys! Try again.',
                        'status' => 0,
                    ];
                }
            } else {
                $data = [
                    'data'   => 'Surveys are already exist!',
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.masterclass.manageLessions', $course->getKey())->with('message', $data);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $data = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', $course->getKey())->with('message', $data);
        }
    }

    /**
     * @param  Course $course
     *
     * @return RedirectResponse
     */

    public function deleteSurveys(Course $course)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        if ($course->status) {
            abort(403);
        }

        try {
            $data    = ['deleted' => 'error'];
            $deleted = $course->deleteSurveys();
            if ($deleted) {
                \Session::put('message', [
                    'data'   => trans('labels.course.survey_deleted'),
                    'status' => 1,
                ]);
                $data['deleted'] = true;
            }
            return $data;
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param CourseSurvey $coursesurvey
     *
     * @return RedirectResponse
     */
    public function editSurvey(CourseSurvey $coursesurvey)
    {
        try {
            $role = getUserRole();
            if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
                return view('errors.401');
            }

            $course = $coursesurvey->surveyCourse()->select('courses.id', 'courses.status')->first();

            $has_question = $coursesurvey->surveyQuestions();
            $data         = [
                'record'           => $coursesurvey,
                'question_types'   => config('zevolifesettings.masterclass_survey_question_type'),
                'surveys_score'    => config('zevolifesettings.masterclass_surveys_score'),
                'questions_count'  => $has_question->count(),
                'survey_questions' => $has_question->get(),
                'ga_title'         => trans('page_title.masterclass.surveydetails.edit_survey') . "(" . ucfirst($coursesurvey->type) . "Survey)",
                'courseStatus'     => $course->status,
            ];

            return \view('admin.course.survey.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', $coursesurvey->course_id)->with('message', $messageData);
        }
    }

    public function updateSurvey(CourseSurvey $coursesurvey, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data = $coursesurvey->updateSurvey($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.course.survey_data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.masterclass.manageLessions', $coursesurvey->course_id)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', $coursesurvey->course_id)->with('message', $messageData);
        }
    }

    public function publishLesson(CourseLession $courseLession)
    {
        try {
            \DB::beginTransaction();
            $data = array();
            if ($courseLession->status) {
                $data['published'] = false;
                $data['message']   = "Lesson has already published.";
            } else {
                $data = $courseLession->publishLession();
            }
            \DB::commit();

            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return [
                'message'   => trans('labels.common_title.something_wrong'),
                'published' => false,
            ];
        }
    }

    public function viewSurvey(CourseSurvey $coursesurvey)
    {
        try {
            $role = getUserRole();
            if (!access()->allow('manage-course-lesson') || $role->group != 'zevo') {
                return view('errors.401');
            }

            $course = $coursesurvey->surveyCourse()->select('courses.id', 'courses.status')->first();
            if (!$course->status) {
                return view('errors.401');
            }

            $has_question = $coursesurvey->surveyQuestions();
            $data         = [
                'record'           => $coursesurvey,
                'question_types'   => config('zevolifesettings.masterclass_survey_question_type'),
                'surveys_score'    => config('zevolifesettings.masterclass_surveys_score'),
                'questions_count'  => $has_question->count(),
                'survey_questions' => $has_question->get(),
                'ga_title'         => trans('page_title.masterclass.surveydetails.view_surveydetails') . "(" . ucfirst($coursesurvey->type) . " Survey)",
                'courseStatus'     => $course->status,
            ];

            return \view('admin.course.survey.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.masterclass.manageLessions', $coursesurvey->course_id)->with('message', $messageData);
        }
    }

    public function reorderingLesson(Course $course, Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = [
                'status'  => false,
                'message' => '',
            ];
            $positions = $request->input('positions', []);

            if (!empty($positions)) {
                $updated = $course->reorderingLesson($positions);
                if ($updated) {
                    $data['status']  = true;
                    $data['message'] = 'Order has been updated successfully';
                } else {
                    $data['message'] = 'Failed to update order, Please try again!';
                }
            } else {
                $data['message'] = 'Nothing to change the order';
            }

            (($data['status']) ? \DB::commit() : \DB::rollback());
            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return [
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong'),
            ];
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType()
    {
        $groupType        = config('zevolifesettings.content_company_group_type');
        $companyGroupType = [];
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        foreach ($groupType as $value) {
            switch ($value) {
                case 'Zevo':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
                    break;
                case 'Parent':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', true)
                        ->get()
                        ->toArray();
                    break;
                case 'Child':
                    $companies      = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNotNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
                    break;
            }

            if (count($companies) > 0) {
                foreach ($companies as $item) {
                    $diff         = $now->diffInHours($item['subscription_end_date'], false);
                    $startDayDiff = $now->diffInHours($item['subscription_start_date'], false);
                    $days         = (int) ceil($diff / 24);

                    if ($startDayDiff > 0) {
                        $planStatus = 'Inactive';
                    } elseif ($days <= 0) {
                        $planStatus = 'Expired';
                    } else {
                        $planStatus = 'Active';
                    }

                    $companyLocation = CompanyLocation::where('company_id', $item['id'])->select('id', 'name')->get()->toArray();

                    $locationArray = [];
                    foreach ($companyLocation as $locationItem) {
                        $departmentArray   = [];
                        $departmentRecords = DepartmentLocation::join('departments', 'departments.id', '=', 'department_location.department_id')->where('department_location.company_location_id', $locationItem['id'])->where('department_location.company_id', $item['id'])->select('departments.id', 'departments.name')->get()->toArray();

                        foreach ($departmentRecords as $departmentItem) {
                            $teamArray   = [];
                            $teamRecords = TeamLocation::join('teams', 'teams.id', '=', 'team_location.team_id')->where('team_location.department_id', $departmentItem['id'])->where('team_location.company_id', $item['id'])->where('team_location.company_location_id', $locationItem['id'])->select('teams.id', 'teams.name')->get()->toArray();

                            foreach ($teamRecords as $teamItem) {
                                $teamArray[] = [
                                    'id'   => $teamItem['id'],
                                    'name' => $teamItem['name'],
                                ];
                            }

                            if (!empty($teamArray)) {
                                $departmentArray[] = [
                                    'departmentName' => $departmentItem['name'],
                                    'team'           => $teamArray,
                                ];
                            }
                        }

                        $locationArray[] = [
                            'locationName' => $locationItem['name'],
                            'department'   => $departmentArray,
                        ];
                    }

                    $plucked[$value][$item['id']] = [
                        'companyName' => $item['name'] . ' - ' . $planStatus,
                        'location'    => $locationArray,
                    ];
                }
                $companyGroupType[] = [
                    'roleType'  => $value,
                    'companies' => $plucked[$value],
                ];
            }
        }
        return $companyGroupType;
    }
}
