<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractAddFileTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testAddBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeAddFileSourceFileRequest(
            'token',
            (string) new Ulid(),
            'filename.yaml',
            'content',
            $method,
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
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
    public function testAddUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeAddFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml',
            'content',
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

    public function testAddSuccess(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');
        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiToken, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $response = $this->applicationClient->makeAddFileSourceFileRequest($apiToken, $sourceId, $filename, $content);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame('[]', $response->getBody()->getContents());
    }
}
