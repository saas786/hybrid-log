<?php
/**
 * Helper functions.
 */

namespace Hybrid\Log;

use function Hybrid\app;

function log() {
    return app( 'hybrid/log' );
}
