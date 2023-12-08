<?php

declare(strict_types=1);

namespace App\Tests\Application\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\FileSource\AssertFileSourceTrait;
use App\Tests\Application\GitSource\AssertGitSourceTrait;
use App\Tests\Application\GitSource\CreateGitSourceDataProviderTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
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

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeDeleteSourceRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeDeleteSourceRequest($apiKey->key, (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    public function testDeleteFileSourceSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $getResponse = $this->applicationClient->makeGetSourceRequest($apiKey->key, $id);
        $this->assertRetrievedFileSource($getResponse, $label, $user->id, $id);

        $deleteResponse = $this->applicationClient->makeDeleteSourceRequest($apiKey->key, $id);
        $this->assertDeletedFileSource($deleteResponse, $label, $user->id, $id);
    }

    /**
     * @dataProvider createGitSourceDataProvider
     */
    public function testDeleteGitSourceSuccess(string $label, string $hostUrl, string $path, ?string $credentials): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $id = $this->createGitSource(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $getResponse = $this->applicationClient->makeGetSourceRequest($apiKey->key, $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeDeleteSourceRequest($apiKey->key, $id);
        $this->assertDeletedGitSource(
            $deleteResponse,
            $label,
            $id,
            $hostUrl,
            $path,
            is_string($credentials),
            $user->id
        );
    }
}
