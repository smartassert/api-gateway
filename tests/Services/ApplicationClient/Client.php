<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class Client
{
    public function __construct(
        private ClientInterface $client,
        private RouterInterface $router,
    ) {
    }

    public function makeCreateUserTokenRequest(
        ?string $userIdentifier,
        ?string $password,
        string $method = 'POST'
    ): ResponseInterface {
        $payload = [];

        if (is_string($userIdentifier)) {
            $payload['user-identifier'] = $userIdentifier;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_token_create'),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($payload)
        );
    }

    public function makeVerifyUserTokenRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_token_verify'),
            $headers
        );
    }

    public function makeRefreshUserTokenRequest(?string $refreshToken, string $method = 'POST'): ResponseInterface
    {
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        if (is_string($refreshToken)) {
            $headers['Authorization'] = 'Bearer ' . $refreshToken;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_token_refresh'),
            $headers
        );
    }

    public function makeListUserApiKeysRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_apikey_list'),
            $headers
        );
    }

    public function makeGetUserDefaultApiKeyRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_apikey_get_default'),
            $headers
        );
    }

    public function makeCreateUserRequest(
        ?string $adminToken,
        ?string $userIdentifier,
        ?string $password,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_string($adminToken)) {
            $headers['Authorization'] = 'Bearer ' . $adminToken;
        }

        $payload = [];

        if (is_string($userIdentifier)) {
            $payload['user-identifier'] = $userIdentifier;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_create'),
            $headers,
            http_build_query($payload)
        );
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
            $headers['Authorization'] = 'Bearer ' . $adminToken;
        }

        $payload = [];

        if (is_string($userId)) {
            $payload['id'] = $userId;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_revoke_all_refresh_token'),
            $headers,
            http_build_query($payload)
        );
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

        return $this->client->makeRequest(
            $method,
            $this->router->generate('user_revoke_refresh_token'),
            $headers,
            http_build_query($payload)
        );
    }

    public function makeCreateFileSourceRequest(?string $apiKey, ?string $label): ResponseInterface
    {
        return $this->makeFileSourceMutationRequest($apiKey, 'POST', null, $label);
    }

    public function makeUpdateFileSourceRequest(?string $apiKey, string $sourceId, ?string $label): ResponseInterface
    {
        return $this->makeFileSourceMutationRequest($apiKey, 'PUT', $sourceId, $label);
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
        ?string $fileSourceId,
        ?string $filename,
        ?string $content = null,
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'POST', $fileSourceId, $filename, $content);
    }

    public function makeReadFileSourceFileRequest(
        ?string $apiKey,
        ?string $fileSourceId,
        ?string $filename
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'GET', $fileSourceId, $filename);
    }

    public function makeUpdateFileSourceFileRequest(
        ?string $apiKey,
        ?string $fileSourceId,
        ?string $filename,
        ?string $content = null,
    ): ResponseInterface {
        return $this->makeFileSourceFileRequest($apiKey, 'PUT', $fileSourceId, $filename, $content);
    }

    public function makeDeleteFileSourceFileRequest(
        ?string $apiKey,
        ?string $fileSourceId,
        ?string $filename,
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
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('file_source_list', ['sourceId' => $sourceId]),
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
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('sources_list'),
            $headers
        );
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
        }

        return $this->client->makeRequest(
            'GET',
            $this->router->generate('suite_list'),
            $headers
        );
    }

    public function makeDeleteSuiteRequest(?string $apiKey, string $suiteId): ResponseInterface
    {
        return $this->makeSuiteRequest($apiKey, 'DELETE', $suiteId);
    }

    /**
     * @param 'DELETE'|'GET' $method
     */
    public function makeSourceActRequest(string $method, ?string $apiKey, string $sourceId): ResponseInterface
    {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('source_act', ['sourceId' => $sourceId]),
            $headers
        );
    }

    private function makeFileSourceFileRequest(
        ?string $apiKey,
        string $method,
        ?string $fileSourceId,
        ?string $filename,
        ?string $content = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $route = 'file_source_file_read';
        if ('POST' === $method) {
            $route = 'file_source_file_create';
            $headers['content-type'] = 'text/x-yaml';
        }
        if ('PUT' === $method) {
            $route = 'file_source_file_update';
            $headers['content-type'] = 'text/x-yaml';
        }
        if ('DELETE' === $method) {
            $route = 'file_source_file_delete';
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate(
                $route,
                [
                    'sourceId' => $fileSourceId,
                    'filename' => $filename,
                ]
            ),
            $headers,
            $content
        );
    }

    /**
     * @param 'POST'|'PUT' $method
     */
    private function makeGitSourceMutationRequest(
        ?string $apiKey,
        string $method,
        string $sourceId = null,
        ?string $label = null,
        ?string $hostUrl = null,
        ?string $path = null,
        ?string $credentials = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
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

        $route = 'git_source_' . ('POST' === $method ? 'create' : 'update');

        return $this->client->makeRequest(
            $method,
            $this->router->generate($route, ['sourceId' => $sourceId]),
            $headers,
            http_build_query($payload)
        );
    }

    /**
     * @param 'POST'|'PUT' $method
     */
    private function makeFileSourceMutationRequest(
        ?string $apiKey,
        string $method,
        ?string $sourceId,
        ?string $label = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $payload = [];
        if (is_string($label)) {
            $payload['label'] = $label;
        }

        if ([] !== $payload) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('file_source_act', ['sourceId' => $sourceId]),
            $headers,
            http_build_query($payload)
        );
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
        }

        $payload = [];
        if (is_string($sourceId)) {
            $payload['sourceId'] = $sourceId;
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

        $route = 'suite_read';
        if ('POST' === $method) {
            $route = 'suite_create';
        }
        if ('PUT' === $method) {
            $route = 'suite_update';
        }
        if ('DELETE' === $method) {
            $route = 'suite_delete';
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate($route, ['suiteId' => $suiteId]),
            $headers,
            http_build_query($payload)
        );
    }
}
