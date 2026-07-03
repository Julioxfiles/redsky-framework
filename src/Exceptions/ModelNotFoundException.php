<?php

namespace RedSky\Framework\Exceptions;

use RedSky\Framework\Exceptions\HttpException;

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
