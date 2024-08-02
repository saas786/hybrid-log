<?php

namespace Hybrid\Log\Context;

use __PHP_Incomplete_Class;
use Hybrid\Contracts\Events\Dispatcher;
use Hybrid\Log\Context\Events\ContextDehydrating as Dehydrating;
use Hybrid\Log\Context\Events\ContextHydrated as Hydrated;
use Hybrid\Tools\Traits\Conditionable;
use Hybrid\Tools\Traits\Macroable;

class Repository {

    use Conditionable;
    use Macroable;

    /**
     * The event dispatcher instance.
     *
     * @var \Hybrid\Events\Dispatcher
     */
    protected $events;

    /**
     * The contextual data.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * The hidden contextual data.
     *
     * @var array<string, mixed>
     */
    protected $hidden = [];

    /**
     * The callback that should handle unserialize exceptions.
     *
     * @var callable|null
     */
    protected static $handleUnserializeExceptionsUsing;

    /**
     * Create a new Context instance.
     */
    public function __construct( Dispatcher $events ) {
        $this->events = $events;
    }

    /**
     * Determine if the given key exists.
     *
     * @param string $key
     * @return bool
     */
    public function has( $key ) {
        return array_key_exists( $key, $this->data );
    }

    /**
     * Determine if the given key exists within the hidden context data.
     *
     * @param string $key
     * @return bool
     */
    public function hasHidden( $key ) {
        return array_key_exists( $key, $this->hidden );
    }

    /**
     * Retrieve all the context data.
     *
     * @return array<string, mixed>
     */
    public function all() {
        return $this->data;
    }

    /**
     * Retrieve all the hidden context data.
     *
     * @return array<string, mixed>
     */
    public function allHidden() {
        return $this->hidden;
    }

    /**
     * Retrieve the given key's value.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get( $key, $default = null ) {
        return $this->data[ $key ] ?? value( $default );
    }

    /**
     * Retrieve the given key's hidden value.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getHidden( $key, $default = null ) {
        return $this->hidden[ $key ] ?? value( $default );
    }

    /**
     * Retrieve the given key's value and then forget it.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function pull( $key, $default = null ) {
        return tap( $this->get( $key, $default ), function () use ( $key ) {
            $this->forget( $key );
        } );
    }

    /**
     * Retrieve the given key's hidden value and then forget it.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function pullHidden( $key, $default = null ) {
        return tap( $this->getHidden( $key, $default ), function () use ( $key ) {
            $this->forgetHidden( $key );
        } );
    }

    /**
     * Retrieve only the values of the given keys.
     *
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function only( $keys ) {
        return array_intersect_key( $this->data, array_flip( $keys ) );
    }

    /**
     * Retrieve only the hidden values of the given keys.
     *
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function onlyHidden( $keys ) {
        return array_intersect_key( $this->hidden, array_flip( $keys ) );
    }

    /**
     * Add a context value.
     *
     * @param string|array<string, mixed> $key
     * @param mixed                       $value
     * @return $this
     */
    public function add( $key, $value = null ) {
        $this->data = array_merge(
            $this->data,
            is_array( $key ) ? $key : [ $key => $value ]
        );

        return $this;
    }

    /**
     * Add a hidden context value.
     *
     * @param string|array<string, mixed> $key
     * @param mixed                       $value
     * @return $this
     */
    public function addHidden( $key, #[\SensitiveParameter]
        $value = null ) {
        $this->hidden = array_merge(
            $this->hidden,
            is_array( $key ) ? $key : [ $key => $value ]
        );

        return $this;
    }

    /**
     * Forget the given context key.
     *
     * @param string|array<int, string> $key
     * @return $this
     */
    public function forget( $key ) {
        foreach ( (array) $key as $k ) {
            unset( $this->data[ $k ] );
        }

        return $this;
    }

    /**
     * Forget the given hidden context key.
     *
     * @param string|array<int, string> $key
     * @return $this
     */
    public function forgetHidden( $key ) {
        foreach ( (array) $key as $k ) {
            unset( $this->hidden[ $k ] );
        }

        return $this;
    }

    /**
     * Add a context value if it does not exist yet.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function addIf( $key, $value ) {
        if ( ! $this->has( $key ) ) {
            $this->add( $key, $value );
        }

        return $this;
    }

    /**
     * Add a hidden context value if it does not exist yet.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function addHiddenIf( $key, #[\SensitiveParameter]
        $value ) {
        if ( ! $this->hasHidden( $key ) ) {
            $this->addHidden( $key, $value );
        }

        return $this;
    }

    /**
     * Push the given values onto the key's stack.
     *
     * @param string $key
     * @param mixed  ...$values
     * @return $this
     * @throws \RuntimeException
     */
    public function push( $key, ...$values ) {
        if ( ! $this->isStackable( $key ) ) {
            throw new \RuntimeException( "Unable to push value onto context stack for key [{$key}]." );
        }

        // Note: Downgraded it to ensure PHP 8.0 compatibility,
        // as it relies on the array unpacking feature with the spread operator (...),
        // which was introduced in PHP 7.4 for arrays but could not be used in such a context until PHP >=8.1.
        // @see https://wiki.php.net/rfc/array_unpacking_string_keys
        /*
        $this->data[ $key ] = [
            ...$this->data[ $key ] ?? [],
            ...$values,
        ];
         */
        $this->data[ $key ] = array_merge( is_array( $this->data[ $key ] ?? [] ) ? $this->data[ $key ] ?? [] : iterator_to_array( $this->data[ $key ] ?? [] ), $values );

        return $this;
    }

