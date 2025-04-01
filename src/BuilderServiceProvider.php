<?php
namespace Arhx\Builder;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class BuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'builder');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/builder'),
        ], 'builder-views');

        $this->publishes([
            __DIR__.'/../public/build' => public_path('vendor/builder'),
        ], 'builder-assets');

        /*
        Vite::useBuildDirectory('vendor/builder');
        */
    }
}
