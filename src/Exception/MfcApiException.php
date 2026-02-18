<?php

namespace App\Exception;

final class MfcApiException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function fromResponse(int $statusCode, string $body): self
    {
        return new self($statusCode, trim($body) ?: "External API error (HTTP $statusCode)");
    }
}
