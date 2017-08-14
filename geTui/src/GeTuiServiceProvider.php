<?php
namespace GeTui;

use GeTui\App\Repositories\MessageApiRepository;
use GeTui\App\Repositories\MessagePushApiRepository;
use GeTui\App\Repositories\UserAppApiRepository;
use GeTui\App\Repositories\UserPushOptionApiRepository;
use Illuminate\Support\ServiceProvider;

// use GeTui\App\Console\Commands\MessagePush;

class GeTuiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // //
//        $this->loadViewsFrom(__DIR__.'/views', 'geTui');  //注册视图模板

//        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->mergeConfigFrom(
            __DIR__.'/config/getui.php', 'getui'
        );

        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         // MessagePush::class,
        //     ]);
        // }


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        include __DIR__.'/routes/getui.php';

        $this->app->singleton('MessageToCenter', function ($app) {
            $messagePushApiRepository = new MessagePushApiRepository($app);
            return new PushMessageToCenter($messagePushApiRepository);
        });
        $this->app->singleton('UserPushOption', function ($app) {
            $userPushOptionApiRepository = new UserPushOptionApiRepository($app);
            return new UserPushOption($userPushOptionApiRepository);
        });
        $this->app->singleton('MessageToApp', function ($app) {
            $messagePushApiRepository = new MessagePushApiRepository($app);
            $messageApiRepository     = new MessageApiRepository($app);
            return new PushMessageToApp($messageApiRepository, $messagePushApiRepository);
        });
        $this->app->singleton('UserApp', function ($app) {
            $userAppApiRepository = new UserAppApiRepository($app);
            return new UserApp($userAppApiRepository);
        });

        $this->app->singleton('GeTuiService', function ($app){
            return new GeTuiService();
        });

        $this->app->singleton('MerGeTuiService', function ($app){
            // var_export($app['config']['getui']['tag']);
            $obj = new GeTuiService();
            return $obj->getMerInstance();
        });
    }
}
