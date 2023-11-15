<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

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
     * @dataProvider createInvalidRequestDataProvider
     */
    public function testCreateBadRequest(
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        string $expectedInvalidField
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $credentials = null;

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        self::assertArrayHasKey('type', $responseData);
        self::assertSame('bad-request', $responseData['type']);

        self::assertArrayHasKey('context', $responseData);
        $contextData = $responseData['context'];
        self::assertIsArray($contextData);

        self::assertSame('sources', $contextData['service']);
        self::assertArrayHasKey('invalid-field', $contextData);

        $invalidFieldData = $contextData['invalid-field'];
        self::assertIsArray($invalidFieldData);
        self::assertArrayHasKey('name', $invalidFieldData);
        self::assertSame($expectedInvalidField, $invalidFieldData['name']);

        self::assertArrayHasKey('value', $invalidFieldData);
        self::assertSame('', $invalidFieldData['value']);

        self::assertArrayHasKey('message', $invalidFieldData);
        self::assertNotSame('', $invalidFieldData['message']);
    }

    /**
     * @return array<mixed>
     */
    public function createInvalidRequestDataProvider(): array
    {
        return [
            'label missing' => [
                'label' => null,
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'expectedInvalidField' => 'label',
            ],
            'host url missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => null,
                'path' => md5((string) rand()),
                'expectedInvalidField' => 'host-url',
            ],
            'path missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => null,
                'expectedInvalidField' => 'path',
            ],
        ];
    }

    /**
     * @dataProvider createGitSourceDataProvider
     */
    public function testCreateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertRetrievedGitSource(
            $response,
            $label,
            null,
            $hostUrl,
            $path,
            is_string($credentials)
        );
    }
}
