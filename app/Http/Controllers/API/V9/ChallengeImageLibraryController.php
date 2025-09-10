<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V9;

use App\Http\Collections\V9\CategoryWiseChallengeImageLibraryListCollection;
use App\Http\Collections\V9\ChallengeImageLibraryTargetTypeCollection;
use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\ChallengeImageLibrary;
use App\Models\ChallengeImageLibraryTargetType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeImageLibraryController extends Controller
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
            $categories = ChallengeImageLibraryTargetType::withCount('images')->having('images_count', '>', 0)->get();

            return $this->successResponse(
                ($categories->count() > 0) ? ['data' => new ChallengeImageLibraryTargetTypeCollection($categories)] : ['data' => []],
                ($categories->count() > 0) ? 'Challenge image library categories retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function getCategoryWiseImages(ChallengeImageLibraryTargetType $category, Request $request)
    {
        try {
            $images = ChallengeImageLibrary::where('target_type', $category->id)
                ->orderBy('id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($images->count() > 0) ? new CategoryWiseChallengeImageLibraryListCollection($images) : ['data' => []],
                ($images->count() > 0) ? 'Images are retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
