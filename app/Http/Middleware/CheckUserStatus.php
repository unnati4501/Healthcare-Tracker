<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Traits\ServesApiTrait;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class CheckUserStatus
 * @package App\Http\Middleware
 */
class CheckUserStatus
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
        if ($this->guard()->check()) {
            /** @var User $user */
            $user = $this->guard()->user();
            if (!empty($user) && $user->is_blocked) {
                Auth::logout();
                return redirect('/login')->withInput()->withErrors(\trans('api_labels.auth.inactive'));
            } else if (!empty($user) && !is_null($user->deleted_at)) {
                Auth::logout();
                return redirect('/login')->withInput()->withErrors(\trans('api_labels.auth.inactive'));
            }
        } elseif (!empty($request->email)) {
            /** @var User $user */
            $user = User::findByEmail($request->email);
            if (!empty($user) && $user->is_blocked) {
                Auth::logout();
                return redirect('/login')->withInput()->withErrors(\trans('api_labels.auth.inactive'));
            }
        }

        return $next($request);
    }
}
