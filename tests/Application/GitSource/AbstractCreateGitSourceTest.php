<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;

abstract class AbstractCreateGitSourceTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateGitSourceRequest(
            'token',
            'label',
            'hostUrl',
            'path',
            'credentials',
            $method,
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $token,
            'label',
            'hostUrl',
            'path',
            'credentials'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'empty token' => [
                'token' => '',
            ],
            'non-empty invalid token' => [
                'token' => md5((string) rand()),
            ],
        ];
    }

    /**
     * @dataProvider createSuccessDataProvider
     *
     * @param callable(string, string, string, string, ?string): array<mixed> $expectedResponseObjectCreator
     */
    public function testCreateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        callable $expectedResponseObjectCreator,
    ): void {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiToken,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertArrayHasKey('git_source', $responseData);

        $objectData = $responseData['git_source'];
        self::assertIsArray($objectData);

        self::assertSame(
            $expectedResponseObjectCreator($objectData['id'], $label, $hostUrl, $path, $credentials),
            $objectData
        );
    }

    /**
     * @return array<mixed>
     */
    public function createSuccessDataProvider(): array
    {
        $expectedResponseObjectCreator = function (
            string $id,
            string $label,
            string $hostUrl,
            string $path,
            ?string $credentials
        ) {
            return [
                'id' => $id,
                'label' => $label,
                'type' => 'git',
                'host_url' => $hostUrl,
                'path' => $path,
                'has_credentials' => is_string($credentials),
            ];
        };

        return [
            'without credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expectedResponseObjectCreator' => $expectedResponseObjectCreator,
            ],
            'with credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => md5((string) rand()),
                'expectedResponseObjectCreator' => $expectedResponseObjectCreator,
            ],
        ];
    }
}
