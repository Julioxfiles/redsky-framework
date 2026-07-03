<?php

namespace RedSky\Framework\Exceptions;

use Exception;

class HttpException extends Exception
{
    protected int $status;
    protected array $errors = [];

    public function __construct(
        string $message,
        int $status = 400,
        array $errors = []
    ) {
        parent::__construct($message);
        $this->status = $status;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
