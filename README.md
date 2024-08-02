# Hybrid Log

The Hybrid Logger package is a powerful and flexible tool for managing and recording log messages in Hybrid applications. It allows developers to configure various log channels, set log levels, and choose from different drivers. With options to tailor log storage and behavior, it facilitates effective debugging, error tracking, and application monitoring, enhancing the robustness and reliability of Hybrid-powered projects.

## Requirements

* PHP 8.0+.
* [Composer](https://getcomposer.org/) for managing PHP dependencies.


## Documentation

You need to register the service provider during your bootstrapping process:

```php
$slug->provider( \Hybrid\Log\Provider::class );
```

Sample `/config/logging.php`

```php
<?php

use Monolog\Handler\NullHandler;
use Monolog\Logger;
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
    'default'      => env( 'LOG_CHANNEL', 'stack' ),

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
    'channels'     => [
        'stack'      => [
            'driver'            => 'stack',
            'channels'          => [
					'single',
					'daily',
					// 'sentry',
				],
            'ignore_exceptions' => false,
        ],
        'single'     => [
            'driver' => 'single',
            'path'   => storage_path( 'logs/hybrid.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
        ],
        'daily'      => [
            'driver' => 'daily',
            'path'   => storage_path( 'logs/hybrid.log' ),
            'level'  => env( 'LOG_LEVEL', 'debug' ),
            'days'   => 14,
        ],
        'null'       => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],
        'emergency'  => [
            'path' => storage_path( 'logs/hybrid.log' ),
        ],
        'sentry'     => [
            'driver' => 'sentry',
            'level'  => Logger::ERROR, // The minimum monolog logging level at which this handler will be triggered
            'bubble' => true, // Whether the messages that are handled can bubble up the stack or not
        ],
    ],

];
```

Sample usage

```php
use Hybrid\Log\Facades\Log;

Log::emergency($message);
Log::alert($message);
Log::critical($message);
Log::error($message);
Log::warning($message);
Log::notice($message);
Log::info($message);
Log::debug($message);

Log::channel('single')->info('Something happened!');
Log::stack(['single', 'daily'])->info('Something happened!');
```

## Copyright and License

This project is licensed under the [GNU GPL](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

2008&thinsp;&ndash;&thinsp;2024 &copy; [Theme Hybrid](https://themehybrid.com).

## Other Licenses

Hybrid Log utilizes code from Illuminate.

<https://github.com/illuminate/log>

License: MIT - <https://opensource.org/licenses/MIT>
Copyright (c) Taylor Otwell