    /**
     * Push the given hidden values onto the key's stack.
     *
     * @param string $key
     * @param mixed  ...$values
     * @return $this
     * @throws \RuntimeException
     */
    public function pushHidden( $key, ...$values ) {
        if ( ! $this->isHiddenStackable( $key ) ) {
            throw new \RuntimeException( "Unable to push value onto hidden context stack for key [{$key}]." );
        }

        // Note: Downgraded it to ensure PHP 8.0 compatibility,
        // as it relies on the array unpacking feature with the spread operator (...),
        // which was introduced in PHP 7.4 for arrays but could not be used in such a context until PHP >=8.1.
        // @see https://wiki.php.net/rfc/array_unpacking_string_keys
        /*
        $this->hidden[ $key ] = [
            ...$this->hidden[ $key ] ?? [],
            ...$values,
        ];
         */
        $this->hidden[ $key ] = array_merge( is_array( $this->hidden[ $key ] ?? [] ) ? $this->hidden[ $key ] ?? [] : iterator_to_array( $this->hidden[ $key ] ?? [] ), $values );

        return $this;
    }

    /**
     * Determine if a given key can be used as a stack.
     *
     * @param string $key
     * @return bool
     */
    protected function isStackable( $key ) {
        return ! $this->has( $key ) ||
            ( is_array( $this->data[ $key ] ) && array_is_list( $this->data[ $key ] ) );
    }

    /**
     * Determine if a given key can be used as a hidden stack.
     *
     * @param string $key
     * @return bool
     */
    protected function isHiddenStackable( $key ) {
        return ! $this->hasHidden( $key ) ||
            ( is_array( $this->hidden[ $key ] ) && array_is_list( $this->hidden[ $key ] ) );
    }

    /**
     * Determine if the repository is empty.
     *
     * @return bool
     */
    public function isEmpty() {
        return $this->all() === [] && $this->allHidden() === [];
    }

    /**
     * Execute the given callback when context is about to be dehydrated.
     *
     * @param callable $callback
     * @return $this
     */
    public function dehydrating( $callback ) {
        $this->events->listen( static fn( Dehydrating $event ) => $callback( $event->context ) );

        return $this;
    }

    /**
     * Execute the given callback when context has been hydrated.
     *
     * @param callable $callback
     * @return $this
     */
    public function hydrated( $callback ) {
        $this->events->listen( static fn( Hydrated $event ) => $callback( $event->context ) );

        return $this;
    }

    /**
     * Handle unserialize exceptions using the given callback.
     *
     * @param callable|null $callback
     * @return static
     */
    public function handleUnserializeExceptionsUsing( $callback ) {
        static::$handleUnserializeExceptionsUsing = $callback;

        return $this;
    }

    /**
     * Flush all context data.
     *
     * @return $this
     */
    public function flush() {
        $this->data   = [];
        $this->hidden = [];

        return $this;
    }

    /**
     * Dehydrate the context data.
     *
     * @return ?array
     * @internal
     */
    public function dehydrate() {
        $instance = ( new static( $this->events ) )
            ->add( $this->all() )
            ->addHidden( $this->allHidden() );

        $instance->events->dispatch( new Dehydrating( $instance ) );

        $serialize = static fn( $value ) => serialize( $instance->getSerializedPropertyValue( $value, withRelations: false ) );

        return $instance->isEmpty() ? null : [
            'data'   => array_map( $serialize, $instance->all() ),
            'hidden' => array_map( $serialize, $instance->allHidden() ),
        ];
    }

    /**
     * Hydrate the context instance.
     *
     * @param ?array $context
     * @return $this
     * @throws \RuntimeException
     * @internal
     */
    public function hydrate( $context ) {
        $unserialize = function ( $value, $key, $hidden ) {
            try {
                return tap( $this->getRestoredPropertyValue( unserialize( $value ) ), static function ( $value ) {
                    if ( $value instanceof __PHP_Incomplete_Class ) {
                        throw new \RuntimeException( 'Value is incomplete class: ' . json_encode( $value ) );
                    }
                } );
            } catch ( \Throwable $e ) {
                if ( null !== static::$handleUnserializeExceptionsUsing ) {
                    return ( static::$handleUnserializeExceptionsUsing )( $e, $key, $value, $hidden );
                }

                throw $e;
            }
        };

        [$data, $hidden] = [
            collect( $context['data'] ?? [] )->map( static fn( $value, $key ) => $unserialize( $value, $key, false ) )->all(),
            collect( $context['hidden'] ?? [] )->map( static fn( $value, $key ) => $unserialize( $value, $key, true ) )->all(),
        ];

        $this->events->dispatch( new Hydrated(
            $this->flush()->add( $data )->addHidden( $hidden )
        ) );

        return $this;
    }

}
