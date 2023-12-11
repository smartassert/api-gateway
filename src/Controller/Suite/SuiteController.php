<?php

declare(strict_types=1);

namespace App\Controller\Suite;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\SerializableInterface;
use SmartAssert\SourcesClient\SuiteClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/suite', name: 'suite_')]
readonly class SuiteController
{
    public function __construct(
        private SuiteClientInterface $client,
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, Request $request): Response
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
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'read', methods: ['GET'])]
    public function get(ApiToken $token, Request $request): Response
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

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, Request $request): Response
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
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'delete', methods: ['DELETE'])]
    public function delete(ApiToken $token, Request $request): Response
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

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: 's', name: 'list', methods: ['GET'])]
    public function list(ApiToken $token): Response
    {
        try {
            $suites = $this->client->list($token->token);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        $serializedSuites = [];
        foreach ($suites as $suite) {
            if ($suite instanceof SerializableInterface) {
                $serializedSuites[] = $suite->toArray();
            }
        }

        return new JsonResponse($serializedSuites);
    }
}
