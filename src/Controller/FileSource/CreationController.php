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
use SmartAssert\SourcesClient\FileSourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

readonly class CreationController
{
    public function __construct(
        private FileSourceClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route('/file-source', name: 'file_source_create', methods: ['POST'])]
    public function create(AuthenticationToken $token, Request $request): JsonResponse
    {
        try {
            $source = $this->client->create($token->token, $request->request->getString('label'));
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
                'file_source',
                new FileSource($source->getLabel(), $source->getDeletedAt())
            )
        );
    }
}
