<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Psr\Http\Message\ResponseInterface;

trait CreateSourceTrait
{
    /**
     * @return non-empty-string
     */
    public function createFileSource(string $apiKey, ?string $label = null): string
    {
        $label = is_string($label) ? $label : md5((string) rand());

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey, $label);

        return $this->extractSourceId($response);
    }

    /**
     * @return non-empty-string
     */
    public function createGitSource(
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): string {
        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        return $this->extractSourceId($response);
    }

    /**
     * @return non-empty-string
     */
    private function extractSourceId(ResponseInterface $response): string
    {
        \assert(200 === $response->getStatusCode());

        $responseData = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($responseData));

        $id = $responseData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        return $id;
    }
}
