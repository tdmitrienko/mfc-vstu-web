<?php

declare(strict_types=1);

namespace App\Service\MfcApi;

use App\DTO\ApplicantStatus;
use App\Exception\MfcApiException;

interface MfcApiClientInterface
{
    /**
     * @return ApplicantStatus[]
     *
     * @throws MfcApiException
     */
    public function getApplicantStatuses(string $email): array;
}
