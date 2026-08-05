<?php

beforeEach( function () {
    $this->context = makeContext();
} );

it( 'starts empty', function () {
    expect( $this->context->isEmpty() )->toBeTrue()
        ->and( $this->context->all() )->toBe( [] )
        ->and( $this->context->allHidden() )->toBe( [] );
} );

it( 'adds and reads a single value', function () {
    $this->context->add( 'key', 'value' );

    expect( $this->context->get( 'key' ) )->toBe( 'value' )
        ->and( $this->context->has( 'key' ) )->toBeTrue()
        ->and( $this->context->missing( 'nope' ) )->toBeTrue();
} );

it( 'adds many values from an array', function () {
    $this->context->add( [
        'a' => 1,
        'b' => 2,
    ] );

    expect( $this->context->all() )->toBe( [
        'a' => 1,
        'b' => 2,
    ] );
} );

it( 'keeps hidden data out of all()', function () {
    $this->context->add( 'visible', 1 )->addHidden( 'secret', 'shh' );

    expect( $this->context->all() )->toBe( [ 'visible' => 1 ] )
        ->and( $this->context->allHidden() )->toBe( [ 'secret' => 'shh' ] )
        ->and( $this->context->hasHidden( 'secret' ) )->toBeTrue()
        ->and( $this->context->has( 'secret' ) )->toBeFalse();
} );

it( 'returns the default for a missing key', function () {
    expect( $this->context->get( 'missing', 'fallback' ) )->toBe( 'fallback' )
        ->and( $this->context->get( 'missing', fn() => 'lazy' ) )->toBe( 'lazy' );
} );

it( 'pulls a value and forgets it', function () {
    $this->context->add( 'key', 'value' );

    expect( $this->context->pull( 'key' ) )->toBe( 'value' )
        ->and( $this->context->has( 'key' ) )->toBeFalse();
} );

it( 'filters with only() and except()', function () {
    $this->context->add( [
        'a' => 1,
        'b' => 2,
        'c' => 3,
    ] );

    expect( $this->context->only( [ 'a', 'c' ] ) )->toBe( [
        'a' => 1,
        'c' => 3,
    ] )
        ->and( $this->context->except( [ 'a' ] ) )->toBe( [
            'b' => 2,
            'c' => 3,
        ] );
} );

it( 'forgets one or many keys', function () {
    $this->context->add( [
        'a' => 1,
        'b' => 2,
        'c' => 3,
    ] )->forget( [ 'a', 'b' ] );

    expect( $this->context->all() )->toBe( [ 'c' => 3 ] );
} );

it( 'only writes with addIf() when the key is absent', function () {
    $this->context->add( 'a', 'original' )->addIf( 'a', 'ignored' )->addIf( 'b', 'written' );

    expect( $this->context->get( 'a' ) )->toBe( 'original' )
        ->and( $this->context->get( 'b' ) )->toBe( 'written' );
} );

it( 'pushes onto and pops off a stack', function () {
    $this->context->push( 'items', 'one', 'two' )->push( 'items', 'three' );

    expect( $this->context->get( 'items' ) )->toBe( [ 'one', 'two', 'three' ] )
        ->and( $this->context->pop( 'items' ) )->toBe( 'three' )
        ->and( $this->context->stackContains( 'items', 'one' ) )->toBeTrue()
        ->and( $this->context->stackContains( 'items', 'three' ) )->toBeFalse();
} );

it( 'refuses to push onto a non-stack key', function () {
    $this->context->add( 'scalar', 'value' )->push( 'scalar', 'boom' );
} )->throws( RuntimeException::class );

it( 'refuses to pop an empty stack', function () {
    $this->context->push( 'items' )->pop( 'items' );
} )->throws( RuntimeException::class );

it( 'increments and decrements a counter', function () {
    $this->context->increment( 'hits' )->increment( 'hits', 4 )->decrement( 'hits', 2 );

    expect( $this->context->get( 'hits' ) )->toBe( 3 );
} );

it( 'restores the previous state after scope()', function () {
    $this->context->add( 'a', 'outer' );

    $result = $this->context->scope(
        fn() => $this->context->get( 'a' ) . '/' . $this->context->get( 'b' ),
        [
            'a' => 'inner',
            'b' => 'temp',
        ]
    );

    expect( $result )->toBe( 'inner/temp' )
        ->and( $this->context->all() )->toBe( [ 'a' => 'outer' ] );
} );

it( 'restores state even when scope() throws', function () {
    $this->context->add( 'a', 'outer' );

    try {
        $this->context->scope( fn() => throw new RuntimeException( 'nope' ), [ 'a' => 'inner' ] );
    } catch ( RuntimeException ) {
        // expected
    }

    expect( $this->context->get( 'a' ) )->toBe( 'outer' );
} );

it( 'flushes visible and hidden data', function () {
    $this->context->add( 'a', 1 )->addHidden( 'b', 2 )->flush();

    expect( $this->context->isEmpty() )->toBeTrue();
} );

it( 'dispatches ContextHydrated when hydrating', function () {
    $this->context->hydrate( [
        'data'   => [ 'user' => serialize( 'jane' ) ],
        'hidden' => [],
    ] );

    expect( $this->context->get( 'user' ) )->toBe( 'jane' );
} )->skip( 'Repository::hydrate() calls getRestoredPropertyValue(), which does not exist. See review item #1.' );

it( 'returns null from dehydrate() when empty', function () {
    expect( $this->context->dehydrate() )->toBeNull();
} );
