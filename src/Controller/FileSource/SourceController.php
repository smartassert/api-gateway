<?php

declare(strict_types=1);

namespace App\Controller\FileSource;

use App\Exception\ServiceException;
use App\Response\LabelledBody;
use App\Response\Response;
use App\Response\Source\FileSource;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\Exception\ModifyReadOnlyEntityException;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source/{sourceId<[A-Z90-9]{26}>}', name: 'file_source_')]
readonly class SourceController
{
    public function __construct(
        private FileSourceClientInterface $client,
    ) {
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'handle', methods: ['GET', 'PUT', 'DELETE'])]
    public function handle(AuthenticationToken $token, string $sourceId, Request $request): SymfonyResponse
    {
        $source = null;

        try {
            if ('GET' === $request->getMethod()) {
                $source = $this->client->get($token->token, $sourceId);
            }

            if ('PUT' === $request->getMethod()) {
                $source = $this->client->update($token->token, $sourceId, $request->request->getString('label'));
            }

            if ('DELETE' === $request->getMethod()) {
                $source = $this->client->delete($token->token, $sourceId);
            }
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
                'file_source',
                new FileSource($source->getId(), $source->getLabel(), $source->getDeletedAt())
            )
        );
    }
}
