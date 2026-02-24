<?php

namespace Tygygydyk\ComposerDependencyScanner\Exception;

class HttpException extends ComposerDependencyScannerException
{
    /**
     * @param int $statusCode HTTP status (0 when no response received, e.g. network failure)
     */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /** 0 when request failed before receiving response (connection, timeout, etc.) */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
