<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class CreationController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route('/{serviceName<[a-z]+>}/create', name: 'user_create', methods: ['POST'])]
    public function create(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
