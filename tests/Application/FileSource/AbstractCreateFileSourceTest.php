<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;

abstract class AbstractCreateFileSourceTest extends AbstractApplicationTestCase
{
    use AssertFileSourceTrait;

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateFileSourceRequest(
            'token',
            'label',
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
        $response = $this->applicationClient->makeCreateFileSourceRequest($token, md5((string) rand()));

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

    public function testCreateSuccess(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');
        $label = md5((string) rand());

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiToken, $label);

        $this->assertRetrievedFileSource($response, $label);
    }
}
