<?php
namespace ShaoZeMing\GeTui;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

/**
 * Class GeTuiServiceProvider
 * @package ShaoZeMing\GeTui
 */
class GeTuiServiceProvider extends ServiceProvider
{

    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $source = realpath(__DIR__.'/config/getui.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('getui.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('getui');
        }
        $this->mergeConfigFrom($source, 'getui');

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
        $this->app->alias(GeTuiService::class, 'getui');

    }

    public function provides()
    {
        return [GeTuiService::class,'getui'];
    }
}
