<?php

namespace Hybrid\Log\Context;

use Hybrid\Core\ServiceProvider;

class ContextServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->scoped( Repository::class );
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

            return $context === null ? $payload : [
                ...$payload,
                'hybrid:log:context' => $context,
            ];
        });

        $this->app['events']->listen(function (JobProcessing $event) {
            Context::hydrate($event->job->payload()['hybrid:log:context'] ?? null);
        });
        */
    }

}
