<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentChallenge;
use App\Models\ContentChallengeActivity;
use Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Admin\UpdateContentChallengeRequest;

/**
 * Class ContentChallengeController
 *
 * @package App\Http\Controllers\Admin
 */
class ContentChallengeController extends Controller {
	/**
	 * variable to store the model object
	 * @var ContentChallenge
	 */
	protected $model;

	/**
	 * variable to store the model object
	 * @var ContentChallengeActivity
	 */
	protected $contentChallengeActivity;

	/**
	 * contructor to initialize model object
	 * @param Category $model, ContentChallengeActivity $contentChallengeActivity
	 */
	public function __construct(ContentChallenge $model, ContentChallengeActivity $contentChallengeActivity) {
		$this->model = $model;
		$this->contentChallengeActivity = $contentChallengeActivity;
		$this->bindBreadcrumbs();
	}

	/**
	 * bind breadcrumbs of categories module
	 */
	private function bindBreadcrumbs() {
		Breadcrumbs::for ('contentChallenge.index', function ($trail) {
			$trail->push('Home', route('dashboard'));
			$trail->push(trans('contentChallenge.breadcrumbs.index'));
		});
		Breadcrumbs::for ('contentChallengeActivity.index', function ($trail, $category) {
			$trail->push('Home', route('dashboard'));
			$trail->push(trans('contentChallenge.breadcrumbs.index'), route('admin.contentChallenge.index'));
			$trail->push(trans('contentChallenge.activities.breadcrumbs.index'), route('admin.contentChallengeActivity.index', $category));
		});
	}

	/**
	 * @return View
	 */
	public function index(Request $request) {
		if (!access()->allow('manage-content-challenge')) {
			abort(403);
		}
		try {
			$data = array();
			$data['pagination'] = config('zevolifesettings.datatable.pagination.short');
			$data['ga_title']   = trans('page_title.contentChallenge.list');
			return \view('admin.contentChallenge.index', $data);
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data'   => trans('contentChallenge.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return \Redirect::route('admin.contentChallenge.index')->with('message', $messageData);
		}
	}

	/**
	 * Get the list of categories for content challenge
	 * @param Request $request
	 * @return Datatable
	 */
	public function getCategories(Request $request) {
		if (!access()->allow('manage-content-challenge')) {
			abort(403);
		}
		try {
			return $this->model->getTableData($request->all());
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data' => trans('contentChallenge.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return response()->json($messageData);
		}
	}

	/**
	 * * Get the content challenge activities list html
	 * @param Category $category
	 * @return View
	 */
	public function indexActivities(ContentChallenge $contentChallenge) {
		if (!access()->allow('manage-content-challenge')) {
			abort(403);
		}
		try {
			$data = array();
			$data['pagination']         = config('zevolifesettings.datatable.pagination.short');
			$data['ga_title']           = trans('page_title.contentChallengeActivity.list', ['activity' => $contentChallenge->category]);
			$data['challengeCategory']  = $contentChallenge;
			return \view('admin.contentChallenge.activities.index', $data);
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data' => trans('contentChallenge.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return \Redirect::route('admin.contentChallenge.index')->with('message', $messageData);
		}
	}

	/**
	 * Get the content challenge activities list
	 * @param Request $request
	 * @return Datatable
	 */
	public function getContentChallengeActivities(Request $request) {
		if (!access()->allow('manage-content-challenge')) {
			abort(403);
		}
		try {
			return $this->contentChallengeActivity->getTableData($request->all());
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data' => trans('contentChallenge.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return response()->json($messageData);
		}
	}

	/**
	 * Update content challenge points
	 * @param Request $request
	 * @return Datatable
	 */
	public function updateActivity(Request $request) {
		if (!access()->allow('update-content-challenge')) {
			abort(403);
		}
		try {
			if (!empty($request)) {
				$this->contentChallengeActivity->where('id', $request->activity_id)
					->update(['daily_limit' => $request->daily_limit, 'points_per_action' => $request->points_per_action]);
				return $messageData = [
					'status' => 1,
					'data' => trans('contentChallenge.activities.messages.activity_updated'),
				];
			}
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data' => trans('contentChallenge.activities.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return response()->json($messageData);
		}
	}

	/**
	 * Update content challenge Description
	 * @param Request $request
	 * @return Datatable
	 */
	public function updateContentChallenge(UpdateContentChallengeRequest $request, ContentChallenge $contentChallenge) {
		if (!access()->allow('manage-content-challenge')) {
			abort(403);
		}
		try {
			$data = $contentChallenge->update([
				'description' => $request->description
			]);
			if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('contentChallenge.messages.description_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.contentChallenge.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('contentChallenge.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.contentChallenge.index')->with('message', $messageData);
            }
		} catch (\Exception $exception) {
			report($exception);
			$messageData = [
				'data' => trans('contentChallenge.activities.messages.something_wrong_try_again'),
				'status' => 0,
			];
			return response()->json($messageData);
		}
	}
}
