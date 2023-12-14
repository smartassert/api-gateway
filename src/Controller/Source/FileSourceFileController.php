<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/source/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileSourceFileController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
        private ServiceCollection $serviceCollection,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route(name: 'handle', methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    public function handle(Request $request): Response
    {
        return $this->serviceProxy->proxy(
            service: $this->serviceCollection->get('source'),
            inbound: $request,
            successContentType: 'text/x-yaml'
        );
    }
}
