<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateChallengeMapRequest;
use App\Http\Requests\Admin\UpdateChallengeMapRequest;
use App\Models\ChallengeMapLibrary;
use App\Models\Company;
use App\Models\MapProperties;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ChallengeMapLibraryController
 *
 * @package App\Http\Controllers\Admin
 */
class ChallengeMapLibraryController extends Controller
{
    /**
     * variable to store the model object
     * @var model
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param ChallengeMapLibrary $model;
     */
    public function __construct(ChallengeMapLibrary $model, MapProperties $modelProperty)
    {
        $this->model         = $model;
        $this->modelProperty = $modelProperty;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of challenge image library module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('challengeMapLibrary.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeMap.title.manage'));
        });
        Breadcrumbs::for('challengeMapLibrary.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeMap.title.manage'), route('admin.challengeMapLibrary.index'));
            $trail->push(trans('challengeMap.title.add'));
        });
        Breadcrumbs::for('challengeMapLibrary.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeMap.title.manage'), route('admin.challengeMapLibrary.index'));
            $trail->push(trans('challengeMap.title.edit'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-challenge-map-library')) {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']   = trans('page_title.challenges.map_library.index');
            $data['roleGroup']  = $role->group;

            return \view('admin.challenge_map_library.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getMapLibrary(Request $request)
    {
        if (!access()->allow('manage-challenge-map-library')) {
            return response()->json([
                'message' => trans('challengeMap.messages.unauthorized'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data = [
                'edit'              => false,
                'companies'         => $this->getAllCompaniesGroupType(),
                'ga_title'          => trans('page_title.challenges.map_library.add_map'),
                'activeAttechCount' => 0,
                'locationsType'     => [
                    1 => 'Main Type',
                    2 => 'Sub Type',
                ],
            ];

            return \view('admin.challenge_map_library.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateChallengeMapRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateChallengeMapRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challengeMap.messages.added-nextstep'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.challengeMapLibrary.step-2', $data->id)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.challengeMapLibrary.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param ChallengeMapLibrary $image
     * @return View
     */
    public function edit(ChallengeMapLibrary $map)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                      = $map->mapEditData();
            $routeName                 = Request()->route()->getName();
            $data['companies']         = $this->getAllCompaniesGroupType();
            $timezone                  = config('app.timezone');
            $now                       = now($timezone)->toDateTimeString();
            $data['routeName']         = $routeName;
            $data['returnData']        = [];
            $data['ga_title']          = trans('page_title.challenges.map_library.edit_map');
            $data['routeUrl']          = 'admin.challengeMapLibrary.update';
            $data['activeAttechCount'] = $map->mapChallenge()
                ->where(function ($q) use ($now) {
                    $q->where('challenges.start_date', '>=', $now)
                        ->orWhere('challenges.start_date', '<=', $now);
                })
                ->where('challenges.end_date', '>=', $now)
                ->where('challenges.finished', '=', false)
                ->where('challenges.cancelled', '=', false)
                ->count();
            if ($routeName == 'admin.challengeMapLibrary.step-2') {
                $data['ga_title'] = trans('page_title.challenges.map_library.add_map');
                $data['routeUrl'] = 'admin.challengeMapLibrary.step-save';
            }

            return \view('admin.challenge_map_library.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param UpdateChallengeMapRequest $request, ChallengeMapLibrary $map
     *
     * @return RedirectResponse
     */
    public function stepSave(UpdateChallengeMapRequest $request, ChallengeMapLibrary $map)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $locationsCount = count($request->location);
            $properties     = MapProperties::where('map_id', $map->id)->select('id', 'properties')->get()->pluck('properties', 'id')->toArray();


            $lastKeyProperties = $properties[array_key_last($properties)];
            $lastProperties = json_decode($lastKeyProperties);

            if (!array_key_exists('locationType', (array) $lastProperties)) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.destination_validation'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.step-2', $map->id)->with('message', $messageData);
            } elseif ($lastProperties->locationType != 1) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.destination_validation'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.step-2', $map->id)->with('message', $messageData);
            }

            if (count($properties) != $locationsCount) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.property_set_message'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.step-2', $map->id)->with('message', $messageData);
            } else {
                $returnData = [];
                foreach ($properties as $key => $property) {
                    $property = json_decode($property);
                    if (!array_key_exists('distance', (array) $property)) {
                        $returnData[$key] = $key;
                    } elseif (!array_key_exists('locationName', (array) $property)) {
                        $returnData[$key] = $key;
                    } elseif (!array_key_exists('locationType', (array) $property)) {
                        $returnData[$key] = $key;
                    } elseif (!array_key_exists('steps', (array) $property)) {
                        $returnData[$key] = $key;
                    }
                }
                if (!empty($returnData)) {
                    $messageData = [
                        'data'   => trans('challengeMap.validation.property_set_message'),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.challengeMapLibrary.step-2', ['map' => $map->id, 'returnData' => implode(',', $returnData)])->with('message', $messageData);
                }
            }

            $data = $map->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'       => trans('challengeMap.messages.updated'),
                    'status'     => 1,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'       => trans('challengeMap.messages.something_wrong_try_again'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.step-2', $map->id)->with('message', $messageData);
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
     * @param UpdateChallengeMapRequest $request, ChallengeMapLibrary $map
     *
     * @return RedirectResponse
     */
    public function update(UpdateChallengeMapRequest $request, ChallengeMapLibrary $map)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $locationsCount = count($request->location);
            $properties     = MapProperties::where('map_id', $map->id)->select('id', 'properties')->get()->pluck('properties', 'id')->toArray();

            $lastKeyProperties = $properties[array_key_last($properties)];
            $lastProperties = json_decode($lastKeyProperties);
            if (!property_exists($lastProperties, 'locationType')) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.destination_validation'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.edit', $map->id)->with('message', $messageData);
            } elseif ($lastProperties->locationType != 1) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.destination_validation'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.edit', $map->id)->with('message', $messageData);
            }

            if (count($properties) != $locationsCount) {
                $messageData = [
                    'data'       => trans('challengeMap.validation.property_set_message'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.edit', $map->id)->with('message', $messageData);
            } else {
                $returnData = [];
                foreach ($properties as $key => $property) {
                    $property = json_decode($property);
                    if (!property_exists($property, 'distance')) {
                        $returnData[$key] = $key;
                    } elseif (!property_exists($property, 'locationName')) {
                        $returnData[$key] = $key;
                    } elseif (!property_exists($property, 'locationType')) {
                        $returnData[$key] = $key;
                    } elseif (!property_exists($property, 'steps')) {
                        $returnData[$key] = $key;
                    }
                }
                if (!empty($returnData)) {
                    $messageData = [
                        'data'   => trans('challengeMap.validation.property_set_message'),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.challengeMapLibrary.edit', ['map' => $map->id, 'returnData' => implode(',', $returnData)])->with('message', $messageData);
                }
            }

            $data = $map->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'       => trans('challengeMap.messages.updated'),
                    'status'     => 1,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'       => trans('challengeMap.messages.something_wrong_try_again'),
                    'status'     => 0,
                    'returnData' => [],
                ];
                return \Redirect::route('admin.challengeMapLibrary.edit', $map->id)->with('message', $messageData);
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
     * @param  ChallengeMapLibrary $map
     *
     * @return RedirectResponse
     */
    public function delete(ChallengeMapLibrary $map)
    {
        $role = getUserRole();
        if (!access()->allow('delete-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $map->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ChallengeMapLibrary $map
     *
     * @return RedirectResponse
     */
    public function archive(ChallengeMapLibrary $map)
    {
        $role = getUserRole();
        if (!access()->allow('delete-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $map->archiveRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  MapProperties $mapLocation
     *
     * @return RedirectResponse
     */
    public function deleteLocation(MapProperties $mapLocation)
    {
        $role = getUserRole();
        if (!access()->allow('delete-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $deleteLocation = $mapLocation->deleteRecord();
            $mapDistance    = MapProperties::select(
                \DB::raw('properties->>"$.distance" AS distance')
            )
                ->where('map_id', $mapLocation->map_id)
                ->get()
                ->pluck('distance')
                ->toArray();

            $mapSteps = MapProperties::select(
                \DB::raw('properties->>"$.steps" AS steps')
            )
                ->where('map_id', $mapLocation->map_id)
                ->get()
                ->sum('steps');

            $totalDistance = array_sum($mapDistance);
            $totalLocation = count($mapDistance);

            ChallengeMapLibrary::where('id', $mapLocation->map_id)
                ->limit(1)
                ->update(['total_distance' => $totalDistance, 'total_location' => $totalLocation, 'total_steps' => $mapSteps]);

            return [
                'status'        => $deleteLocation,
                'totalDistance' => $totalDistance,
                'totalLocation' => $totalLocation,
                'totalSteps'    => $mapSteps,
            ];
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ChallengeMapLibrary $map
     *
     * @return RedirectResponse
     */
    public function getMapLocation(ChallengeMapLibrary $map)
    {
        if (!access()->allow('manage-challenge-map-library')) {
            abort(403);
        }
        try {
            $propertyArray = [];
            $i             = 1;
            $mapProperties = MapProperties::where('map_id', $map->id)
                ->get()
                ->pluck('properties', 'id')
                ->toArray();

            foreach ($mapProperties as $key => $value) {
                $property        = json_decode($value);
                $lat             = isset($property->lat) ? $property->lat : 0;
                $lng             = isset($property->lng) ? $property->lng : 0;
                $propertyArray[] = [
                    'key'     => $i,
                    'id'      => $key,
                    'lat'     => $lat,
                    'lng'     => $lng,
                    'latLong' => $lat . ',' . $lng,
                ];
                $i++;
            }
            return $propertyArray;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  MapProperties $mapLocation
     *
     * @return RedirectResponse
     */
    public function getLocation(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $previousLocationId  = $request->previousId;
            $mapLocation         = MapProperties::find($request->mapLocation);
            $mapPreviousLocation = MapProperties::find($previousLocationId);
            $distance            = $steps            = 0;
            $locations           = [];
            if (!empty($mapLocation)) {
                $locationProperties = json_decode($mapLocation->properties);

                if (!empty($mapPreviousLocation)) {
                    $previousLocationProperties = json_decode($mapPreviousLocation->properties);
                    $previousLat                = $previousLocationProperties->lat;
                    $previousLng                = $previousLocationProperties->lng;
                    $lat                        = $locationProperties->lat;
                    $lng                        = $locationProperties->lng;
                    $distance                   = (int) round($this->distance($previousLat, $previousLng, $lat, $lng, 'K'));
                    $steps                      = config('zevolifesettings.steps') * $distance;
                }

                $locations = [
                    'id'        => $mapLocation->id,
                    'mapId'     => $mapLocation->map_id,
                    'property'  => $locationProperties,
                    'imageUrl'  => isset($mapLocation->image) ? $mapLocation->image : '',
                    'imageName' => !empty($mapLocation->getFirstMedia('propertyimage')) ? $mapLocation->getFirstMedia('propertyimage')->name : '',
                    'distance'  => $distance,
                    'steps'     => $steps,
                ];
            }
            return response()->json($locations);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function storeProperty(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-map') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();

            $data = $this->modelProperty->storeEntity($request->all());
            if ($data) {
                $mapDistance = MapProperties::select(
                    \DB::raw('properties->>"$.distance" AS distance')
                )
                    ->where('map_id', $request->map_id)
                    ->get()
                    ->pluck('distance')
                    ->toArray();

                $mapSteps = MapProperties::select(
                    \DB::raw('properties->>"$.steps" AS steps')
                )
                    ->where('map_id', $request->map_id)
                    ->get()
                    ->sum('steps');

                $totalDistance = array_sum($mapDistance);
                $totalLocation = count($mapDistance);

                ChallengeMapLibrary::where('id', $request->map_id)
                    ->limit(1)
                    ->update(['total_distance' => $totalDistance, 'total_location' => $totalLocation, 'total_steps' => $mapSteps]);

                \DB::commit();
                return [
                    'data'          => trans('challengeMap.messages.property_updated'),
                    'status'        => 1,
                    'totalDistance' => $totalDistance,
                    'totalLocation' => $totalLocation,
                    'totalSteps'    => $mapSteps,
                    'propertiesId'  => $data->id,
                ];
            } else {
                \DB::rollback();
                return [
                    'data'          => trans('challengeMap.messages.something_wrong_try_again'),
                    'status'        => 0,
                    'totalDistance' => 0,
                    'totalLocation' => 0,
                ];
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getMapDetails(ChallengeMapLibrary $map)
    {
        try {
            return $map;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function storeLatLong(Request $request)
    {
        try {
            \DB::beginTransaction();

            $data = $this->modelProperty->storeLatLongEntity($request->all());

            if ($data) {
                $mapDistance = MapProperties::select(
                    \DB::raw('properties->>"$.distance" AS distance')
                )
                    ->where('map_id', $request->map_id)
                    ->get()
                    ->pluck('distance')
                    ->toArray();
                $totalLocation = count($mapDistance);

                ChallengeMapLibrary::where('id', $request->map_id)
                    ->limit(1)
                    ->update(['total_location' => $totalLocation]);

                \DB::commit();
                return [
                    'data'          => trans('challengeMap.messages.map_lat_long'),
                    'status'        => 1,
                    'totalLocation' => $totalLocation,
                    'propertiesId'  => $data->id,
                ];
            } else {
                \DB::rollback();
                return [
                    'data'          => trans('challengeMap.messages.something_wrong_try_again'),
                    'status'        => 0,
                    'totalLocation' => 0,
                ];
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeMap.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeMapLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    protected function getAllCompaniesGroupType()
    {
        $groupType        = config('zevolifesettings.content_company_group_type');
        $companyGroupType = [];
        $nowInUTC         = now(config('app.timezone'))->toDateTimeString();
        foreach ($groupType as $value) {
            $companies = [];
            if ($value == 'Zevo') {
                $companies = Company::where('subscription_start_date', '<=', $nowInUTC)
                        ->where('subscription_end_date', '>=', $nowInUTC)
                        ->whereNull('parent_id')
                        ->where('is_reseller', false)
                        ->pluck('name', 'id')
                        ->toArray();
            }
            if (count($companies) > 0) {
                $companyGroupType[] = [
                    'roleType'  => $value,
                    'companies' => $companies,
                ];
            }
        }
        return $companyGroupType;
    }

    /**
     * Get unit between two lat long in KM
     *
     * @return array
     **/
    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist  = acos($dist);
            $dist  = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit  = strtoupper($unit);

            if ($unit == "K") {
                return $miles * 1.609344;
            } elseif ($unit == "N") {
                return $miles * 0.8684;
            } else {
                return $miles;
            }
        }
    }
}
