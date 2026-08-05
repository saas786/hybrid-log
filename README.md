# Hybrid Log

PSR-3 logging for Hybrid Core, built on [Monolog](https://github.com/Seldaek/monolog).
Configure named channels, write to one or several at once, and attach contextual data that follows every log entry.

## Requirements

* PHP 8.2+
* [Composer](https://getcomposer.org/)

## Installation

```sh
composer require themehybrid/hybrid-log
```

Register both service providers during bootstrap. `LogServiceProvider` is required; `ContextServiceProvider` backs the `Context` facade and is expected by the log manager.

```php
$slug->provider( \Hybrid\Log\LogServiceProvider::class );
$slug->provider( \Hybrid\Log\Context\ContextServiceProvider::class );
```

## Configuration

Channels are defined in `/config/logging.php`. `default` names the channel used when you don't pick one.

```php
<?php

use Monolog\Handler\NullHandler;
use function Hybrid\storage_path;
use function Hybrid\Tools\env;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default'  => env( 'LOG_CHANNEL', 'stack' ),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

	'deprecations' => [
		'channel' => env( 'LOG_DEPRECATIONS_CHANNEL', 'null' ),
		'trace'   => false,
	],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, hybrid uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [

        'stack'     => [
            'driver'            => 'stack',
            'channels'          => explode( ',', (string) env( 'LOG_STACK', 'single,daily' ) ),
            'ignore_exceptions' => false,
        ],

        'single'    => [
            'driver' => 'single',
            'path'   => storage_path( 'logs/hybrid-core.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],

        'daily'     => [
            'driver' => 'daily',
            'path'   => storage_path( 'logs/hybrid-core.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
            'days'   => 14,
        ],

        'null'      => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        // Used when no other channel can be resolved. Keep it simple.
        'emergency' => [
            'path' => storage_path( 'logs/hybrid-core.log' ),
        ],

        'sentry'     => [
            'driver' => 'sentry',
            'level'  => Logger::ERROR, // The minimum monolog logging level at which this handler will be triggered
            'bubble' => true, // Whether the messages that are handled can bubble up the stack or not
        ],
    ],

];
```

### Drivers

| Driver | Writes to |
| --- | --- |
| `single` | One file |
| `daily` | One file per day, rotated after `days` |
| `stack` | Several channels at once, listed in `channels` |
| `syslog` | System log |
| `errorlog` | PHP's error log |
| `slack` | A Slack incoming webhook (`url`, `username`, `emoji`) |
| `monolog` | Any Monolog handler, named in `handler` with `with` for constructor args |
| `custom` | A factory class named in `via`, returning a Monolog instance |

`level` accepts any PSR-3 level name and defaults to `debug`.

## Writing logs

```php
use Hybrid\Log\Facades\Log;

Log::emergency( $message );
Log::alert( $message );
Log::critical( $message );
Log::error( $message );
Log::warning( $message );
Log::notice( $message );
Log::info( $message );
Log::debug( $message );
```

Pass contextual data as a second argument:

```php
Log::info( 'Order shipped.', [ 'order_id' => 42 ] );
```

### Choosing a channel

```php
Log::channel( 'daily' )->info( 'Something happened.' );

// Write to several channels in one call.
Log::stack( [ 'single', 'daily' ] )->info( 'Something happened.' );

// Build a channel on the fly, without adding it to config.
Log::build( [
    'driver' => 'single',
    'path'   => storage_path( 'logs/audit.log' ),
] )->info( 'Something happened.' );
```

### Sharing context

`withContext()` adds data to every subsequent entry on that channel. `shareContext()` does the same across all channels, including ones resolved later.

```php
Log::withContext( [ 'request_id' => $id ] );
Log::shareContext( [ 'tenant' => $tenant ] );
```

## Context

The `Context` facade holds data that is attached to every log entry automatically, so you don't have to thread it through your call stack.

```php
use Hybrid\Log\Facades\Context;

Context::add( 'user_id', 7 );
Context::add( [ 'locale' => 'en_US', 'theme' => 'default' ] );

Context::get( 'user_id' );        // 7
Context::has( 'user_id' );        // true
Context::forget( 'user_id' );
```

Hidden context is kept out of log entries. Use it for values you want available to your own code but not written to disk.

```php
Context::addHidden( 'api_token', $token );
Context::getHidden( 'api_token' );
```

Other useful methods:

```php
Context::addIf( 'key', 'value' );       // Only writes when the key is absent.
Context::push( 'breadcrumbs', 'step' ); // Appends to an array.
Context::pop( 'breadcrumbs' );
Context::increment( 'queries' );
Context::only( [ 'user_id' ] );
Context::except( [ 'user_id' ] );
Context::flush();
```

`scope()` runs a callback with extra context, then restores the previous state — even if the callback throws.

```php
$result = Context::scope( function () {
    Log::info( 'Inside the scope.' ); // Includes job_id.

    return $this->handle();
}, [ 'job_id' => $id ] );
```

Each `add`, `push` and `increment` variant has a `Hidden` counterpart: `addHiddenIf()`, `pushHidden()`, `popHidden()`, `onlyHidden()`, and so on.

## Helper functions

```php
use function Hybrid\Log\logger;
use function Hybrid\Log\logs;
use function Hybrid\Log\context;
use function Hybrid\Log\info;

logger( 'A debug message.', [ 'key' => 'value' ] );
logger()->error( 'Something broke.' );  // No arguments returns the logger.

logs();            // The LogManager.
logs( 'daily' );   // A specific channel.

context( 'user_id' );              // Read.
context( [ 'user_id' => 7 ] );     // Write.
context();                         // The Context repository.

info( 'An informational message.' );
```

## Listening for log entries

```php
Log::listen( function ( \Hybrid\Log\Events\MessageLogged $event ) {
    // $event->level, $event->message, $event->context
} );
```

## Testing

```sh
composer install
composer test
```

## Copyright and License

This project is licensed under the [GNU GPL](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

2008&thinsp;&ndash;&thinsp;2026 &copy; [Theme Hybrid](https://themehybrid.com).

## Third-Party Licenses

Hybrid Log utilizes code from the illuminate/log package.

Repository: <https://github.com/illuminate/log>

License: MIT License - <https://opensource.org/licenses/MIT>

Copyright (c) Taylor Otwell
