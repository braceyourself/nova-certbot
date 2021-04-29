<?php

namespace Braceyourself\NovaCertbot;

use Braceyourself\NovaCertbot\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Braceyourself\NovaCertbot\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'NovaCertbot');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/braceyourself/certbot')
            ->namespace('Braceyourself\NovaCertbot\Http\Controllers')
            ->group(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/Config/certbot.php' => config_path('certbot.php')
        ]);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $configPath = __DIR__ . '/Config/certbot.php';
        $this->mergeConfigFrom($configPath, 'certbot');


        $this->app->singleton(Certbot::class, function ($app) {
            return new Certbot(collect(config('certbot')));
        });

    }
}
