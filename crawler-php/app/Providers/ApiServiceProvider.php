<?php

namespace App\Providers;

use App\Application\Interactors\BigQuery\BigQueryManager;
use App\Application\Interactors\Csv\CsvManager;
use App\Application\Interactors\Facebook\FacebookApiManager;
use App\Application\Interactors\Facebook\FacebookCrawlerManager;
use App\Application\Interactors\RiskWord\RiskWordManager;
use App\Application\Repositories\BigQuery\BigQueryRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Gateways\BigQuery\BigQueryRepositoryClient;
use App\Application\Interactors\Reddit\RedditApiManager;
use App\Application\Interactors\Reddit\RedditCrawlerManager;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\Interactors\Twitter\TwitterApiManager;
use App\Application\Interactors\Twitter\TwitterCrawlerManager;
use App\Application\OutputData\OuterApiResponse\ApiResponse;
use App\Application\OutputData\OuterApiResponse\OuterApiResponse;
use App\Application\Repositories\Csv\CsvRepository;
use App\Application\Repositories\FacebookApiRepository;
use App\Application\Repositories\RedditApiRepository;
use App\Application\Repositories\TwitterApiRepository;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Application\UseCases\Reddit\RedditCrawlerUseCase;
use App\Application\UseCases\Twitter\TwitterApiUseCase;
use App\Application\UseCases\Twitter\TwitterCrawlerUseCase;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\Facebook\FacebookApiUseCase;
use App\Application\UseCases\Facebook\FacebookCrawlerUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Gateways\Csv\CsvClient;
use App\Gateways\FacebookApiRequest;
use App\Gateways\HttpRequest\GuzzleClient;
use App\Gateways\RedditApiRequest;
use App\Gateways\Translation\TranslationClient;
use App\Gateways\TwitterApiRequest;
use App\Http\Controllers\Api\SnsCrawlController;
use App\Http\Controllers\Api\TranslationApiController;
use Illuminate\Support\Facades\Config;
// use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider //implements DeferrableProvider
{
    public function register()
    {
        // CrawlerUseCase
        $this->app->bind(
            FacebookCrawlerUseCase::class,
            FacebookCrawlerManager::class
        );

        $this->app->bind(
            TwitterCrawlerUseCase::class,
            TwitterCrawlerManager::class
        );

        $this->app->bind(
            RedditCrawlerUseCase::class,
            RedditCrawlerManager::class
        );

        // ApiUseCase
        $this->app->bind(
            FacebookApiUseCase::class,
            FacebookApiManager::class
        );
        $this->app->bind(
            TwitterApiUseCase::class,
            TwitterApiManager::class
        );

        $this->app->bind(
            RedditApiUseCase::class,
            RedditApiManager::class
        );

        $this->app->bind(
            BigQueryUseCase::class,
            BigQueryManager::class
        );

        $this->app->bind(
            CsvUseCase::class,
            CsvManager::class
        );

        $this->app->bind(
            RiskWordUseCase::class,
            RiskWordManager::class
        );


        // Repository
        $this->app->bind(
            HttpClient::class,
            GuzzleClient::class
        );

        $this->app->singleton(HttpClient::class, function () {
            return new GuzzleClient();
        });

        $this->app->bind(BigQueryRepository::class, function () {
            return new BigQueryRepositoryClient(Config::get('app.GCP_PROJECT_ID'));
        });

        $this->app->bind(
            CsvRepository::class,
            CsvClient::class
        );

        // $this->app->bind(
        //     TranslationRepository::class,
        //     TranslationClient::class
        // );

        $this->app->bind(
            FacebookApiRepository::class,
            FacebookApiRequest::class
        );

        $this->app->bind(
            TwitterApiRepository::class,
            TwitterApiRequest::class
        );

        $this->app->bind(
            RedditApiRepository::class,
            RedditApiRequest::class
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
