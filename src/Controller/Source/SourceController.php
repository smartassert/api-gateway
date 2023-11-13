<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Response\LabelledCollectionBody;
use App\Response\Response;
use App\Response\Source\FileSourceBody;
use App\Response\Source\GitSourceBody;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\Model\FileSource;
use SmartAssert\SourcesClient\Model\GitSource;
use SmartAssert\SourcesClient\SourceClientInterface;
use Symfony\Component\Routing\Annotation\Route;

readonly class SourceController
{
    public function __construct(
        private SourceClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/sources/list', name: 'sources_list', methods: ['GET'])]
    public function list(AuthenticationToken $token): Response
    {
        try {
            $sources = $this->client->list($token->token);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        $sourceBodies = [];
        foreach ($sources as $source) {
            if ($source instanceof FileSource) {
                $sourceBodies[] = new FileSourceBody($source);
            }

            if ($source instanceof GitSource) {
                $sourceBodies[] = new GitSourceBody($source);
            }
        }

        return new Response(
            new LabelledCollectionBody('sources', $sourceBodies),
        );
    }
}
