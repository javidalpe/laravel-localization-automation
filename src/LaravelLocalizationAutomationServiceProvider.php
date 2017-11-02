<?php

namespace Javidalpe\LaravelLocalizationAutomation;

use Illuminate\Support\ServiceProvider;
use Javidalpe\LaravelLocalizationAutomation\Commands\AutoTranslateCommand;

class LaravelLocalizationAutomationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoTranslateCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
