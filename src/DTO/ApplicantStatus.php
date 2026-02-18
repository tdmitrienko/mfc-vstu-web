<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ApplicantStatus
{
    public function __construct(
        public ?string $document,
        public ApplicantStatusEnum $status,
        public string $userCode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            document: empty($data['Document']) ? null : $data['Document'],
            status: ApplicantStatusEnum::from(strtolower($data['Status'])),
            userCode: $data['UserCode'],
        );
    }
}
