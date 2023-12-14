<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'user_token_')]
readonly class TokenController
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
    #[Route('/user/frontend-token/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/frontend-token/verify', name: 'verify', methods: ['GET'])]
    public function verify(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/frontend-token/refresh ', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }
}
