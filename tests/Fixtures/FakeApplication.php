<?php

namespace Hybrid\Log\Tests\Fixtures;

use ArrayAccess;
use Hybrid\Log\Context\Repository;
use InvalidArgumentException;

/**
 * The smallest thing LogManager will accept as an application.
 *
 * LogManager only ever touches: $app['config'], $app['events'], make(),
 * bound(), environment(), storagePath() and runningUnitTests().
 */
class FakeApplication implements ArrayAccess {
    public FakeConfig $config;

    public FakeDispatcher $events;

    /** @var array<string, mixed> */
    protected array $bindings = [];

    protected bool $runningUnitTests = true;

    public function __construct( array $config = [], ?string $storagePath = null ) {
        $this->config      = new FakeConfig( $config );
        $this->events      = new FakeDispatcher;
        $this->storagePath = $storagePath ?? sys_get_temp_dir() . '/hybrid-log-tests';

        // LogManager pushes this onto every resolved channel.
        $this->bindings[ \Hybrid\Contracts\Log\ContextLogProcessor::class ] = new NullLogProcessor;
        $this->bindings[ Repository::class ]                                = new Repository( $this->events );
    }

    protected string $storagePath;

    public function bind( string $abstract, $concrete ): void {
        $this->bindings[ $abstract ] = $concrete;
    }

    public function make( $abstract, array $parameters = [] ) {
        if ( isset( $this->bindings[ $abstract ] ) ) {
            $binding = $this->bindings[ $abstract ];

            return $binding instanceof \Closure ? $binding( $this, $parameters ) : $binding;
        }

        if ( class_exists( $abstract ) ) {
            return new $abstract( ...array_values( $parameters ) );
        }

        throw new InvalidArgumentException( "Unresolvable [{$abstract}]." );
    }

    public function bound( $abstract ) {
        return isset( $this->bindings[ $abstract ] );
    }

    public function environment() {
        return 'testing';
    }

    public function storagePath() {
        return $this->storagePath;
    }

    public function runningInConsole() {
        return true;
    }

    public function runningUnitTests() {
        return $this->runningUnitTests;
    }

    public function setRunningUnitTests( bool $value ): static {
        $this->runningUnitTests = $value;

        return $this;
    }

    public function offsetExists( $offset ): bool {
        return in_array( $offset, [ 'config', 'events' ], true ) || isset( $this->bindings[ $offset ] );
    }

    public function offsetGet( $offset ): mixed {
        return match ( $offset ) {
            'config' => $this->config,
            'events' => $this->events,
            default => $this->bindings[ $offset ] ?? null,
        };
    }

    public function offsetSet( $offset, $value ): void {
        $this->bindings[ $offset ] = $value;
    }

    public function offsetUnset( $offset ): void {
        unset( $this->bindings[ $offset ] );
    }
}
