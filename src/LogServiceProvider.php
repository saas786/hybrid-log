<?php

namespace Hybrid\Log;

use Hybrid\Core\ServiceProvider;

class LogServiceProvider extends ServiceProvider {
    /**
     * Register.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton( 'log', fn( $app ) => new LogManager( $app ) );
    }
}
