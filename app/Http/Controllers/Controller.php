<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * This function is used to remove the DB name from the exception message
     * 
     * @param QueryException $exception
     * @return array
     */
    protected function getQueryExceptionMessageData(QueryException $exception)
    {
        return [
            'data'   => str_replace("'" . DB::connection()->getDatabaseName() . "'.", '', $exception->getMessage()),
            'status' => 0
        ];
    }
}
