<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ApplicantStatus;
use App\DTO\ApplicantStatusEnum;

class MfcApiClientStub implements MfcApiClientInterface
{
    public function getApplicantStatuses(string $email): array
    {
        return [
            new ApplicantStatus(
                document: 'STUB-DOC-' . rand(11111, 99999),
                status: ApplicantStatusEnum::Student,
                userCode: 'STUB-USER-001',
            ),
            new ApplicantStatus(
                document: 'STUB-DOC-' . rand(11111, 99999),
                status: ApplicantStatusEnum::Student,
                userCode: 'STUB-USER-001',
            ),
            new ApplicantStatus(
                document: null,
                status: ApplicantStatusEnum::Employee,
                userCode: 'STUB-USER-001',
            ),
        ];
    }
}
