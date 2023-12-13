<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\Response\ErrorResponse;
use App\Response\TransparentResponse;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class ServiceProxy
{
    public function __construct(
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @param non-empty-string     $successContentType
     * @param non-empty-string     $errorContentType
     * @param array<int<100, 599>> $bareResponseStatusCodes
     *
     * @throws ServiceException
     */
    public function sendRequest(
        Service $service,
        RequestInterface $request,
        string $successContentType = 'application/json',
        string $errorContentType = 'application/json',
        array $bareResponseStatusCodes = [401, 404],
    ): Response {
        $request = $request->withUri(new Uri($service->getBaseUrl() . $request->getUri()));

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ServiceException($service->getName(), $e);
        }

        $statusCode = $response->getStatusCode();
        if (in_array($statusCode, $bareResponseStatusCodes)) {
            return new EmptyResponse($statusCode);
        }

        $contentType = $response->getHeaderLine('content-type');
        if ('' === $contentType) {
            return new EmptyResponse($statusCode);
        }

        if (
            (200 === $statusCode && str_starts_with($contentType, $successContentType))
            || ($contentType === $errorContentType)
        ) {
            return new TransparentResponse($response);
        }

        return new ErrorResponse(
            'service-communication-failure',
            500,
            [
                'service' => $service->getName(),
                'code' => $statusCode,
                'reason' => $response->getReasonPhrase(),
                'expected_content_type' => $successContentType,
                'actual_content_type' => $contentType,
            ]
        );
    }
}
