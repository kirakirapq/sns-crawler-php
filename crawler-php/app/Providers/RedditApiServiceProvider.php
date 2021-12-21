<?php

namespace App\Providers;

use App\Application\Interactors\BigQuery\BigQueryManager;
use App\Application\Interactors\Csv\CsvManager;
use App\Application\Interactors\RiskWord\RiskWordManager;
use App\Application\Repositories\BigQuery\BigQueryRepository;
use App\Application\UseCases\BigQuery\BigQueryUseCase;
use App\Gateways\BigQuery\BigQueryRepositoryClient;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Application\Interactors\Reddit\RedditApiManager;
use App\Application\Interactors\Reddit\RedditCrawlerManager;
use App\Application\Repositories\Csv\CsvRepository;
use App\Application\Repositories\RedditApiRepository;
use App\Application\UseCases\Reddit\RedditApiUseCase;
use App\Application\UseCases\Reddit\RedditCrawlerUseCase;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Application\UseCases\Csv\CsvUseCase;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use App\Gateways\Csv\CsvClient;
use App\Gateways\HttpRequest\GuzzleClient;
use App\Gateways\Translation\TranslationClient;
use App\Gateways\RedditApiRequest;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RedditApiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        // $this->app->bind(
        //     RedditCrawlerUseCase::class,
        //     RedditCrawlerManager::class
        // );

        // $this->app->bind(
        //     RedditApiUseCase::class,
        //     RedditApiManager::class
        // );

        // $this->app->bind(
        //     RedditApiRepository::class,
        //     RedditApiRequest::class
        // );



        // $this->app->when(
        //     [
        //         RedditCrawlerManager::class,
        //         RedditApiManager::class,
        //         RiskWordManager::class,
        //     ]
        // )
        //     ->needs(BigQueryUseCase::class)
        //     ->give(BigQueryManager::class);

        // $this->app->bind(
        //     TranslationUseCase::class,
        //     TranslationManager::class
        // );

        // $this->app->bind(
        //     TranslationRepository::class,
        //     TranslationClient::class
        // );

        // $this->app->bind(
        //     CsvUseCase::class,
        //     CsvManager::class
        // );

        // $this->app->bind(
        //     CsvRepository::class,
        //     CsvClient::class
        // );

        // $this->app->bind(
        //     RiskWordUseCase::class,
        //     RiskWordManager::class
        // );
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

    /**
     * 遅延ロード
     * このプロバイダにより提供されるサービスの取得
     *
     * @return array
     */
    public function provides()
    {
        return [RedditApiUseCase::class, RedditCrawlerUseCase::class, HttpRequestHttpClient::class];
    }
}
