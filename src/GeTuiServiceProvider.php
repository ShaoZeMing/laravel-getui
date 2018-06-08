<?php
namespace Shaozeming\GeTui;

use Illuminate\Support\ServiceProvider;


/**
 * Class GeTuiServiceProvider
 * @package Shaozeming\GeTui
 */
class GeTuiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/getui.php' => config_path('getui.php'),
            ], 'config');
        }

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GeTuiService::class, function ($app) {
            return new GeTuiService($app->config->get('getui', []));
        });


    }

    public function provides()
    {
        return [GeTuiService::class];
    }
}
