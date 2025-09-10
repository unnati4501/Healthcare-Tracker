<?php

namespace App\Http\Middleware;

use App\Http\Traits\ServesApiTrait;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\ApiLog;
use Closure;

class TrackApiData
{
    use ProvidesAuthGuardTrait, ServesApiTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // collect request data and save
        $inputData = [];
        if ($this->guard()->check()) {
            $user = $this->guard()->user();
            $inputData['user_id'] = (!empty($user)) ? $user->getKey() : null;
        }
        $inputData['type'] = 'request';
        $inputData['route'] = $request->getPathInfo();
        $inputData['headers'] = $request->headers;
        $inputData['parameters'] = $request->parameters;
        $inputData['request_data'] = json_encode($request->all());

        $apiLog = ApiLog::create($inputData);

        $response = $next($request);

        // collect response data and update api log model
        $apiLog->update(['response_data' => $response]);

        return $response;
    }
}
