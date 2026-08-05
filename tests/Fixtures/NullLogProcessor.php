<?php

namespace Hybrid\Log\Tests\Fixtures;

use Hybrid\Contracts\Log\ContextLogProcessor;
use Monolog\LogRecord;

/**
 * Stands in for ContextLogProcessor without touching the global container.
 */
class NullLogProcessor implements ContextLogProcessor {
    public function __invoke( LogRecord $record ): LogRecord {
        return $record;
    }
}
