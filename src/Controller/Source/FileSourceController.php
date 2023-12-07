<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Exception\UnexpectedServiceResponseException;
use App\Response\EmptyResponse;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceProxy;
use GuzzleHttp\Psr7\Request as HttpRequest;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source', name: 'file_source_')]
readonly class FileSourceController
{
    public function __construct(
        private FileSourceClientInterface $client,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnexpectedServiceResponseException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, Request $request): Response
    {
        $httpRequest = new HttpRequest(
            $request->getMethod(),
            '/file-source',
            [
                'authorization' => 'Bearer ' . $token->token,
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['label' => $request->request->get('label')])
        );

        try {
            $response = $this->sourcesProxy->sendRequest($httpRequest);

            $statusCode = $response->getStatusCode();
            $responseContentType = $response->getHeaderLine('content-type');

            if (200 === $statusCode) {
                if (str_starts_with($responseContentType, 'application/json')) {
                    return new Response(
                        $response->getBody()->getContents(),
                        $response->getStatusCode(),
                        ['content-type' => $response->getHeaderLine('content-type')]
                    );
                }

                throw new UnexpectedServiceResponseException(
                    'sources',
                    'application/json',
                    $response
                );
            }

            if (str_starts_with($responseContentType, 'application/json')) {
                return new Response(
                    $response->getBody()->getContents(),
                    $response->getStatusCode(),
                    ['content-type' => $response->getHeaderLine('content-type')]
                );
            }

            throw new UnexpectedServiceResponseException(
                'sources',
                'application/json',
                $response
            );
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, string $sourceId, Request $request): Response
    {
        try {
            $source = $this->client->update($token->token, $sourceId, $request->request->getString('label'));

            return new JsonResponse($source->toArray());
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            ModifyReadOnlyEntityException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'delete', methods: ['DELETE'])]
    public function delete(ApiToken $token, string $sourceId, Request $request): Response
    {
        $httpRequest = new HttpRequest(
            'DELETE',
            '/source/' . $sourceId,
            [
                'authorization' => 'Bearer ' . $token->token,
            ]
        );

        try {
            $response = $this->sourcesProxy->sendRequest($httpRequest);

            $statusCode = $response->getStatusCode();
            $responseContentType = $response->getHeaderLine('content-type');

            if (404 === $statusCode) {
                return new EmptyResponse(404);
            }

            if (200 === $statusCode) {
                if (str_starts_with($responseContentType, 'application/json')) {
                    return new Response(
                        $response->getBody()->getContents(),
                        $response->getStatusCode(),
                        ['content-type' => $response->getHeaderLine('content-type')]
                    );
                }

                throw new UnexpectedServiceResponseException(
                    'sources',
                    'application/json',
                    $response
                );
            }

            if (str_starts_with($responseContentType, 'application/json')) {
                return new Response(
                    $response->getBody()->getContents(),
                    $response->getStatusCode(),
                    ['content-type' => $response->getHeaderLine('content-type')]
                );
            }

            throw new UnexpectedServiceResponseException(
                'sources',
                'application/json',
                $response
            );
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}/list', name: 'list', methods: ['GET'])]
    public function list(ApiToken $token, string $sourceId): JsonResponse
    {
        try {
            $filenames = $this->client->list($token->token, $sourceId);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new JsonResponse($filenames);
    }
}
