<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'user_token_')]
readonly class TokenController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route('/{serviceName<[a-z]+>}/frontend-token/create', name: 'create', methods: ['POST'])]
    public function create(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }

    /**
     * @throws ServiceException
     */
    #[Route('/{serviceName<[a-z]+>}/frontend-token/verify', name: 'verify', methods: ['GET'])]
    public function verify(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }

    /**
     * @throws ServiceException
     */
    #[Route('/{serviceName<[a-z]+>}/frontend-token/refresh ', name: 'refresh', methods: ['POST'])]
    public function refresh(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
