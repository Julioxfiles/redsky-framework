<?php

declare(strict_types=1);

namespace RedSky\Support;

use RedSky\Foundation\Application;

abstract class ServiceProvider
{
    public function __construct(
        protected Application $app
    ) {
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}