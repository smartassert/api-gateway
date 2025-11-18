<?php

declare(strict_types=1);

namespace App\Tests\Application\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\FileSource\AssertFileSourceTrait;
use App\Tests\Application\GitSource\AssertGitSourceTrait;
use App\Tests\Application\GitSource\CreateGitSourceDataProviderTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use CreateSourceTrait;
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testGetUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeSourceActRequest('GET', $token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetBadMethod(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeSourceActRequest('PUT', $apiKey['key'], (string) new Ulid());

        self::assertSame(405, $response->getStatusCode());
    }

    public function testGetNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeSourceActRequest('GET', $apiKey['key'], (string) new Ulid());

        self::assertSame(403, $response->getStatusCode());
    }

    public function testGetFileSourceSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $response = $this->applicationClient->makeSourceActRequest('GET', $apiKey['key'], $id);

        $this->assertRetrievedFileSource($response, $label, $user['id'], $id);
    }

    #[DataProvider('createGitSourceDataProvider')]
    public function testGetGitSourceSuccess(string $label, string $hostUrl, string $path, ?string $credentials): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $id = $this->createGitSource(
            $apiKey['key'],
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $getResponse = $this->applicationClient->makeSourceActRequest('GET', $apiKey['key'], $id);
        self::assertSame(200, $getResponse->getStatusCode());
        $this->assertRetrievedGitSource(
            $getResponse,
            $label,
            $id,
            $hostUrl,
            $path,
            is_string($credentials),
            $user['id'],
        );
    }
}
