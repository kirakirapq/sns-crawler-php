<?php

namespace App\Providers;

use App\Application\Interactors\RiskWord\RiskWordInvoker;
use App\Application\Interactors\RiskWord\RiskWordManager;
use App\Application\UseCases\RiskWord\RiskWordBaseInvoker;
use App\Application\UseCases\RiskWord\RiskWordUseCase;
use Illuminate\Support\ServiceProvider;


class RiskWordServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            RiskWordUseCase::class,
            RiskWordManager::class
        );

        $this->app->bind(
            RiskWordBaseInvoker::class,
            RiskWordInvoker::class
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
