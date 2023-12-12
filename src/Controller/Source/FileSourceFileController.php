<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileSourceFileController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'handle', methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    public function handle(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $requestBuilder = $requestBuilder->withBearerAuthorization($token->token);

        if ('POST' === $request->getMethod() || 'PUT' === $request->getMethod()) {
            $requestBuilder = $requestBuilder->withBody(
                (string) $request->getContent(),
                (string) $request->headers->get('content-type')
            );
        }

        $httpRequest = $requestBuilder->get();

        $bareResponseStatusCodes = [401, 404];
        $successContentType = 'application/json';

        if ('GET' === $request->getMethod()) {
            $successContentType = 'text/x-yaml';
        } else {
            $bareResponseStatusCodes[] = 200;
        }

        try {
            return $this->sourcesProxy->sendRequest(
                request: $httpRequest,
                successContentType: $successContentType,
                bareResponseStatusCodes: $bareResponseStatusCodes
            );
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }
}
