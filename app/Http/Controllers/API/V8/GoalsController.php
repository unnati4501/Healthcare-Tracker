<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Collections\V8\GoalTagCollection;
use App\Http\Controllers\Controller;
use App\Http\Traits\ServesApiTrait;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use App\Http\Traits\ProvidesAuthGuardTrait;

class GoalsController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Returns onboarding slides
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            $goalObj = new Goal();
            $goalRecords = $goalObj->getAssociatedGoalTags();

            return $this->successResponse(
                ($goalRecords->count() > 0) ? ['data' => new GoalTagCollection($goalRecords, $userSelectedGoal) ] : ['data' => []],
                ($goalRecords->count() > 0) ? 'Goal list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
