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

    public function makeCreateFileSourceRequest(
        ?string $apiKey,
        ?string $label,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $payload = [];

        if (is_string($label)) {
            $payload['label'] = $label;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('file_source_create'),
            $headers,
            http_build_query($payload)
        );
    }

    public function makeCreateGitSourceRequest(
        ?string $apiKey,
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        ?string $credentials,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

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

        return $this->client->makeRequest(
            $method,
            $this->router->generate('git_source_create'),
            $headers,
            http_build_query($payload)
        );
    }

    public function makeFileSourceFileRequest(
        ?string $apiKey,
        ?string $fileSourceId,
        ?string $filename,
        string $method,
        ?string $content = null,
    ): ResponseInterface {
        $headers = [];
        if (is_string($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate(
                'file_source_file_handle',
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
     * @param 'DELETE'|'GET'|'PUT' $method
     */
    public function makeFileSourceRequest(
        ?string $apiKey,
        string $method,
        string $sourceId,
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
            $this->router->generate('file_source_handle', ['sourceId' => $sourceId]),
            $headers,
            http_build_query($payload)
        );
    }

    /**
     * @param 'DELETE'|'GET'|'PUT' $method
     */
    public function makeGitSourceRequest(
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

        return $this->client->makeRequest(
            $method,
            $this->router->generate('git_source_handle', ['sourceId' => $sourceId]),
            $headers,
            http_build_query($payload)
        );
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
        ?string $jwt,
        string $method = 'GET',
    ): ResponseInterface {
        $headers = [];
        if (is_string($jwt)) {
            $headers['Authorization'] = 'Bearer ' . $jwt;
        }

        return $this->client->makeRequest(
            $method,
            $this->router->generate('sources_list'),
            $headers
        );
    }
}
