<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

readonly class ServiceProxy
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $baseUrl,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $request->withUri(new Uri($this->baseUrl . $request->getUri()));

        return $this->httpClient->sendRequest($request);
    }
}
