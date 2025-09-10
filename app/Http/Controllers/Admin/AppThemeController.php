<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAppThemeRequest;
use App\Http\Requests\Admin\UpdateAppThemeRequest;
use App\Models\AppTheme;
use Illuminate\Http\Request;
use Breadcrumbs;

class AppThemeController extends Controller
{
    /**
     * AppTheme model varibale
     *
     * @var App\Models\AppTheme
     **/
    public $appTheme;

    /**
     * contructor to initialize model object
     * @param AppSlide $model ;
     */
    public function __construct(AppTheme $appTheme)
    {
        $this->appTheme = $appTheme;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('apptheme.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('appthemes.title.index_title'));
        });
        Breadcrumbs::for('apptheme.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('appthemes.title.index_title'), route('admin.app-themes.index'));
            $trail->push('Add App Theme');
        });
        Breadcrumbs::for('apptheme.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('appthemes.title.index_title'), route('admin.app-themes.index'));
            $trail->push('Edit App Theme');
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (!access()->allow('app-theme')) {
            abort(403);
        }

        try {
            $data = [
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.app-theme.index'),
            ];

            return view('admin.app-theme.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Mixed View|RedirecResponse
     */
    public function create()
    {
        if (!access()->allow('add-event')) {
            abort(403);
        }

        try {
            $data = [
                'ga_title' => trans('page_title.app-theme.create'),
            ];

            return view('admin.app-theme.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::route('admin.app-themes.index')->with('message', [
                'data'   => trans('appthemes.message.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function getThemes(Request $request)
    {
        if (!access()->allow('app-theme-management')) {
            return response()->json([
                'message' => trans('appthemes.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->appTheme->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('appthemes.message.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\CreateAppThemeRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAppThemeRequest $request)
    {
        if (!access()->allow('create-app-theme')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->appTheme->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                return \Redirect::route('admin.app-themes.index')->with('message', [
                    'data'   => trans('appthemes.message.data_store_success'),
                    'status' => 1,
                ]);
            } else {
                \DB::rollback();
                return \Redirect::route('admin.app-themes.create')->with('message', [
                    'data'   => trans('appthemes.message.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return \Redirect::route('admin.app-themes.index')->with('message', [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AppTheme $theme
     * @return View
     */
    public function edit(AppTheme $theme)
    {
        if (!access()->allow('update-app-theme')) {
            abort(403);
        }

        try {
            // get theme used/default counts, if used then prevent theme to be deleted
            if ($theme->company()->count() > 0 || $theme->default()->count() > 0) {
                return view('errors.401');
            }

            $data = [
                'theme'    => $theme,
                'ga_title' => trans('page_title.app-theme.edit'),
            ];

            return view('admin.app-theme.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::route('admin.app-themes.index')->with('message', [
                'data'   => trans('appthemes.message.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\AppTheme $theme
     * @param  \App\Http\Requests\Admin\UpdateAppThemeRequest $request
     * @return RedirectResponse
     */
    public function update(AppTheme $theme, UpdateAppThemeRequest $request)
    {
        if (!access()->allow('update-app-theme')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $theme->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                return \Redirect::route('admin.app-themes.index')->with('message', [
                    'data'   => trans('appthemes.message.data_store_success'),
                    'status' => 1,
                ]);
            } else {
                \DB::rollback();
                return \Redirect::route('admin.app-themes.edit', $theme->id)->with('message', [
                    'data'   => trans('appthemes.message.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return \Redirect::route('admin.app-themes.edit', $theme->id)->with('message', [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AppTheme $theme
     * @return Boolean
     */
    public function delete(AppTheme $theme)
    {
        if (!access()->allow('delete-app-theme')) {
            return response()->json([
                'message' => trans('appthemes.message.unauthorized_access'),
            ], 422);
        }

        try {
            // get counts of theme
            $usedCount  = $theme->company()->count('id');
            $hasDefault = $theme->default()->count('id');

            if ($usedCount == 0 && $hasDefault == 0) {
                $deleted = $theme->deleteRecord();
                if ($deleted) {
                    return response()->json([
                        'data' => trans('appthemes.message.data_deleted_success'),
                    ], 200);
                } else {
                    return response()->json([
                        'data' => trans('appthemes.message.data_deleted_failed'),
                    ], 422);
                }
            } else {
                return response()->json([
                    'data' => trans('appthemes.message.theme_in_use'),
                ], 422);
            }
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data' => trans('appthemes.message.something_wrong_try_again'),
            ], 500);
        }
    }
}
