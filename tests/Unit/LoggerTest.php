<?php

use Hybrid\Log\Events\MessageLogged;
use Hybrid\Log\Logger;
use Hybrid\Log\Tests\Fixtures\FakeDispatcher;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger as Monolog;

beforeEach( function () {
    $this->handler    = new TestHandler;
    $this->monolog    = new Monolog( 'testing', [ $this->handler ] );
    $this->dispatcher = new FakeDispatcher;
    $this->logger     = new Logger( $this->monolog, $this->dispatcher );
} );

it( 'writes each psr level through to the underlying logger', function ( string $method, Level $level ) {
    $this->logger->{$method}( 'a message' );

    expect( $this->handler->hasRecordThatContains( 'a message', $level ) )->toBeTrue();
} )->with( [
    [ 'emergency', Level::Emergency ],
    [ 'alert', Level::Alert ],
    [ 'critical', Level::Critical ],
    [ 'error', Level::Error ],
    [ 'warning', Level::Warning ],
    [ 'notice', Level::Notice ],
    [ 'info', Level::Info ],
    [ 'debug', Level::Debug ],
] );

it( 'writes an arbitrary level via log() and write()', function () {
    $this->logger->log( 'warning', 'via log' );
    $this->logger->write( 'error', 'via write' );

    expect( $this->handler->hasWarningThatContains( 'via log' ) )->toBeTrue()
        ->and( $this->handler->hasErrorThatContains( 'via write' ) )->toBeTrue();
} );

it( 'merges shared context into every record', function () {
    $this->logger->withContext( [ 'user_id' => 7 ] );
    $this->logger->info( 'hello', [ 'request_id' => 'abc' ] );

    expect( $this->handler->getRecords()[0]->context )
        ->toBe( [
            'user_id'    => 7,
            'request_id' => 'abc',
        ] );
} );

it( 'lets per-call context override shared context', function () {
    $this->logger->withContext( [ 'user_id' => 7 ] )->info( 'hello', [ 'user_id' => 9 ] );

    expect( $this->handler->getRecords()[0]->context['user_id'] )->toBe( 9 );
} );

it( 'forgets only the given keys with withoutContext()', function () {
    $this->logger->withContext( [
        'a' => 1,
        'b' => 2,
    ] )->withoutContext( [ 'a' ] );
    $this->logger->info( 'hello' );

    expect( $this->handler->getRecords()[0]->context )->toBe( [ 'b' => 2 ] );
} );

it( 'forgets all context when withoutContext() gets no keys', function () {
    $this->logger->withContext( [ 'a' => 1 ] )->withoutContext();
    $this->logger->info( 'hello' );

    expect( $this->handler->getRecords()[0]->context )->toBe( [] );
} );

it( 'dispatches a MessageLogged event carrying level, message and context', function () {
    $this->logger->withContext( [ 'shared' => true ] )->error( 'boom', [ 'code' => 500 ] );

    $events = $this->dispatcher->dispatchedOf( MessageLogged::class );

    expect( $events )->toHaveCount( 1 )
        ->and( $events[0]->level )->toBe( 'error' )
        ->and( $events[0]->message )->toBe( 'boom' )
        ->and( $events[0]->context )->toBe( [
            'shared' => true,
            'code'   => 500,
        ] );
} );

it( 'does not write or dispatch when the level is not being handled', function () {
    $handler = new TestHandler( Level::Error );
    $logger  = new Logger( new Monolog( 'testing', [ $handler ] ), $this->dispatcher );

    $logger->debug( 'ignored' );

    expect( $handler->getRecords() )->toBeEmpty()
        ->and( $this->dispatcher->dispatchedOf( MessageLogged::class ) )->toBeEmpty();
} );

it( 'registers a listener for MessageLogged via listen()', function () {
    $seen = null;

    $this->logger->listen( function ( MessageLogged $event ) use ( &$seen ) {
        $seen = $event->message;
    } );

    $this->logger->info( 'heard' );

    expect( $seen )->toBe( 'heard' );
} );

it( 'throws when listen() is called without a dispatcher', function () {
    ( new Logger( $this->monolog ) )->listen( fn() => null );
} )->throws( RuntimeException::class, 'Events dispatcher has not been set.' );

it( 'stringifies an array message', function () {
    $this->logger->info( [ 'a' => 1 ] );

    expect( $this->handler->getRecords()[0]->message )->toContain( "'a' => 1" );
} );

it( 'exposes and proxies to the underlying logger', function () {
    expect( $this->logger->getLogger() )->toBe( $this->monolog )
        ->and( $this->logger->getName() )->toBe( 'testing' ); // proxied via __call
} );
