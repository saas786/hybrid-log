<?php

namespace Hybrid\Log;

use Hybrid\Core\ServiceProvider;

/**
 * Log provider class.
 */
class Provider extends ServiceProvider {

    /**
     * Register.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton( 'log', static fn( $app ) => new LogManager( $app ) );
    }

}
