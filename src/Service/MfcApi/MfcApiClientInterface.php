<?php

declare(strict_types=1);

namespace App\Service\MfcApi;

use App\DTO\ApplicantStatus;
use App\DTO\RegisterRequestResult;
use App\Entity\MfcRequest;
use App\Exception\MfcApiException;

interface MfcApiClientInterface
{
    /**
     * @return ApplicantStatus[]
     *
     * @throws MfcApiException
     */
    public function getApplicantStatuses(string $email): array;

    /**
     * @throws MfcApiException
     */
    public function registerRequestByApplicant(MfcRequest $mfcRequest): RegisterRequestResult;
}
