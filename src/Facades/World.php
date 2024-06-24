<?php

namespace Altwaireb\World\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Altwaireb\World\World
 */
class World extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Altwaireb\World\World::class;
    }
}
