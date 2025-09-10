<?php
declare (strict_types = 1);

namespace App\Http\Traits;

use App\Models\User\User;
use App\Models\User\UserDevice;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTGuard;

/**
 * Trait ProvidesAuthGuardTrait
 *
 * @package App\Http\Traits
 */
trait ProvidesAuthGuardTrait
{
    /**
     * @return Guard|StatefulGuard|JWTGuard
     */
    protected function guard(): Guard
    {
        return Auth::guard(app('request')->wantsJson() ? 'api' : 'web');
    }

    /**
     * @return Authenticatable|null
     * @throws AuthenticationException
     */
    public function user():  ? Authenticatable
    {
        if (!$this->guard()->check()) {
            throw new AuthenticationException();
        }

        return $this->guard()->user();
    }

    /**
     * @return UserDevice|null
     * @throws AuthenticationException
     */
    public function userDevice() :  ? UserDevice
    {
        $device = \null;

        /** @var Request $request */
        $request = app('request');

        if ($request->wantsJson()) {
            /** @var User $user */
            $user = $this->user();

            $device = $user->devices
                ->where('udid', $request->headers->get('X-Device-Id'))
                ->where('os', \lower($request->headers->get('X-Device-Os')))
                ->first();
        }

        return $device;
    }
}
