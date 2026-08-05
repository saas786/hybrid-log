<?php

namespace Hybrid\Log\Tests\Fixtures;

use ArrayAccess;

/**
 * Dot-notation config store — LogManager reads $app['config']['logging.default'].
 */
class FakeConfig implements ArrayAccess {
    public function __construct( protected array $items = [] ) {}

    public function offsetExists( $offset ): bool {
        return $this->offsetGet( $offset ) !== null;
    }

    public function offsetGet( $offset ): mixed {
        $value = $this->items;

        foreach ( explode( '.', (string) $offset ) as $segment ) {
            if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
                return null;
            }

            $value = $value[ $segment ];
        }

        return $value;
    }

    public function offsetSet( $offset, $value ): void {
        $segments = explode( '.', (string) $offset );
        $target   = &$this->items;

        foreach ( $segments as $segment ) {
            if ( ! isset( $target[ $segment ] ) || ! is_array( $target[ $segment ] ) ) {
                $target[ $segment ] = [];
            }

            $target = &$target[ $segment ];
        }

        $target = $value;
    }

    public function offsetUnset( $offset ): void {
        unset( $this->items[ $offset ] );
    }
}
