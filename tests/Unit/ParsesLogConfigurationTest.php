<?php

use Hybrid\Log\ParsesLogConfiguration;
use Monolog\Level;

/**
 * Exposes the trait's protected methods so they can be tested directly.
 */
function parser(): object {
    return new class() {

        use ParsesLogConfiguration;

        protected function getFallbackChannelName() {
            return 'fallback-channel';
        }

        public function callLevel( array $config ) {
            return $this->level( $config );
        }

        public function callActionLevel( array $config ) {
            return $this->actionLevel( $config );
        }

        public function callParseChannel( array $config ) {
            return $this->parseChannel( $config );
        }
    };
}

it( 'maps a string level onto a Monolog level', function ( string $name, Level $expected ) {
    expect( parser()->callLevel( [ 'level' => $name ] ) )->toBe( $expected );
} )->with( [
    [ 'debug', Level::Debug ],
    [ 'info', Level::Info ],
    [ 'notice', Level::Notice ],
    [ 'warning', Level::Warning ],
    [ 'error', Level::Error ],
    [ 'critical', Level::Critical ],
    [ 'alert', Level::Alert ],
    [ 'emergency', Level::Emergency ],
] );

it( 'defaults to debug when no level is configured', function () {
    expect( parser()->callLevel( [] ) )->toBe( Level::Debug );
} );

it( 'defaults the action level to debug', function () {
    expect( parser()->callActionLevel( [] ) )->toBe( Level::Debug );
} );

it( 'rejects an unknown level', function () {
    parser()->callLevel( [ 'level' => 'chatty' ] );
} )->throws( InvalidArgumentException::class, 'Invalid log level.' );

it( 'rejects an unknown action level', function () {
    parser()->callActionLevel( [ 'action_level' => 'chatty' ] );
} )->throws( InvalidArgumentException::class, 'Invalid log action level.' );

it( 'reads the channel name from config', function () {
    expect( parser()->callParseChannel( [ 'name' => 'billing' ] ) )->toBe( 'billing' );
} );

it( 'falls back to the fallback channel name', function () {
    expect( parser()->callParseChannel( [] ) )->toBe( 'fallback-channel' );
} );
