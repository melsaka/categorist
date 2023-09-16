<?php

namespace Melsaka\Categorist;

use Illuminate\Support\ServiceProvider;

class CategoristServiceProvider extends ServiceProvider
{
    // package migrations
    private $migration = __DIR__ . '/database/migrations/';

    private $config = __DIR__ . '/config/categorist.php';


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->config, 'categorist');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom([ $this->migration ]);

        $this->publishes([ $this->config => config_path('categorist.php') ], 'categorist');
    }
}
