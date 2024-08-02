<?php

namespace Hybrid\Log\Facades;

use Hybrid\Core\Facades\Facade;

/**
 * @see \Hybrid\Log\LogManager
 *
 * @method static \Psr\Log\LoggerInterface build(array $config)
 * @method static \Psr\Log\LoggerInterface stack(array $channels, string|null $channel = null)
 * @method static \Psr\Log\LoggerInterface channel(string|null $channel = null)
 * @method static \Psr\Log\LoggerInterface driver(string|null $driver = null)
 * @method static \Hybrid\Log\LogManager shareContext(array $context)
 * @method static array sharedContext()
 * @method static \Hybrid\Log\LogManager flushSharedContext()
 * @method static string|null getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static \Hybrid\Log\LogManager extend(string $driver, \Closure $callback)
 * @method static void forgetChannel(string|null $driver = null)
 * @method static array getChannels()
 * @method static void emergency(string|\Stringable $message, array $context = [])
 * @method static void alert(string|\Stringable $message, array $context = [])
 * @method static void critical(string|\Stringable $message, array $context = [])
 * @method static void error(string|\Stringable $message, array $context = [])
 * @method static void warning(string|\Stringable $message, array $context = [])
 * @method static void notice(string|\Stringable $message, array $context = [])
 * @method static void info(string|\Stringable $message, array $context = [])
 * @method static void debug(string|\Stringable $message, array $context = [])
 * @method static void log(mixed $level, string|\Stringable $message, array $context = [])
 * @method static \Hybrid\Log\LogManager setApplication(\Hybrid\Contracts\Core\Application $app)
 * @method static void write(string $level, \Hybrid\Contracts\Arrayable|\Hybrid\Contracts\Jsonable|\Hybrid\Tools\Stringable|array|string $message, array $context = [])
 * @method static \Hybrid\Log\Logger withContext(array $context = [])
 * @method static \Hybrid\Log\Logger withoutContext()
 * @method static void listen(\Closure $callback)
 * @method static \Psr\Log\LoggerInterface getLogger()
 * @method static \Hybrid\Contracts\Events\Dispatcher getEventDispatcher()
 * @method static void setEventDispatcher(\Hybrid\Contracts\Events\Dispatcher $dispatcher)
 * @method static \Hybrid\Log\Logger|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \Hybrid\Log\Logger|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 */
class Log extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'log';
    }

}
