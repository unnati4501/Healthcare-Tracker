<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Collections\V30\SubCategoryCollection as v30subcategorycollection;
use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Controllers\API\V26\CommonController as v26CommonController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserGoal;
use App\Models\Webinar;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v26CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get list of master categories
     *
     * @param Request $request, Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCategories(Request $request, Category $category)
    {
        try {
            $user      = $this->user();
            $company   = $user->company()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $records   = SubCategory::where('category_id', $category->id)
                ->orderBy('is_excluded', 'DESC')
                ->get();

            if ($category->short_name == 'meditation') {
                $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($category, $company) {

                        return $item->meditations()->join('meditation_tracks_company', function ($join) use ($company) {
                            $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                                ->where('meditation_tracks_company.company_id', $company->id);
                        })->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();

                    /*if ($favouritedCount > 0) {
                        $records = array(0 => "My ⭐") + $subcategoryRecords;
                    } else {
                        $records = $subcategoryRecords + array(0 => "My ⭐");
                    }*/
                    $records = $subcategoryRecords + array(0 => "My ⭐");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();

                    /*if ($favouritedCount > 0) {
                        $records = array(-1 => "View all") + array(0 => "My ⭐") + $subcategoryRecords;
                    } else {
                        $records = array(-1 => "View all") + $subcategoryRecords + array(0 => "My ⭐");
                    }*/
                    $records = array(-1 => "View all") + $subcategoryRecords + array(0 => "My ⭐");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'recipe') {
                $favouritedCount = $user->recipeLogs()
                    ->wherePivot('favourited', true)
                    ->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) {
                        return $item->recipes()->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();

                    /*if ($favouritedCount > 0) {
                        $records = array(0 => "My ⭐") + $subcategoryRecords;
                    } else {
                        $records = $subcategoryRecords + array(0 => "My ⭐");
                    }*/
                    $records = $subcategoryRecords + array(0 => "My ⭐");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();

                    /*if ($favouritedCount > 0) {
                        $records = array(-1 => "All") + array(0 => "My ⭐") + $subcategoryRecords;
                    } else {
                        $records = array(-1 => "All") + $subcategoryRecords + array(0 => "My ⭐");
                    }*/
                    $records = array(-1 => "All") + $subcategoryRecords + array(0 => "My ⭐");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $records = $records->filter(function ($item, $key) use ($category) {
                    switch ($category->short_name) {
                        case 'group':
                            if ($item->short_name == 'public') {
                                return true;
                            }
                            return $item->groups()
                                ->where('is_archived', 0)
                                ->where('is_visible', 1)
                                ->count() > 0;
                            break;
                        case 'course':
                            $users     = $this->user();
                            $companyId = $users->company()->first()->id;

                            $categoryCount = Course::where("sub_category_id", $item->id)
                                ->join('masterclass_company', function ($join) use ($companyId) {
                                    $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                                        ->where('masterclass_company.company_id', $companyId);
                                })
                                ->where("courses.status", true)
                                ->count();
                            return ($categoryCount > 0) ? $item : [];
                            break;
                        case 'feed':
                            $users     = $this->user();
                            $companyId = $users->company()->first()->id;
                            $timezone  = $user->timezone ?? config('app.timezone');
                            $feedCount = Feed::where("sub_category_id", $item->id)
                                ->join('feed_company', function ($join) use ($companyId) {
                                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                                        ->where('feed_company.company_id', '=', $companyId);
                                })
                                ->join('sub_categories', function ($join) {
                                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                                })
                                ->where(function (Builder $query) use ($timezone) {
                                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                                })
                                ->where(function (Builder $query) use ($timezone) {
                                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                                })
                                ->where("feeds.sub_category_id", $item->id)
                                ->count();

                            return ($feedCount > 0) ? $item : [];
                            break;
                        // case 'recipe':
                        //     return $item->recipes()->count() > 0;
                        //     break;
                        case 'webinar':
                            $user          = $this->user();
                            $company       = $user->company()->first();
                            $categoryCount = Webinar::where("sub_category_id", $item->id)
                                ->join('webinar_company', function ($join) use ($company) {
                                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                                        ->where('webinar_company.company_id', $company->id);
                                })
                                ->count();
                            return ($categoryCount > 0) ? $item : [];
                            break;
                        default:
                            return true;
                            break;
                    }
                });
            } else {
                $records = $records->filter(function ($item, $key) use ($category) {
                    if ($category->short_name == 'group') {
                        if ($item->short_name == 'public') {
                            return true;
                        }
                        return $item->groups()
                            ->where('is_archived', 0)
                            ->where('is_visible', 1)
                            ->count() > 0;
                    } else {
                        return true;
                    }
                });

                if ($category->short_name == 'course' || $category->short_name == 'webinar') {
                    $records = $records->pluck("name", "id")->toArray();
                    $new_array = array_map(function ($id, $name) {
                        return array(
                            'id'         => $id,
                            'name'       => $name,
                            'short_name' => str_replace(' ', '_', strtolower($name)),
                        );
                    }, array_keys($records), $records);

                    $records = SubCategory::hydrate($new_array);
                }
            }

            return $this->successResponse(($records->count() > 0) ? new v30subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
