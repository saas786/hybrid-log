<?php

namespace Hybrid\Log\Tests\Fixtures;

use Hybrid\Contracts\Events\Dispatcher;

/**
 * A minimal, recording event dispatcher.
 *
 * Records everything dispatched so tests can assert on it, and supports the
 * closure-with-typehint listener style used by Repository::dehydrating().
 */
class FakeDispatcher implements Dispatcher {
    /** @var array<int, object|string> */
    public array $dispatched = [];

    /** @var array<string, array<int, callable>> */
    protected array $listeners = [];

    public function listen( $events, $listener = null ) {
        // Repository passes a single closure whose first parameter typehint is
        // the event class, mirroring the framework's inferred-event style.
        if ( null === $listener && $events instanceof \Closure ) {
            $parameters = ( new \ReflectionFunction( $events ) )->getParameters();
            $type       = $parameters[0]->getType();

            $listener = $events;
            $events   = $type instanceof \ReflectionNamedType ? $type->getName() : '*';
        }

        foreach ( (array) $events as $event ) {
            $this->listeners[ $event ][] = $listener;
        }
    }

    public function dispatch( $event, $payload = [], $halt = false ) {
        $this->dispatched[] = $event;

        $name = is_object( $event ) ? $event::class : $event;

        foreach ( $this->listeners[ $name ] ?? [] as $listener ) {
            $listener( $event );
        }

        return null;
    }

    /**
     * All dispatched events of the given class.
     *
     * @param class-string $class
     *
     * @return array<int, object>
     */
    public function dispatchedOf( string $class ): array {
        return array_values( array_filter(
            $this->dispatched,
            static fn( $event ) => $event instanceof $class
        ) );
    }

    public function hasListeners( $eventName ) {
        return ! empty( $this->listeners[ $eventName ] );
    }

    public function subscribe( $subscriber ) {}

    public function until( $event, $payload = [] ) {
        return null;
    }

    public function push( $event, $payload = [] ) {}

    public function flush( $event ) {}

    public function forget( $event ) {
        unset( $this->listeners[ $event ] );
    }

    public function forgetPushed() {}
}
