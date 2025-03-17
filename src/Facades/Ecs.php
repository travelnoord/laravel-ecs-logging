<?php

namespace Travelnoord\Logging\Facades;

use Illuminate\Support\Facades\Facade;
use Travelnoord\Logging\ContextManager;

/**
 * @mixin ContextManager
 */
class Ecs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContextManager::class;
    }
}
