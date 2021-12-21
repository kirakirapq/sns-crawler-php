<?php

namespace App\Providers;

use App\Application\Interactors\Facebook\FacebookApiManager;
use App\Application\Interactors\Facebook\FacebookCrawlerManager;
use App\Application\Repositories\FacebookApiRepository;
use App\Application\UseCases\Facebook\FacebookApiUseCase;
use App\Application\UseCases\Facebook\FacebookCrawlerUseCase;
use App\Gateways\FacebookApiRequest;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;


class FacebookServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        // UseCase
        $this->app->bind(
            FacebookCrawlerUseCase::class,
            FacebookCrawlerManager::class
        );

        // ApiUseCase
        $this->app->bind(
            FacebookApiUseCase::class,
            FacebookApiManager::class
        );

        // repository
        $this->app->bind(
            FacebookApiRepository::class,
            FacebookApiRequest::class
        );
    }

    /**
     * Bootstrap any application Managers.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    // /**
    //  * 遅延ロード
    //  * このプロバイダにより提供されるサービスの取得
    //  *
    //  * @return array
    //  */
    // public function provides()
    // {
    //     return [TwitterApiUseCase::class, TwitterCrawlerUseCase::class, HttpRequestHttpClient::class];
    // }
}
