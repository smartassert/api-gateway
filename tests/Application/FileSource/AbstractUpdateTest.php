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

    public function testUpdateBadMethod(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $apiKey['key'],
            (string) new Ulid(),
            md5((string) rand()),
            'GET'
        );

        self::assertSame(405, $response->getStatusCode());
    }

    public function testUpdateNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $apiKey['key'],
            (string) new Ulid(),
            md5((string) rand())
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function testUpdateDeletedSource(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $getResponse = $this->applicationClient->makeSourceActRequest('GET', $apiKey['key'], $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeSourceActRequest('DELETE', $apiKey['key'], $id);
        self::assertSame(200, $deleteResponse->getStatusCode());

        $newLabel = md5((string) rand());
        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey['key'], $id, $newLabel);
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
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey['key'], $id, null);

        $this->assertBadRequest(
            $response,
            'wrong_size',
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

    public function testUpdateDuplicateLabel(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $firstSourceLabel = md5((string) rand());
        $this->createFileSource($apiKey['key'], $firstSourceLabel);

        $secondSourceLabel = md5((string) rand());
        $secondSourceId = $this->createFileSource($apiKey['key'], $secondSourceLabel);

        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $apiKey['key'],
            $secondSourceId,
            $firstSourceLabel
        );

        $this->assertDuplicateObjectResponse($response, 'label', $firstSourceLabel);
    }

    public function testUpdateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $newLabel = md5((string) rand());

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey['key'], $id, $newLabel);
        $this->assertRetrievedFileSource($response, $newLabel, $user['id'], $id);
    }
}
