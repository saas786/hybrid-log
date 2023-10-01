<?php
/**
 * Helper functions.
 */

namespace Hybrid\Log;

use function Hybrid\app;

if ( ! function_exists( __NAMESPACE__ . '\\info' ) ) {
    /**
     * Write some information to the log.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    function info( $message, $context = [] ) {
        app( 'log' )->info( $message, $context );
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\logger' ) ) {
    /**
     * Log a debug message to the logs.
     *
     * @param  string|null $message
     * @param  array       $context
     * @return \Hybrid\Log\LogManager|null
     */
    function logger( $message = null, array $context = [] ) {
        if ( is_null( $message ) ) {
            return app( 'log' );
        }

        return app( 'log' )->debug( $message, $context );
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\logs' ) ) {
    /**
     * Get a log driver instance.
     *
     * @param  string|null $driver
     * @return \Hybrid\Log\LogManager|\Psr\Log\LoggerInterface
     */
    function logs( $driver = null ) {
        return $driver ? app( 'log' )->driver( $driver ) : app( 'log' );
    }
}
