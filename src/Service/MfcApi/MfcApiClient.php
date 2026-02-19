<?php

namespace App\Service\MfcApi;

use App\DTO\ApplicantStatus;
use App\DTO\ApplicantStatusEnum;
use App\DTO\RegisterRequestResult;
use App\Entity\MfcRequest;
use App\Exception\MfcApiException;
use App\Service\MfcFileStorage;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

class MfcApiClient implements MfcApiClientInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly MfcFileStorage $fileStorage,
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $username,
        private readonly string $password,
    ) {}

    /**
     * @return ApplicantStatus[]
     *
     * @throws MfcApiException
     */
    public function getApplicantStatuses(string $email): array
    {
        $query = http_build_query([
            'Email' => $email,
        ]);

        $url = rtrim($this->baseUrl, '/') . '/getApplicantStatuses?' . $query;

        $response = $this->httpClient->sendRequest(
            new Request('POST', $url, $this->buildHeaders()),
        );

        if ($response->getStatusCode() !== 200) {
            throw MfcApiException::fromResponse(
                $response->getStatusCode(),
                (string) $response->getBody(),
            );
        }

        /** @var array<int, array{Document: string, Active: bool, Status: string, UserCode: string}> $data */
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return array_map(ApplicantStatus::fromArray(...), $data);
    }

    /**
     * @throws MfcApiException
     */
    public function registerRequestByApplicant(MfcRequest $mfcRequest): RegisterRequestResult
    {
        $query = http_build_query([
            'DocumentId' => $mfcRequest->getDocumentNumber(),
            'RequestType' => $mfcRequest->getApplicationType()->getSlug(),
            'Role' => match ($mfcRequest->getApplicationType()->getRoles()[0]) {
                'ROLE_STUDENT' => ApplicantStatusEnum::Student->value,
                'ROLE_EMPLOYEE' => ApplicantStatusEnum::Employee->value,
            },
            'UserCode' => $mfcRequest->getOwner()->getMfcCode(),
        ]);

        $url = rtrim($this->baseUrl, '/') . '/registerRequestByApplicant?' . $query;

        $body = null;
        $headers = $this->buildHeaders();

        if (!$mfcRequest->getFiles()->isEmpty()) {
            $multipartElements = [];
            foreach ($mfcRequest->getFiles() as $file) {
                $fileInfo = $this->fileStorage->getSplFileInfo($file);
                if ($fileInfo === null) {
                    continue;
                }

                $multipartElements[] = [
                    'name' => 'files',
                    'contents' => fopen($fileInfo->getPathname(), 'r'),
                    'filename' => $file->getOriginalName(),
                    'headers' => ['Content-Type' => $file->getMimeType() ?? 'application/octet-stream'],
                ];
            }

            $body = new MultipartStream($multipartElements);
            $headers['Content-Type'] = 'multipart/form-data; boundary=' . $body->getBoundary();
        }

        $response = $this->httpClient->sendRequest(
            new Request('POST', $url, $headers, $body),
        );

        if ($response->getStatusCode() !== 200) {
            throw MfcApiException::fromResponse(
                $response->getStatusCode(),
                (string) $response->getBody(),
            );
        }

        $rawBody = (string) $response->getBody();

        if ($rawBody === '') {
            throw MfcApiException::fromResponse($response->getStatusCode(), 'Empty response body');
        }

        /** @var string[] $data e.g. ["000015"] */
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

        return RegisterRequestResult::fromArray($data);
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(): array
    {
        return [
            'X-API-KEY' => $this->apiKey,
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
            'Accept' => 'application/json',
        ];
    }
}
