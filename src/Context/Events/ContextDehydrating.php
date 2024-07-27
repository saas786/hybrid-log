<?php

namespace Hybrid\Log\Context\Events;

class ContextDehydrating {

    /**
     * The context instance.
     *
     * @var \Hybrid\Log\Context\Repository
     */
    public $context;

    /**
     * Create a new event instance.
     *
     * @param \Hybrid\Log\Context\Repository $context
     */
    public function __construct( $context ) {
        $this->context = $context;
    }

}
