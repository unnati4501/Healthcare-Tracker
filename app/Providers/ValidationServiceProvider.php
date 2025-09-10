<?php
declare(strict_types = 1);

namespace App\Providers;

use App\Validators\CommonValidator;
use Validator;

/**
 * Class ValidationServiceProvider
 *
 * @package App\Providers
 */
class ValidationServiceProvider extends \Illuminate\Validation\ValidationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new CommonValidator($translator, $data, $rules, $messages);
        });
    }
}
