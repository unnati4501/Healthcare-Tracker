<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V41;

use App\Http\Collections\V41\ShortsListCollection;
use App\Http\Collections\V41\RecentShortsCollection;
use App\Http\Collections\V41\ShortsDetailsCollection;
use App\Http\Resources\V41\ShortsListResource;
use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\Shorts;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class ShortsController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait, PaginationTrait;

    /**
     * List all the shorts based on user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($subcategory, Request $request)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();
            if ($subcategory > 0) {
                $subcatData = SubCategory::find($subcategory);
                if (empty($subcatData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $shortsData = Shorts::select('shorts.*', DB::raw("(SELECT count(view_count) FROM shorts_user WHERE short_id = `shorts`.`id`) AS shorts_view_count"))
                ->join('shorts_team', function ($join) use ($team) {
                    $join->on('shorts.id', '=', 'shorts_team.short_id')
                        ->where('shorts_team.team_id', '=', $team->getKey());
                });
            
            if ($subcategory <= 0) {
                $shortsData->join('sub_categories', 'sub_categories.id', '=', 'shorts.sub_category_id');
            }
           
            if ($subcategory > 0) {
                $shortsData->where("shorts.sub_category_id", $subcategory)
                    ->orderByRaw("`shorts`.`updated_at` DESC");
            } elseif ($subcategory == 0) {
                $shortsData->join('shorts_user', 'shorts.id', '=', 'shorts_user.short_id')
                        ->where("shorts_user.user_id", $user->id)
                        ->where(["favourited" => 1, "sub_categories.status" => 1])
                        ->orderByRaw("`shorts_view_count` DESC, `shorts`.`updated_at` DESC");
            } else {
                $shortsData->where(["sub_categories.status" => 1])
                        ->orderByRaw("`shorts_view_count` DESC, `shorts`.`updated_at` DESC");
            }
            $shortsData = $shortsData->groupBy("shorts.id");

            $recomendationShorts = $shortsData->limit(4)->get()->shuffle();
            
            $shortsData = $shortsData->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($shortsData->count() > 0) {
                $shorts  = new ShortsListCollection($shortsData, $recomendationShorts);
                $message = 'Shorts Retrieved Successfully';
            } else {
                $shorts  = ['data' => []];
                $message = trans('shorts.message.no_shorts_found');
            }
            return $this->successResponse($shorts, $message);
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get short details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, Shorts $short)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();

            $mainShort =  $short->where('id', $short->id)->get()->toArray();
            $otherShorts = Shorts::select(
                'shorts.*'
            );

            $otherShorts = $otherShorts->join('shorts_team', function ($join) use ($team) {
                    $join->on('shorts.id', '=', 'shorts_team.short_id')
                        ->where('shorts_team.team_id', '=', $team->getKey());
                })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'shorts.sub_category_id');
                });
            $otherShorts = $otherShorts->where('shorts.id', '!=', $short->id)->get()->toArray();
            if(!empty($mainShort) || !empty($otherShorts)){
                $allShorts         = array_merge($mainShort, $otherShorts);
                $getallShorts      = Shorts::hydrate($allShorts)->toArray();
                $getallShorts      = $this->paginate($getallShorts);
            }
            if (!empty($getallShorts) && $getallShorts->count() > 0) {
                return $this->successResponse(new ShortsDetailsCollection($getallShorts), 'Shorts Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to favorited unfavourited Short
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouriteShort(Request $request, Shorts $short)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.short.favorited');
            $pivotExsisting = $short
                ->shortsUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('short_id', $short->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                           = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited    = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.short.unfavorited');
                }
            } else {
                $short
                    ->shortsUserLogs()
                    ->attach($user, ['favourited' => true, 'favourited_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to saved unsaved Short
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsave(Request $request, Shorts $short)
    {
        try {
            \DB::beginTransaction();

            $user           = $this->user();
            $message        = trans('api_messages.short.saved');
            $pivotExsisting = $short
                ->shortsUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('short_id', $short->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = (($saved == 1) ? 0 : 1);
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    $message = trans('api_messages.short.unsaved');
                }
            } else {
                $short->shortsUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to liked unliked Short
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeShort(Request $request, Shorts $short)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.short.liked');
            $pivotExsisting = $short
                ->shortsUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('short_id', $short->getKey())
                ->first();
            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    $message = trans('api_messages.short.unliked');
                }
            } else {
                $short
                    ->shortsUserLogs()
                    ->attach($user, ['liked' => true]);
            }
            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $short->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * get saved shorts listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            $user           = $this->user();
            $team           = $user->teams()->first();
            $records        = $user->userShortsLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'shorts.sub_category_id');
                })
                ->join('shorts_team', function ($join) use ($team) {
                    $join->on('shorts.id', '=', 'shorts_team.short_id')
                        ->where('shorts_team.team_id', '=', $team->getKey());
                });
            $records = $records->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('shorts_user.saved_at', 'DESC')
                ->orderBy('shorts_user.id', 'DESC')
                ->groupBy('shorts.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($records->count() > 0) ? new ShortsListCollection($records, null) : ['data' => []],
                ($records->count() > 0) ? 'Shorts List retrieved successfully.' : 'No results'
            );

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
