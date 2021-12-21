<?php

namespace App\Providers;

use App\Application\Interactors\Translation\GoogleTranslationInvoker;
use App\Application\Interactors\Translation\TranslationManager;
use App\Application\Repositories\HttpRequest\HttpClient;
use App\Application\Repositories\Translation\TranslationRepository;
use App\Application\UseCases\Translation\TranslationInvoker;
use App\Application\UseCases\Translation\TranslationUseCase;
use App\Http\Controllers\Api\TranslationApiController;
use App\Gateways\HttpRequest\GuzzleClient;
use App\Gateways\Translation\TranslationClient;
use Illuminate\Support\ServiceProvider;


class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            TranslationUseCase::class,
            TranslationManager::class
        );

        $this->app->bind(
            TranslationInvoker::class,
            GoogleTranslationInvoker::class
        );

        $this->app->bind(
            TranslationRepository::class,
            TranslationClient::class
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
