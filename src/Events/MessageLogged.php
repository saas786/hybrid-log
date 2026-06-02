<?php

namespace Hybrid\Log\Events;

class MessageLogged {
    /**
     * Create a new event instance.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function __construct(
        public $level,
        public $message,
        public array $context = []
    ) {}

}
