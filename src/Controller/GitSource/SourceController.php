<?php

declare(strict_types=1);

namespace App\Controller\GitSource;

use App\Exception\ServiceException;
use App\Response\LabelledBody;
use App\Response\Response;
use App\Response\Source\GitSource;
use App\Security\AuthenticationToken;
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
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/git-source', name: 'git_source_')]
readonly class SourceController
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
    public function create(AuthenticationToken $token, Request $request): JsonResponse
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
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new Response(
            new LabelledBody(
                'git_source',
                new GitSource(
                    $source->getId(),
                    $source->getLabel(),
                    $source->getHostUrl(),
                    $source->getPath(),
                    $source->hasCredentials(),
                    $source->getDeletedAt()
                )
            )
        );
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'handle', methods: ['GET', 'PUT', 'DELETE'])]
    public function handle(
        AuthenticationToken $token,
        string $sourceId,
        Request $request
    ): SymfonyResponse {
        try {
            $credentials = $request->request->getString('credentials');
            if ('' === $credentials) {
                $credentials = null;
            }

            $source = match ($request->getMethod()) {
                'GET' => $this->client->get($token->token, $sourceId),
                'PUT' => $this->client->update(
                    $token->token,
                    $sourceId,
                    $request->request->getString('label'),
                    $request->request->getString('host-url'),
                    $request->request->getString('path'),
                    $credentials
                ),
                'DELETE' => $this->client->delete($token->token, $sourceId),
                default => null,
            };
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

        if (null === $source) {
            return new SymfonyResponse(null, 405);
        }

        return new Response(
            new LabelledBody(
                'git_source',
                new GitSource(
                    $source->getId(),
                    $source->getLabel(),
                    $source->getHostUrl(),
                    $source->getPath(),
                    $source->hasCredentials(),
                    $source->getDeletedAt()
                )
            )
        );
    }
}
