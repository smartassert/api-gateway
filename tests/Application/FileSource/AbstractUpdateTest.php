<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use AssertBadRequestTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $token,
            (string) new Ulid(),
            md5((string) rand())
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testUpdateNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $apiKey->key,
            (string) new Ulid(),
            md5((string) rand())
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testUpdateDeletedSource(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $getResponse = $this->applicationClient->makeGetSourceRequest($apiKey->key, $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeDeleteSourceRequest($apiKey->key, $id);
        self::assertSame(200, $deleteResponse->getStatusCode());

        $newLabel = md5((string) rand());
        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, $newLabel);
        self::assertSame(405, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame(
            [
                'class' => 'modify_read_only',
                'entity' => [
                    'id' => $id,
                    'type' => 'file-source',
                ],
            ],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    public function testUpdateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, null);

        $this->assertBadRequestFoo(
            $response,
            'empty',
            [
                'name' => 'label',
                'value' => '',
                'requirements' => [
                    'data_type' => 'string',
                    'size' => ['minimum' => 1, 'maximum' => 255],
                ],
            ]
        );
    }

    public function testUpdateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $newLabel = md5((string) rand());

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, $newLabel);
        $this->assertRetrievedFileSource($response, $newLabel, $user->id, $id);
    }
}
