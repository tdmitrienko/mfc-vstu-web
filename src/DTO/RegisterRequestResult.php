<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class RegisterRequestResult
{
    public function __construct(
        public string $requestId,
    ) {}

    /**
     * @param string[] $data e.g. ["000015"]
     */
    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data[0],
        );
    }
}
