<?php

use Hybrid\Log\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

function logPath(): string {
    return rtrim( sys_get_temp_dir(), '/\\' ) . DIRECTORY_SEPARATOR . 'hybrid-log-tests'
        . DIRECTORY_SEPARATOR . 'test.log';
}

beforeEach( function () {
    $this->manager = makeManager( [
        'default'  => 'single',
        'channels' => [
            'single'    => [
                'driver' => 'single',
                'path'   => logPath(),
                'level'  => 'debug',
            ],
            'daily'     => [
                'driver' => 'daily',
                'path'   => logPath(),
                'days'   => 3,
            ],
            'errors'    => [
                'driver' => 'single',
                'path'   => logPath(),
                'level'  => 'error',
            ],
            'stack'     => [
                'driver'   => 'stack',
                'channels' => [ 'single', 'daily' ],
            ],
            'emergency' => [ 'path' => logPath() ],
        ],
    ] );
} );

it( 'reads and writes the default driver name', function () {
    expect( $this->manager->getDefaultDriver() )->toBe( 'single' );

    $this->manager->setDefaultDriver( 'daily' );

    expect( $this->manager->getDefaultDriver() )->toBe( 'daily' );
} );

it( 'resolves a channel as a Logger', function () {
    expect( $this->manager->channel( 'single' ) )->toBeInstanceOf( Logger::class );
} );

it( 'caches resolved channels', function () {
    expect( $this->manager->channel( 'single' ) )->toBe( $this->manager->channel( 'single' ) )
        ->and( $this->manager->getChannels() )->toHaveKey( 'single' );
} );

it( 'forgets a resolved channel', function () {
    $first = $this->manager->channel( 'single' );

    $this->manager->forgetChannel( 'single' );

    expect( $this->manager->getChannels() )->not->toHaveKey( 'single' )
        ->and( $this->manager->channel( 'single' ) )->not->toBe( $first );
} );

it( 'builds a single driver on a StreamHandler', function () {
    $handlers = $this->manager->channel( 'single' )->getLogger()->getHandlers();

    expect( $handlers[0] )->toBeInstanceOf( StreamHandler::class );
} );

it( 'builds a daily driver on a RotatingFileHandler honouring days', function () {
    $handler = $this->manager->channel( 'daily' )->getLogger()->getHandlers()[0];

    expect( $handler )->toBeInstanceOf( RotatingFileHandler::class );
} );

it( 'applies the configured level to the handler', function () {
    $handler = $this->manager->channel( 'errors' )->getLogger()->getHandlers()[0];

    expect( $handler->getLevel() )->toBe( \Monolog\Level::Error )
        ->and( $handler->isHandling( new \Monolog\LogRecord(
            new DateTimeImmutable, 'errors', \Monolog\Level::Debug, 'x'
        ) ) )->toBeFalse();
} );

it( 'aggregates child handlers into a stack driver', function () {
    $handlers = $this->manager->channel( 'stack' )->getLogger()->getHandlers();

    expect( $handlers )->toHaveCount( 2 );
} );

it( 'builds an ad hoc stack from channel names', function () {
    expect( $this->manager->stack( [ 'single', 'daily' ] ) )->toBeInstanceOf( Logger::class );
} );

it( 'builds an on demand channel from raw config', function () {
    $logger = $this->manager->build( [
        'driver' => 'single',
        'path'   => logPath(),
    ] );

    expect( $logger )->toBeInstanceOf( Logger::class )
        ->and( $logger->getLogger()->getHandlers()[0] )->toBeInstanceOf( StreamHandler::class );
} );

it( 'resolves a custom driver registered with extend()', function () {
    $this->manager->extend( 'custom-thing', fn() => new Monolog( 'custom' ) );

    $manager = makeManager( [
        'default'  => 'mine',
        'channels' => [ 'mine' => [ 'driver' => 'custom-thing' ] ],
    ] );

    $manager->extend( 'custom-thing', fn() => new Monolog( 'custom' ) );

    expect( $manager->channel( 'mine' )->getLogger()->getName() )->toBe( 'custom' );
} );

it( 'falls back to the emergency logger for an unknown channel', function () {
    // resolve() throws InvalidArgumentException; get() swallows it and returns
    // the emergency logger rather than bubbling up. See review item #5.
    expect( $this->manager->channel( 'does-not-exist' ) )->toBeInstanceOf( Logger::class );
} );

it( 'shares context across already resolved channels', function () {
    $channel = $this->manager->channel( 'single' );

    $this->manager->shareContext( [ 'tenant' => 'acme' ] );

    expect( $this->manager->sharedContext() )->toBe( [ 'tenant' => 'acme' ] );
} );

it( 'applies shared context to channels resolved afterwards', function () {
    $this->manager->shareContext( [ 'tenant' => 'acme' ] );

    $reflected = new ReflectionProperty( Logger::class, 'context' );

    expect( $reflected->getValue( $this->manager->channel( 'single' ) ) )
        ->toBe( [ 'tenant' => 'acme' ] );
} );

it( 'flushes the shared context', function () {
    $this->manager->shareContext( [ 'tenant' => 'acme' ] )->flushSharedContext();

    expect( $this->manager->sharedContext() )->toBe( [] );
} );

it( 'proxies psr level calls to the default driver', function () {
    $this->manager->info( 'through the manager' );

    expect( file_get_contents( logPath() ) )->toContain( 'through the manager' );
} );

afterEach( function () {
    if ( is_file( logPath() ) ) {
        unlink( logPath() );
    }
} );
