<?php

namespace Hybrid\Log\Context;

use Hybrid\Contracts\Log\ContextLogProcessor as ContextLogProcessorContract;
use Hybrid\Core\ServiceProvider;
use Hybrid\Tools\Env;

// use Hybrid\Log\Facades\Context;
// use Hybrid\Queue\Events\JobProcessing;
// use Hybrid\Queue\Queue;

class ContextServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->scoped( Repository::class );

        if ( $this->app->runningInConsole() ) {
            $this->app->resolving( Repository::class, function ( Repository $repository ) {
                $context = Env::get( '__HYBRID_CORE_CONTEXT' );

                if ( $context && $context = json_decode( $context, associative: true ) ) {
                    $repository->hydrate( $context );
                }
            } );
        }

        $this->app->bind( ContextLogProcessorContract::class, fn() => new ContextLogProcessor );
    }

    /**
     * Boot the application services.
     *
     * @return void
     */
    public function boot() {
        /*
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $context = Context::dehydrate();

            return $context === null
            ? $payload
            : [
                ...$payload,
                'hybrid_core:log:context' => $context,
            ];
        });

        $this->app['events']->listen(function (JobProcessing $event) {
            Context::hydrate($event->job->payload()['hybrid_core:log:context'] ?? null);
        });
        */
    }
}
