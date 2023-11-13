<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use AssertFileSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeFileSourceRequest(
            $token,
            (string) new Ulid(),
            'PUT',
            md5((string) rand())
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

    public function testUpdateNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceRequest(
            $apiToken,
            (string) new Ulid(),
            'PUT',
            md5((string) rand())
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');
        $label = md5((string) rand());

        $createResponse = $this->applicationClient->makeCreateFileSourceRequest($apiToken, $label);
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $createdSourceData = $createResponseData['file_source'];
        \assert(is_array($createdSourceData));

        $id = $createdSourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        $newLabel = md5((string) rand());

        $response = $this->applicationClient->makeFileSourceRequest($apiToken, $id, 'PUT', $newLabel);

        $this->assertRetrievedFileSource($response, $newLabel, $id);
    }
}
