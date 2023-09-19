<?php

namespace Hybrid\Log;

use Closure;
use Hybrid\Contracts\Arrayable;
use Hybrid\Contracts\Events\Dispatcher;
use Hybrid\Contracts\Jsonable;
use Hybrid\Log\Events\MessageLogged;
use Hybrid\Tools\Traits\Conditionable;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface {

    use Conditionable;

    /**
     * The underlying logger implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The event dispatcher instance.
     *
     * @var \Hybrid\Contracts\Events\Dispatcher|null
     */
    protected $dispatcher;

    /**
     * Any context to be added to logs.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Create a new log writer instance.
     *
     * @return void
     */
    public function __construct( LoggerInterface $logger, ?Dispatcher $dispatcher = null ) {
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function emergency( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log an alert message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function alert( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log a critical message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function critical( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log an error message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function error( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log a warning message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function warning( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log a notice to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function notice( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function info( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log a debug message to the logs.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function debug( $message, array $context = [] ): void {
        $this->writeLog( __FUNCTION__, $message, $context );
    }

    /**
     * Log a message to the logs.
     *
     * @param  string                                                                                       $level
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function log( $level, $message, array $context = [] ): void {
        $this->writeLog( $level, $message, $context );
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param  string                                                                                       $level
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    public function write( $level, $message, array $context = [] ): void {
        $this->writeLog( $level, $message, $context );
    }

    /**
     * Write a message to the log.
     *
     * @param  string                                                                                       $level
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @param  array                                                                                        $context
     */
    protected function writeLog( $level, $message, $context ): void {
        $this->logger->{$level}(
            $message = $this->formatMessage( $message ),
            $context = array_merge( $this->context, $context )
        );

        $this->fireLogEvent( $level, $message, $context );
    }

    /**
     * Add context to all future logs.
     *
     * @param  array $context
     * @return $this
     */
    public function withContext( array $context = [] ) {
        $this->context = array_merge( $this->context, $context );

        return $this;
    }

    /**
     * Flush the existing context array.
     *
     * @return $this
     */
    public function withoutContext() {
        $this->context = [];

        return $this;
    }

    /**
     * Register a new callback handler for when a log event is triggered.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function listen( Closure $callback ) {
        if ( ! isset( $this->dispatcher ) ) {
            throw new \RuntimeException( 'Events dispatcher has not been set.' );
        }

        $this->dispatcher->listen( MessageLogged::class, $callback );
    }

    /**
     * Fires a log event.
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    protected function fireLogEvent( $level, $message, array $context = [] ) {
        // If the event dispatcher is set, we will pass along the parameters to the
        // log listeners. These are useful for building profilers or other tools
        // that aggregate all of the log messages for a given "request" cycle.
        if ( isset( $this->dispatcher ) ) {
            $this->dispatcher->dispatch( new MessageLogged( $level, $message, $context ) );
        }
    }

    /**
     * Format the parameters for the logger.
     *
     * @param  \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message
     * @return string
     */
    protected function formatMessage( $message ) {
        if ( is_array( $message ) ) {
            return var_export( $message, true );
        }

        if ( $message instanceof Jsonable ) {
            return $message->toJson();
        }

        if ( $message instanceof Arrayable ) {
            return var_export( $message->toArray(), true );
        }

        return (string) $message;
    }

    /**
     * Get the underlying logger implementation.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Hybrid\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher() {
        return $this->dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @return void
     */
    public function setEventDispatcher( Dispatcher $dispatcher ) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dynamically proxy method calls to the underlying logger.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call( $method, $parameters ) {
        return $this->logger->{$method}( ...$parameters );
    }

}
