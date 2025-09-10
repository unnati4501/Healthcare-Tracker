<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use Closure;
use DB;

/**
 * Class UpdateUserTimezone
 * @package App\Http\Middleware
 */
class UpdateUserTimezone
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
            /* @var User $user */
            $user = $this->guard()->user();

            // Update user tracker if needed
            if ($request->headers->has('X-User-Timezone') && !empty($request->headers->get('X-User-Timezone'))) {
                DB::beginTransaction();
                $userTimezone      = $request->headers->get('X-User-Timezone');
                $oldMexicoTimezone = config('zevolifesettings.mexico_city_timezone.old_timezone');
                $newMexicoTimezone = config('zevolifesettings.mexico_city_timezone.new_timezone');
                $isTimezone        = false;
                if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
                    $userTimezone = $newMexicoTimezone;
                    $isTimezone   = true;
                }
                DB::transaction(function () use($user, $userTimezone, $isTimezone)  {
                    User::where('id', $user->id)->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                }, 3);
                DB::commit();
            }
        }

        return $next($request);
    }
}