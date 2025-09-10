<?php declare (strict_types = 1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AzureServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/azure.php' => config_path('azure.php'),
            ], 'azure-config');
        } else {
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/azure.php',
                'azure'
            );
        }
    }
}
