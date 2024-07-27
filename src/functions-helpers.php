<?php
/**
 * Helper functions.
 */

namespace Hybrid\Log;

use Hybrid\Log\Context\Repository as ContextRepository;
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
     * @return ($message is null ? \Hybrid\Log\LogManager : null)
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
     * @return ($driver is null ? \Hybrid\Log\LogManager : \Psr\Log\LoggerInterface)
     */
    function logs( $driver = null ) {
        return $driver ? app( 'log' )->driver( $driver ) : app( 'log' );
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\context' ) ) {
    /**
     * Get / set the specified context value.
     *
     * @param  array|string|null $key
     * @param  mixed             $default
     * @return ($key is string ? mixed : \Hybrid\Log\Context\Repository)
     */
    function context( $key = null, $default = null ) {
        $context = app( ContextRepository::class );

        return match ( true ) {
            is_null( $key ) => $context,
            is_array( $key ) => $context->add( $key ),
            default => $context->get( $key, $default ),
        };
    }
}
