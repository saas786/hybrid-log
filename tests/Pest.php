<?php

/**
 * Pest configuration for themehybrid/hybrid-log.
 *
 * These are plain unit tests — no WordPress bootstrap is required for the
 * Logger / Repository / LogManager classes, so we do not bind the WP plugin
 * TestCase globally. If you later add integration tests that need WordPress,
 * scope the plugin's TestCase to that directory only, e.g.:
 *
 *     uses( \AlvaroDelera\PestWpPlugin\TestCase::class )->in( 'Integration' );
 */

use Hybrid\Log\Tests\Fixtures\FakeApplication;
use Hybrid\Log\Tests\Fixtures\FakeDispatcher;

/**
 * Build a Repository backed by a spy dispatcher.
 */
function makeContext(): \Hybrid\Log\Context\Repository {
    return new \Hybrid\Log\Context\Repository( new FakeDispatcher );
}

/**
 * Build a LogManager wired to an in-memory fake application.
 *
 * @param array $config Contents of the "logging" config namespace.
 */
function makeManager( array $config ): \Hybrid\Log\LogManager {
    return new \Hybrid\Log\LogManager( new FakeApplication( [ 'logging' => $config ] ) );
}
