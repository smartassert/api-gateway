<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use App\Exception\BareHttpException;
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
     * @throws BareHttpException
     */
    public function sendRequest(
        RequestInterface $request,
        ?ResponseHandlingSpecification $responseHandlingSpecification = null,
    ): ResponseInterface {
        $request = $request->withUri(new Uri($this->baseUrl . $request->getUri()));
        $response = $this->httpClient->sendRequest($request);

        if ($responseHandlingSpecification instanceof ResponseHandlingSpecification) {
            if (in_array($response->getStatusCode(), $responseHandlingSpecification->getBareResponseStatusCodes())) {
                throw new BareHttpException($response->getStatusCode());
            }
        }

        return $response;
    }
}
