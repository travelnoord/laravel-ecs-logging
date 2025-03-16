<?php

namespace Travelnoord\Logging;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->scoped(ContextManager::class);
    }
}
