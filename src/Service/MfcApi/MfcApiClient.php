<?php

namespace App\Service\MfcApi;

use App\DTO\ApplicantStatus;
use App\Exception\MfcApiException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

class MfcApiClient implements MfcApiClientInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
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
        $url = rtrim($this->baseUrl, '/') . '/getApplicantStatuses?' . http_build_query(['Email' => $email]);

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
