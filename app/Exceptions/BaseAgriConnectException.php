<?php

// app/Exceptions/BaseAgriConnectException.php
namespace App\Exceptions;

use Exception;

abstract class BaseAgriConnectException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorCode;
    protected array $context = [];

    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message);
        $this->context = $context;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'context' => $this->context,
            ],
        ], $this->statusCode);
    }
}
