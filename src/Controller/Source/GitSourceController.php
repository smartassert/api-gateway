<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\Exception\ModifyReadOnlyEntityException;
use SmartAssert\SourcesClient\GitSourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/git-source', name: 'git_source_')]
readonly class GitSourceController
{
    public function __construct(
        private GitSourceClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, Request $request): JsonResponse
    {
        try {
            $credentials = $request->request->getString('credentials');
            if ('' === $credentials) {
                $credentials = null;
            }

            $source = $this->client->create(
                $token->token,
                $request->request->getString('label'),
                $request->request->getString('host-url'),
                $request->request->getString('path'),
                $credentials,
            );

            return new JsonResponse($source->toArray());
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
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
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, string $sourceId, Request $request): Response
    {
        try {
            $credentials = $request->request->getString('credentials');
            if ('' === $credentials) {
                $credentials = null;
            }

            $source = $this->client->update(
                $token->token,
                $sourceId,
                $request->request->getString('label'),
                $request->request->getString('host-url'),
                $request->request->getString('path'),
                $credentials
            );

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
    public function delete(ApiToken $token, string $sourceId): Response
    {
        try {
            $source = $this->client->delete($token->token, $sourceId);

            return new JsonResponse($source->toArray());
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }
}
