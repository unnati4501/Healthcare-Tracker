<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Collections\V9\ChallengeImageLibraryTargetTypeCollection;
use App\Http\Controllers\API\V9\ChallengeImageLibraryController as v9ChallengeImageLibraryController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\ChallengeImageLibraryTargetType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeImageLibraryController extends v9ChallengeImageLibraryController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * To get list of image categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = ChallengeImageLibraryTargetType::where('slug', '!=', 'map')->withCount('images')->having('images_count', '>', 0)->get();

            return $this->successResponse(
                ($categories->count() > 0) ? ['data' => new ChallengeImageLibraryTargetTypeCollection($categories)] : ['data' => []],
                ($categories->count() > 0) ? 'Challenge image library categories retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
