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

#[Route(path: '/file-source', name: 'file_source_')]
readonly class FileSourceController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>?}', name: 'act', methods: ['POST', 'PUT'])]
    public function act(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}/list/', name: 'list', methods: ['GET'])]
    public function list(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }
}
