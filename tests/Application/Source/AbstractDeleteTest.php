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

abstract class AbstractDeleteTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use AssertGitSourceTrait;
    use CreateSourceTrait;
    use CreateGitSourceDataProviderTrait;

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeSourceActRequest('DELETE', $token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeSourceActRequest('DELETE', $apiKey['key'], (string) new Ulid());

        self::assertSame(403, $response->getStatusCode());
    }

    public function testDeleteFileSourceSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $getResponse = $this->applicationClient->makeSourceActRequest('GET', $apiKey['key'], $id);
        $this->assertRetrievedFileSource($getResponse, $label, $user['id'], $id);

        $deleteResponse = $this->applicationClient->makeSourceActRequest('DELETE', $apiKey['key'], $id);
        $this->assertDeletedFileSource($deleteResponse, $label, $user['id'], $id);
    }

    #[DataProvider('createGitSourceDataProvider')]
    public function testDeleteGitSourceSuccess(string $label, string $hostUrl, string $path, ?string $credentials): void
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

        $deleteResponse = $this->applicationClient->makeSourceActRequest('DELETE', $apiKey['key'], $id);
        $this->assertDeletedGitSource(
            $deleteResponse,
            $label,
            $id,
            $hostUrl,
            $path,
            is_string($credentials),
            $user['id']
        );
    }
}
