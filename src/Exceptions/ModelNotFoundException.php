<?php

namespace RedSky\Exceptions;

use RedSky\Exceptions\HttpException;

class ModelNotFoundException extends HttpException
{
    public function __construct(string $model)
    {
        parent::__construct(
            "{$model} not found",
            404
        );
    }
}
