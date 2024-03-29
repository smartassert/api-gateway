<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function makeUndefinedServiceRequest(string $url): ResponseInterface
    {
        return $this->client->makeRequest('GET', $url);
    }

    public function makeCreateUserTokenRequest(
        ?string $userIdentifier,
        ?string $password,
        string $method = 'POST'
    ): ResponseInterface {
        $url = '/user/frontend-token/create';
        $headers = ['Content-Type' => 'application/json'];

        $payload = [];
        if (is_string($userIdentifier)) {
            $payload['username'] = $userIdentifier;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        return $this->client->makeRequest($method, $url, $headers, (string) json_encode($payload));
    }

    public function makeVerifyUserTokenRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        $url = '/user/frontend-token/verify';

        return $this->client->makeRequest($method, $url, $headers);
    }

    public function makeRefreshUserTokenRequest(?string $refreshToken, string $method = 'POST'): ResponseInterface
    {
        $headers = ['Content-Type' => 'application/json'];

        $url = '/user/frontend-token/refresh';
        $body = (string) json_encode(['refresh_token' => $refreshToken]);

        return $this->client->makeRequest($method, $url, $headers, $body);
    }

    public function makeListUserApiKeysRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        $url = '/user/apikey/list';

        return $this->client->makeRequest($method, $url, $headers);
    }

    public function makeGetUserDefaultApiKeyRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        $url = '/user/apikey';

        return $this->client->makeRequest($method, $url, $headers);
    }

    public function makeCreateUserRequest(
        ?string $adminToken,
        ?string $identifier,
        ?string $password,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_string($adminToken)) {
            $headers['Authorization'] = $adminToken;
        }

        $payload = [];

        if (is_string($identifier)) {
            $payload['identifier'] = $identifier;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        $url = '/user/create';

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    public function makeRevokeAllRefreshTokensForUserRequest(
        ?string $adminToken,
        ?string $userId,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_string($adminToken)) {
            $headers['Authorization'] = $adminToken;
        }

        $payload = [];

        if (is_string($userId)) {
            $payload['id'] = $userId;
        }

        $url = '/user/refresh-token/revoke-all-for-user';

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    public function makeRevokeRefreshTokenRequest(
        ?string $jwt,
        ?string $refreshToken,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_string($jwt)) {
            $headers['Authorization'] = 'Bearer ' . $jwt;
        }

        $payload = [];

        if (is_string($refreshToken)) {
            $payload['refresh_token'] = $refreshToken;
        }

        $url = '/user/refresh-token/revoke';

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    public function makeCreateFileSourceRequest(
        ?string $apiKey,
        ?string $label,
        string $method = 'POST'
    ): ResponseInterface {
        return $this->makeFileSourceMutationRequest($apiKey, $method, null, $label);
    }

    public function makeUpdateFileSourceRequest(
        ?string $apiKey,
        string $sourceId,
        ?string $label,
        string $method = 'PUT'
    ): ResponseInterface {
        return $this->makeFileSourceMutationRequest($apiKey, $method, $sourceId, $label);
    }

    public function makeCreateGitSourceRequest(
        ?string $apiKey,
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        ?string $credentials
    ): ResponseInterface {
        return $this->makeGitSourceMutationRequest(
            $apiKey,
            'POST',
            null,
            $label,
            $hostUrl,
            $path,
            $credentials
        );
    }

    public function makeUpdateGitSourceRequest(
        ?string $apiKey,
        ?string $sourceId,
        ?string $label = null,
        ?string $hostUrl = null,
        ?string $path = null,
        ?string $credentials = null
    ): ResponseInterface {
        return $this->makeGitSourceMutationRequest(
            $apiKey,
            'PUT',
            $sourceId,
            $label,
            $hostUrl,
            $path,
            $credentials
        );
    }

    public function makeCreateFileSourceFileRequest(
        ?string $apiKey,
        string $fileSourceId,
        string $filename,
        ?string $content = null,
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'POST', $fileSourceId, $filename, $content);
    }

    public function makeReadFileSourceFileRequest(
        ?string $apiKey,
        string $fileSourceId,
        string $filename
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'GET', $fileSourceId, $filename);
    }

    public function makeUpdateFileSourceFileRequest(
        ?string $apiKey,
        string $fileSourceId,
        string $filename,
        ?string $content = null,
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'PUT', $fileSourceId, $filename, $content);
    }

    public function makeDeleteFileSourceFileRequest(
        ?string $apiKey,
        string $fileSourceId,
        string $filename,
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'DELETE', $fileSourceId, $filename);
    }

    public function makeFileSourceFilesRequest(
        ?string $apiKey,
        string $sourceId,
        string $method = 'GET',
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = sprintf('/source/file-source/%s/list/', $sourceId);

        return $this->client->makeRequest(
            $method,
            $url,
            $headers
        );
    }

    public function makeListSourcesRequest(
        ?string $apiKey,
        string $method = 'GET',
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = '/source/sources';

        return $this->client->makeRequest($method, $url, $headers);
    }

    /**
     * @param string[] $tests
     */
    public function makeCreateSuiteRequest(
        ?string $apiKey,
        ?string $sourceId,
        ?string $label,
        ?array $tests,
    ): ResponseInterface {
        return $this->makeSuiteRequest($apiKey, 'POST', null, $sourceId, $label, $tests);
    }

    public function makeGetSuiteRequest(?string $apiKey, string $suiteId): ResponseInterface
    {
        return $this->makeSuiteRequest($apiKey, 'GET', $suiteId);
    }

    /**
     * @param string[] $tests
     */
    public function makeUpdateSuiteRequest(
        ?string $apiKey,
        ?string $suiteId,
        ?string $sourceId,
        ?string $label,
        ?array $tests,
    ): ResponseInterface {
        return $this->makeSuiteRequest($apiKey, 'PUT', $suiteId, $sourceId, $label, $tests);
    }

    public function makeListSuitesRequest(?string $apiKey): ResponseInterface
    {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = '/source/suites';

        return $this->client->makeRequest('GET', $url, $headers);
    }

    public function makeDeleteSuiteRequest(?string $apiKey, string $suiteId): ResponseInterface
    {
        return $this->makeSuiteRequest($apiKey, 'DELETE', $suiteId);
    }

    public function makeSourceActRequest(string $method, ?string $apiKey, string $sourceId): ResponseInterface
    {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = sprintf('/source/%s', $sourceId);

        return $this->client->makeRequest($method, $url, $headers);
    }

    public function makeCreateJobCoordinatorJobRequest(
        ?string $apiKey,
        ?string $suiteId,
        ?int $maximumDurationInSeconds,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $payload = [];
        if (is_int($maximumDurationInSeconds)) {
            $payload['maximum_duration_in_seconds'] = $maximumDurationInSeconds;
        }

        if (('POST' === $method || 'PUT' === $method) && [] !== $payload) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $url = '/job-coordinator';
        if (is_string($suiteId)) {
            $url .= '/' . $suiteId;
        }

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    public function makeGetJobCoordinatorJobRequest(
        ?string $apiKey,
        ?string $jobId,
        string $method = 'GET'
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = '/job-coordinator';
        if (is_string($jobId)) {
            $url .= '/' . $jobId;
        }

        return $this->client->makeRequest($method, $url, $headers);
    }

    public function makeListJobCoordinatorJobsRequest(
        ?string $apiKey,
        string $suiteId,
        string $method = 'GET'
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $url = '/job-coordinator/' . $suiteId . '/list';

        return $this->client->makeRequest($method, $url, $headers);
    }

    private function makeFileSourceFileRequest(
        ?string $apiKey,
        string $method,
        string $fileSourceId,
        string $filename,
        ?string $content = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        if ('POST' === $method || 'PUT' === $method) {
            $headers['content-type'] = 'application/yaml';
        }

        if ('GET' === $method) {
            $headers['accept'] = 'application/yaml, text/x-yaml';
        }

        $url = sprintf('/source/file-source/%s/%s', $fileSourceId, $filename);

        return $this->client->makeRequest($method, $url, $headers, $content);
    }

    /**
     * @param 'POST'|'PUT' $method
     */
    private function makeGitSourceMutationRequest(
        ?string $apiKey,
        string $method,
        ?string $sourceId = null,
        ?string $label = null,
        ?string $hostUrl = null,
        ?string $path = null,
        ?string $credentials = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $payload = [];
        if (is_string($label)) {
            $payload['label'] = $label;
        }

        if (is_string($hostUrl)) {
            $payload['host-url'] = $hostUrl;
        }

        if (is_string($path)) {
            $payload['path'] = $path;
        }

        if (is_string($credentials)) {
            $payload['credentials'] = $credentials;
        }

        if ([] !== $payload) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $url = '/source/git-source';
        if (is_string($sourceId)) {
            $url .= '/' . $sourceId;
        }

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    private function makeFileSourceMutationRequest(
        ?string $apiKey,
        string $method,
        ?string $sourceId,
        ?string $label = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $payload = [];
        if (is_string($label)) {
            $payload['label'] = $label;
        }

        if ([] !== $payload) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $url = '/source/file-source';
        if (is_string($sourceId)) {
            $url .= '/' . $sourceId;
        }

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }

    /**
     * @param 'DELETE'|'GET'|'POST'|'PUT' $method
     * @param string[]                    $tests
     */
    private function makeSuiteRequest(
        ?string $apiKey,
        string $method,
        ?string $suiteId,
        ?string $sourceId = null,
        ?string $label = null,
        ?array $tests = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
            $headers['Translate-Authorization-To'] = 'api-token';
        }

        $payload = [];
        if (is_string($sourceId)) {
            $payload['source_id'] = $sourceId;
        }

        if (is_string($label)) {
            $payload['label'] = $label;
        }

        if (is_array($tests)) {
            $payload['tests'] = $tests;
        }

        if (('POST' === $method || 'PUT' === $method) && [] !== $payload) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $url = '/source/suite';
        if (is_string($suiteId)) {
            $url .= '/' . $suiteId;
        }

        return $this->client->makeRequest($method, $url, $headers, http_build_query($payload));
    }
}
