<?php

namespace App\Providers;

use App\Application\Interactors\Notification\SlackNotificationInvoker;
use App\Application\Interactors\Notification\SlackNotificationManager;
use App\Application\Repositories\Notification\NotificationClient;
use App\Application\UseCases\Notification\NotificationInvoker;
use App\Application\UseCases\Notification\NotificationUseCase;
use App\Gateways\Notification\SlackClient;
use Illuminate\Support\ServiceProvider;


class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            NotificationInvoker::class,
            SlackNotificationInvoker::class
        );

        $this->app->bind(
            NotificationUseCase::class,
            SlackNotificationManager::class
        );

        $this->app->bind(
            NotificationClient::class,
            SlackClient::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
